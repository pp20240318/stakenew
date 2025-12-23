<?php

namespace app\api\controller;

use Throwable;
use think\facade\Config;
use think\facade\Db;
use think\Request;
use app\common\controller\Frontend;

class Stake extends Frontend
{
    protected array $noNeedLogin = ['plans', 'config', 'levels', 'activities'];

    public function initialize(): void
    {
        parent::initialize();
    }

    /**
     * Get all available staking plans
     * @param Request $request
     * @return void
     */
    public function plans(Request $request)
    {
        $userId = $request->param('user_id');
        $sort = $request->param('sort', 'sort');
        $order = $request->param('order', 'asc');
        $minAmount = $request->param('min_amount');
        $maxAmount = $request->param('max_amount');
        $duration = $request->param('duration');
        $coinType = $request->param('coin_type');

        // Build query
        $query = Db::name('ba_stake_plan')->where('status', 1)->where('is_deleted', 0);

        // Apply filters
        if ($minAmount) {
            $query->where('min_amount', '>=', $minAmount);
        }
        if ($maxAmount) {
            $query->where('max_amount', '<=', $maxAmount);
        }
        if ($duration) {
            $query->where('duration', $duration);
        }
        if ($coinType) {
            $query->where('coin_type', $coinType);
        }

        // If user_id is provided, check level restrictions
        if ($userId) {
            $userLevel = $this->getUserLevel($userId);
            if ($userLevel) {
                // Only show plans available for user's level
                $query->where(function ($q) use ($userLevel) {
                    $q->where('level_limit', 'null')
                      ->whereOr('level_limit', '')
                      ->whereOr(function ($sq) use ($userLevel) {
                          $sq->where('level_limit', 'like', '%"'.$userLevel.'"%')
                             ->whereOr('level_limit', 'like', '%,'.$userLevel.',%');
                      });
                });
            }
        }

        // Get paginated results
        $list = $query->order($sort, $order)->paginate();

        $this->success('', $list);
    }

    /**
     * Get detailed information about a specific plan
     * @param Request $request
     * @param int $id
     * @return void
     */
    public function planDetail(Request $request, $id)
    {
        $plan = Db::name('ba_stake_plan')->where('id', $id)->where('status', 1)->where('is_deleted', 0)->find();
        
        if (!$plan) {
            $this->error('Plan not found');
        }

        // Get reward rules for this plan
        $rules = Db::name('ba_stake_reward_rule')
            ->where(function ($query) use ($id) {
                $query->where('plan_id', $id)->whereOr('plan_id', 0);
            })
            ->where('status', 1)
            ->select();

        $plan['reward_rules'] = $rules;

        $this->success('', $plan);
    }

    /**
     * Create a new stake
     * @param Request $request
     * @return void
     */
    public function create(Request $request)
    {
        $userId = $request->param('user_id');
        $planId = $request->param('plan_id');
        $amount = $request->param('amount');
        $coinType = $request->param('coin_type', 'USDT');

        // Validate parameters
        if (!$userId || !$planId || !$amount) {
            $this->error('Missing required parameters');
        }

        // Get plan details
        $plan = Db::name('ba_stake_plan')->where('id', $planId)->where('status', 1)->where('is_deleted', 0)->find();
        if (!$plan) {
            $this->error('Invalid plan');
        }

        // Check if amount is within plan limits
        if ($amount < $plan['min_amount'] || ($plan['max_amount'] > 0 && $amount > $plan['max_amount'])) {
            $this->error("Amount must be between {$plan['min_amount']} and {$plan['max_amount']}");
        }

        // Check if user has enough balance
        $user = Db::name('user')->where('id', $userId)->find();
        if (!$user || $user['money'] < $amount) {
            $this->error('Insufficient balance');
        }

        // Generate order number
        $orderNo = 'ST' . date('YmdHis') . rand(1000, 9999);
        
        // Start transaction
        Db::startTrans();
        try {
            // Deduct user balance
            Db::name('user')->where('id', $userId)->dec('money', $amount)->update();
            
            // Record wallet transaction
            Db::name('ba_stake_wallet_record')->insert([
                'user_id' => $userId,
                'type' => 'stake',
                'amount' => -$amount,
                'coin_type' => $coinType,
                'description' => "Staked {$amount} {$coinType} in {$plan['name']}",
                'create_time' => time()
            ]);

            // Create stake record
            $startTime = time();
            $endTime = $startTime + ($plan['duration'] * 86400);
            $nextProfitTime = strtotime(date('Y-m-d 00:00:00', strtotime('+1 day')));
            
            $stakeData = [
                'user_id' => $userId,
                'plan_id' => $planId,
                'order_no' => $orderNo,
                'amount' => $amount,
                'coin_type' => $coinType,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'duration' => $plan['duration'],
                'status' => 0, // Active
                'annual_rate' => $plan['annual_rate'],
                'daily_rate' => $plan['daily_rate'],
                'total_reward' => 0,
                'received_reward' => 0,
                'next_profit_time' => $nextProfitTime,
                'create_time' => time(),
                'update_time' => time()
            ];
            
            $stakeId = Db::name('ba_stake_record')->insertGetId($stakeData);
            
            // Update user level if needed
            $this->updateUserLevel($userId);
            
            // Process referral rewards
            $this->processReferralRewards($userId, $stakeId, $amount, $coinType);
            
            Db::commit();
            
            // Get the created stake
            $stake = Db::name('ba_stake_record')->where('id', $stakeId)->find();
            
            $this->success('Stake created successfully', $stake);
        } catch (Throwable $e) {
            Db::rollback();
            $this->error('Failed to create stake: ' . $e->getMessage());
        }
    }

    /**
     * Redeem a stake (early or on maturity)
     * @param Request $request
     * @return void
     */
    public function redeem(Request $request)
    {
        $userId = $request->param('user_id');
        $stakeId = $request->param('stake_id');

        // Validate parameters
        if (!$userId || !$stakeId) {
            $this->error('Missing required parameters');
        }

        // Get stake details
        $stake = Db::name('ba_stake_record')
            ->where('id', $stakeId)
            ->where('user_id', $userId)
            ->where('status', 0) // Active
            ->find();

        if (!$stake) {
            $this->error('Invalid stake or already redeemed');
        }

        // Check if mature or early redemption
        $isEarlyRedemption = time() < $stake['end_time'];
        $redemptionFee = 0;
        
        if ($isEarlyRedemption) {
            // Get early redemption fee from plan or config
            $plan = Db::name('ba_stake_plan')->where('id', $stake['plan_id'])->find();
            $redemptionRate = $plan['early_redemption_rate'] > 0 ? $plan['early_redemption_rate'] : $this->getConfig('redemption_fee_rate', 2);
            $redemptionFee = $stake['amount'] * ($redemptionRate / 100);
        }

        $redeemedAmount = $stake['amount'] - $redemptionFee;

        // Start transaction
        Db::startTrans();
        try {
            // Update stake status
            Db::name('ba_stake_record')
                ->where('id', $stakeId)
                ->update([
                    'status' => $isEarlyRedemption ? 2 : 1, // 1=Completed, 2=Early redeemed
                    'redemption_time' => time(),
                    'redemption_fee' => $redemptionFee,
                    'update_time' => time()
                ]);

            // Add redeemed amount to user balance
            Db::name('user')->where('id', $userId)->inc('money', $redeemedAmount)->update();

            // Record wallet transaction
            Db::name('ba_stake_wallet_record')->insert([
                'user_id' => $userId,
                'type' => 'redemption',
                'amount' => $redeemedAmount,
                'coin_type' => $stake['coin_type'],
                'related_id' => $stakeId,
                'description' => $isEarlyRedemption 
                    ? "Early redemption of stake #{$stakeId} (fee: {$redemptionFee} {$stake['coin_type']})"
                    : "Mature redemption of stake #{$stakeId}",
                'create_time' => time()
            ]);

            Db::commit();

            $this->success('Stake redeemed successfully', [
                'stake_id' => $stakeId,
                'redeemed_amount' => $redeemedAmount,
                'redemption_fee' => $redemptionFee,
                'status' => $isEarlyRedemption ? 2 : 1,
                'redemption_time' => time()
            ]);
        } catch (Throwable $e) {
            Db::rollback();
            $this->error('Failed to redeem stake: ' . $e->getMessage());
        }
    }

    /**
     * Claim available profits from stake
     * @param Request $request
     * @return void
     */
    public function claimProfit(Request $request)
    {
        $userId = $request->param('user_id');
        $stakeId = $request->param('stake_id');

        // Validate parameters
        if (!$userId || !$stakeId) {
            $this->error('Missing required parameters');
        }

        // Get unclaimed profits
        $profits = Db::name('ba_stake_profit_record')
            ->where('user_id', $userId)
            ->where('stake_record_id', $stakeId)
            ->where('status', 0) // Unclaimed
            ->select();

        if (count($profits) == 0) {
            $this->error('No profits available to claim');
        }

        $totalAmount = 0;
        $profitIds = [];

        // Start transaction
        Db::startTrans();
        try {
            foreach ($profits as $profit) {
                $totalAmount += $profit['amount'];
                $profitIds[] = $profit['id'];

                // Update profit status
                Db::name('ba_stake_profit_record')
                    ->where('id', $profit['id'])
                    ->update([
                        'status' => 1, // Claimed
                        'receive_time' => time(),
                        'update_time' => time()
                    ]);
            }

            // Add amount to user balance
            Db::name('user')->where('id', $userId)->inc('money', $totalAmount)->update();

            // Update received_reward in stake record
            Db::name('ba_stake_record')
                ->where('id', $stakeId)
                ->inc('received_reward', $totalAmount)
                ->update();

            // Record wallet transaction
            Db::name('ba_stake_wallet_record')->insert([
                'user_id' => $userId,
                'type' => 'profit',
                'amount' => $totalAmount,
                'coin_type' => $profits[0]['coin_type'],
                'related_id' => $stakeId,
                'description' => "Claimed profits from stake #{$stakeId}",
                'create_time' => time()
            ]);

            Db::commit();

            $this->success('Profit claimed successfully', [
                'claimed_amount' => $totalAmount,
                'stake_id' => $stakeId,
                'profit_ids' => $profitIds
            ]);
        } catch (Throwable $e) {
            Db::rollback();
            $this->error('Failed to claim profit: ' . $e->getMessage());
        }
    }

    /**
     * Get all stake positions for a user
     * @param Request $request
     * @return void
     */
    public function userStakes(Request $request)
    {
        $userId = $request->param('user_id');
        $status = $request->param('status');
        $coinType = $request->param('coin_type');

        if (!$userId) {
            $this->error('User ID is required');
        }

        $query = Db::name('ba_stake_record')
            ->alias('sr')
            ->leftJoin('ba_stake_plan sp', 'sp.id = sr.plan_id')
            ->field('sr.*, sp.name as plan_name')
            ->where('sr.user_id', $userId);

        if ($status !== null && $status !== '') {
            $query->where('sr.status', $status);
        }

        if ($coinType) {
            $query->where('sr.coin_type', $coinType);
        }

        $list = $query->order('sr.create_time', 'desc')->paginate();

        $this->success('', $list);
    }

    /**
     * Get detailed information about a specific stake
     * @param Request $request
     * @param int $id
     * @return void
     */
    public function details(Request $request, $id)
    {
        $userId = $request->param('user_id');

        if (!$userId) {
            $this->error('User ID is required');
        }

        // Get stake details
        $stake = Db::name('ba_stake_record')
            ->alias('sr')
            ->leftJoin('ba_stake_plan sp', 'sp.id = sr.plan_id')
            ->field('sr.*, sp.name as plan_name')
            ->where('sr.id', $id)
            ->where('sr.user_id', $userId)
            ->find();

        if (!$stake) {
            $this->error('Stake not found');
        }

        // Get profits for this stake
        $profits = Db::name('ba_stake_profit_record')
            ->where('stake_record_id', $id)
            ->where('user_id', $userId)
            ->order('day_index', 'asc')
            ->select();

        $this->success('', [
            'stake' => $stake,
            'profits' => $profits
        ]);
    }

    /**
     * Get a summary of user's staking activity
     * @param Request $request
     * @return void
     */
    public function userSummary(Request $request)
    {
        $userId = $request->param('user_id');
        $coinType = $request->param('coin_type');

        if (!$userId) {
            $this->error('User ID is required');
        }

        // Build base query
        $query = Db::name('ba_stake_record')->where('user_id', $userId);
        if ($coinType) {
            $query->where('coin_type', $coinType);
        }

        // Get total staked
        $totalStaked = $query->sum('amount');

        // Get active stakes count
        $activeStakes = $query->where('status', 0)->count();

        // Get total earned
        $totalEarned = Db::name('ba_stake_profit_record')
            ->where('user_id', $userId)
            ->where('status', 1); // Claimed
        if ($coinType) {
            $totalEarned->where('coin_type', $coinType);
        }
        $totalEarned = $totalEarned->sum('amount');

        // Get unclaimed profit
        $unclaimedProfit = Db::name('ba_stake_profit_record')
            ->where('user_id', $userId)
            ->where('status', 0); // Unclaimed
        if ($coinType) {
            $unclaimedProfit->where('coin_type', $coinType);
        }
        $unclaimedProfit = $unclaimedProfit->sum('amount');

        // Get user level
        $userLevel = Db::name('ba_stake_user_level')
            ->alias('ul')
            ->leftJoin('ba_stake_level l', 'l.id = ul.level_id')
            ->field('ul.*, l.name as level_name, l.reward_boost')
            ->where('ul.user_id', $userId)
            ->where('ul.status', 1)
            ->find();

        $this->success('', [
            'total_staked' => $totalStaked ?: 0,
            'active_stakes' => $activeStakes ?: 0,
            'total_earned' => $totalEarned ?: 0,
            'unclaimed_profit' => $unclaimedProfit ?: 0,
            'user_level' => $userLevel
        ]);
    }

    /**
     * Get profit history for a user
     * @param Request $request
     * @return void
     */
    public function profitHistory(Request $request)
    {
        $userId = $request->param('user_id');
        $stakeId = $request->param('stake_id');
        $status = $request->param('status');

        if (!$userId) {
            $this->error('User ID is required');
        }

        $query = Db::name('ba_stake_profit_record')
            ->alias('pr')
            ->leftJoin('ba_stake_record sr', 'sr.id = pr.stake_record_id')
            ->leftJoin('ba_stake_plan sp', 'sp.id = pr.plan_id')
            ->field('pr.*, sp.name as plan_name')
            ->where('pr.user_id', $userId);

        if ($stakeId) {
            $query->where('pr.stake_record_id', $stakeId);
        }

        if ($status !== null && $status !== '') {
            $query->where('pr.status', $status);
        }

        $list = $query->order('pr.profit_date', 'desc')->paginate();

        $this->success('', $list);
    }

    /**
     * Get all available stake user levels
     * @param Request $request
     * @return void
     */
    public function levels(Request $request)
    {
        $levels = Db::name('ba_stake_level')->order('level', 'asc')->select();
        $this->success('', $levels);
    }

    /**
     * Get user's current stake level
     * @param Request $request
     * @return void
     */
    public function userLevel(Request $request)
    {
        $userId = $request->param('user_id');

        if (!$userId) {
            $this->error('User ID is required');
        }

        // Get current user level
        $userLevel = Db::name('ba_stake_user_level')
            ->alias('ul')
            ->leftJoin('ba_stake_level l', 'l.id = ul.level_id')
            ->field('ul.*, l.name as level_name, l.description as level_description, l.level, l.reward_boost, l.icon')
            ->where('ul.user_id', $userId)
            ->where('ul.status', 1)
            ->find();

        if (!$userLevel) {
            // User has no level yet
            $this->success('', [
                'user_level' => null,
                'next_level' => Db::name('ba_stake_level')->where('level', 1)->find()
            ]);
            return;
        }

        // Get next level
        $nextLevel = Db::name('ba_stake_level')
            ->where('level', '>', $userLevel['level'])
            ->order('level', 'asc')
            ->find();

        $result = [
            'user_level' => $userLevel,
            'next_level' => null
        ];

        if ($nextLevel) {
            // Calculate progress to next level
            $amountNeeded = $nextLevel['required_amount'] - $userLevel['total_staked'];
            $progress = min(100, ($userLevel['total_staked'] / $nextLevel['required_amount']) * 100);
            
            $nextLevel['progress'] = round($progress);
            $nextLevel['amount_needed'] = max(0, $amountNeeded);
            
            $result['next_level'] = $nextLevel;
        }

        $this->success('', $result);
    }

    /**
     * Get user's referral statistics
     * @param Request $request
     * @return void
     */
    public function referralStats(Request $request)
    {
        $userId = $request->param('user_id');

        if (!$userId) {
            $this->error('User ID is required');
        }

        // Get total referrals
        $level1Referrals = Db::name('ba_stake_referral_reward')
            ->where('user_id', $userId)
            ->where('level', 1)
            ->group('referral_id')
            ->count();

        $level2Referrals = Db::name('ba_stake_referral_reward')
            ->where('user_id', $userId)
            ->where('level', 2)
            ->group('referral_id')
            ->count();

        // Get total rewards
        $level1Reward = Db::name('ba_stake_referral_reward')
            ->where('user_id', $userId)
            ->where('level', 1)
            ->where('status', 1) // Paid
            ->sum('amount');

        $level2Reward = Db::name('ba_stake_referral_reward')
            ->where('user_id', $userId)
            ->where('level', 2)
            ->where('status', 1) // Paid
            ->sum('amount');

        // Get pending rewards
        $pendingReward = Db::name('ba_stake_referral_reward')
            ->where('user_id', $userId)
            ->where('status', 0) // Pending
            ->sum('amount');

        $this->success('', [
            'total_referrals' => ($level1Referrals + $level2Referrals) ?: 0,
            'level1_referrals' => $level1Referrals ?: 0,
            'level2_referrals' => $level2Referrals ?: 0,
            'total_reward' => ($level1Reward + $level2Reward) ?: 0,
            'level1_reward' => $level1Reward ?: 0,
            'level2_reward' => $level2Reward ?: 0,
            'pending_reward' => $pendingReward ?: 0
        ]);
    }

    /**
     * Get detailed referral rewards
     * @param Request $request
     * @return void
     */
    public function referralRewards(Request $request)
    {
        $userId = $request->param('user_id');
        $status = $request->param('status');

        if (!$userId) {
            $this->error('User ID is required');
        }

        $query = Db::name('ba_stake_referral_reward')
            ->alias('rr')
            ->leftJoin('user u', 'u.id = rr.referral_id')
            ->field('rr.*, u.username as referral_username')
            ->where('rr.user_id', $userId);

        if ($status !== null && $status !== '') {
            $query->where('rr.status', $status);
        }

        $list = $query->order('rr.create_time', 'desc')->paginate();

        $this->success('', $list);
    }

    /**
     * Get wallet transaction history for staking
     * @param Request $request
     * @return void
     */
    public function walletRecords(Request $request)
    {
        $userId = $request->param('user_id');
        $type = $request->param('type');
        $coinType = $request->param('coin_type');

        if (!$userId) {
            $this->error('User ID is required');
        }

        $query = Db::name('ba_stake_wallet_record')->where('user_id', $userId);

        if ($type) {
            $query->where('type', $type);
        }

        if ($coinType) {
            $query->where('coin_type', $coinType);
        }

        $list = $query->order('create_time', 'desc')->paginate();

        $this->success('', $list);
    }

    /**
     * Get active staking promotional activities
     * @param Request $request
     * @return void
     */
    public function activities(Request $request)
    {
        $status = $request->param('status');

        $query = Db::name('ba_stake_activity');

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        } else {
            // By default, show active activities
            $query->where('status', 1);
        }

        $activities = $query->order('start_time', 'desc')->select();
        $this->success('', $activities);
    }

    /**
     * Get system configuration for staking
     * @param Request $request
     * @return void
     */
    public function config(Request $request)
    {
        $configs = Db::name('ba_stake_config')->select();
        
        $result = [];
        foreach ($configs as $config) {
            $result[$config['name']] = $config['value'];
        }
        
        $this->success('', $result);
    }

    /**
     * Helper method to get user's level
     * @param int $userId
     * @return int|null
     */
    protected function getUserLevel($userId)
    {
        $userLevel = Db::name('ba_stake_user_level')
            ->alias('ul')
            ->leftJoin('ba_stake_level l', 'l.id = ul.level_id')
            ->field('l.level')
            ->where('ul.user_id', $userId)
            ->where('ul.status', 1)
            ->find();
        
        return $userLevel ? $userLevel['level'] : null;
    }

    /**
     * Helper method to update user's level based on their staking activity
     * @param int $userId
     * @return void
     */
    protected function updateUserLevel($userId)
    {
        // Get user's total staked amount and max duration
        $userStats = Db::name('ba_stake_record')
            ->where('user_id', $userId)
            ->where('status', 0) // Active stakes only
            ->field('SUM(amount) as total_staked, MAX(duration) as max_duration')
            ->find();
        
        if (!$userStats || $userStats['total_staked'] <= 0) {
            return;
        }

        // Find the highest level the user qualifies for
        $qualifyingLevel = Db::name('ba_stake_level')
            ->where('required_amount', '<=', $userStats['total_staked'])
            ->where('required_duration', '<=', $userStats['max_duration'])
            ->order('level', 'desc')
            ->find();
        
        if (!$qualifyingLevel) {
            return;
        }

        // Check if user already has this level or higher
        $currentUserLevel = Db::name('ba_stake_user_level')
            ->alias('ul')
            ->leftJoin('ba_stake_level l', 'l.id = ul.level_id')
            ->field('ul.*, l.level')
            ->where('ul.user_id', $userId)
            ->where('ul.status', 1)
            ->find();
        
        if ($currentUserLevel && $currentUserLevel['level'] >= $qualifyingLevel['level']) {
            // User already has this level or higher, just update the stats
            Db::name('ba_stake_user_level')
                ->where('id', $currentUserLevel['id'])
                ->update([
                    'total_staked' => $userStats['total_staked'],
                    'max_duration' => $userStats['max_duration'],
                    'update_time' => time()
                ]);
            return;
        }

        // Calculate expiration (30 days after the longest stake ends)
        $longestStake = Db::name('ba_stake_record')
            ->where('user_id', $userId)
            ->where('status', 0) // Active
            ->order('end_time', 'desc')
            ->find();
        
        $expireTime = $longestStake ? $longestStake['end_time'] + (30 * 86400) : (time() + (30 * 86400));

        // User needs a new level or upgrade
        if ($currentUserLevel) {
            // Update existing level
            Db::name('ba_stake_user_level')
                ->where('id', $currentUserLevel['id'])
                ->update([
                    'level_id' => $qualifyingLevel['id'],
                    'total_staked' => $userStats['total_staked'],
                    'max_duration' => $userStats['max_duration'],
                    'expire_time' => $expireTime,
                    'update_time' => time()
                ]);
        } else {
            // Create new level record
            Db::name('ba_stake_user_level')->insert([
                'user_id' => $userId,
                'level_id' => $qualifyingLevel['id'],
                'total_staked' => $userStats['total_staked'],
                'max_duration' => $userStats['max_duration'],
                'expire_time' => $expireTime,
                'status' => 1,
                'create_time' => time(),
                'update_time' => time()
            ]);
        }
    }

    /**
     * Helper method to process referral rewards for a stake
     * @param int $userId User who staked
     * @param int $stakeId Stake record ID
     * @param float $amount Stake amount
     * @param string $coinType Coin type
     * @return void
     */
    protected function processReferralRewards($userId, $stakeId, $amount, $coinType)
    {
        // Get the user's referrer (level 1)
        $user = Db::name('user')->where('id', $userId)->find();
        if (!$user || empty($user['pid'])) {
            return; // No referrer
        }

        $level1ReferrerId = $user['pid'];
        $level1Rate = $this->getConfig('level1_referral_rate', 5);
        $level1Amount = $amount * ($level1Rate / 100);

        // Create level 1 referral reward
        if ($level1Amount > 0) {
            Db::name('ba_stake_referral_reward')->insert([
                'user_id' => $level1ReferrerId,
                'referral_id' => $userId,
                'stake_record_id' => $stakeId,
                'level' => 1,
                'amount' => $level1Amount,
                'coin_type' => $coinType,
                'rate' => $level1Rate,
                'status' => 1, // Auto-pay
                'create_time' => time(),
                'update_time' => time()
            ]);

            // Add to user balance
            Db::name('user')->where('id', $level1ReferrerId)->inc('money', $level1Amount)->update();

            // Record wallet transaction
            Db::name('ba_stake_wallet_record')->insert([
                'user_id' => $level1ReferrerId,
                'type' => 'referral',
                'amount' => $level1Amount,
                'coin_type' => $coinType,
                'related_id' => $stakeId,
                'description' => "Level 1 referral reward from user #{$userId}",
                'create_time' => time()
            ]);
        }

        // Check for level 2 referrer
        $level1User = Db::name('user')->where('id', $level1ReferrerId)->find();
        if (!$level1User || empty($level1User['pid'])) {
            return; // No level 2 referrer
        }

        $level2ReferrerId = $level1User['pid'];
        $level2Rate = $this->getConfig('level2_referral_rate', 2);
        $level2Amount = $amount * ($level2Rate / 100);

        // Create level 2 referral reward
        if ($level2Amount > 0) {
            Db::name('ba_stake_referral_reward')->insert([
                'user_id' => $level2ReferrerId,
                'referral_id' => $userId,
                'stake_record_id' => $stakeId,
                'level' => 2,
                'amount' => $level2Amount,
                'coin_type' => $coinType,
                'rate' => $level2Rate,
                'status' => 1, // Auto-pay
                'create_time' => time(),
                'update_time' => time()
            ]);

            // Add to user balance
            Db::name('user')->where('id', $level2ReferrerId)->inc('money', $level2Amount)->update();

            // Record wallet transaction
            Db::name('ba_stake_wallet_record')->insert([
                'user_id' => $level2ReferrerId,
                'type' => 'referral',
                'amount' => $level2Amount,
                'coin_type' => $coinType,
                'related_id' => $stakeId,
                'description' => "Level 2 referral reward from user #{$userId}",
                'create_time' => time()
            ]);
        }
    }

    /**
     * Helper method to get config value
     * @param string $name Config name
     * @param mixed $default Default value if not found
     * @return mixed
     */
    protected function getConfig($name, $default = null)
    {
        $config = Db::name('ba_stake_config')->where('name', $name)->find();
        return $config ? $config['value'] : $default;
    }
} 