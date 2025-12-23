<?php

namespace app\api\controller;

use Throwable;
use think\facade\Db;
use think\facade\Config;
use think\Request;
use app\common\controller\Frontend;
use think\facade\Log;
use think\Response;

class Bot extends Frontend
{
    protected array $noNeedLogin = ['index','status','transactions'];

    public function initialize(): void
    {
        parent::initialize();
        
        // 记录所有请求头信息，帮助调试
        Log::info('Bot Controller Request Headers: ' . json_encode(request()->header()));
        Log::info('Bot Controller All Params: ' . json_encode(request()->param()));
        
        // 检查 token 是否存在
        $token = request()->header('token');
        Log::info('Bot Controller Token: ' . ($token ?: 'none'));
        
        // 检查 request 对象是否有 userId 属性
        $userId = request()->userId ?? null;
        Log::info('Bot Controller userId from request: ' . ($userId ?: 'none'));
        
        // 检查请求参数是否有 userid
        $userIdParam = request()->param('userid');
        Log::info('Bot Controller userid from param: ' . ($userIdParam ?: 'none'));
    }

    /**
     * Get bot statistics and account info
     * @throws Throwable
     */
    public function index(Request $request)
    {
        // 首先尝试从token获取userId
        $userid = $request->userId;
        
        Log::info('666666');
        // 如果没有userId，尝试从参数获取
        if (empty($userid)) {
            $userid = $request->param("userid");
            Log::info('Bot index: Using userid from param: ' . ($userid ?: 'none'));
        } else {
            Log::info('Bot index: Using userId from request: ' . $userid);
        }
        
        // Default values
        $balanceTotal = '0.00';
        $profitTotal = '0.00';
        $runningBots = 0;
        $totalBots = 0;
        
        if (!empty($userid)) {
            // Get user balance
            $user = Db::name("user")->where("id", $userid)->find();
            if ($user) {
                $balanceTotal = $user['money'] ? number_format($user['money'], 2) : '0.00';
            }
            
            // Get today's profit
            $today = strtotime(date('Y-m-d'));
            $profitLog = Db::name("user_money_log")
                ->field("sum(profit) as profit")
                ->where("user_id", $userid)
                ->where("type",2)
                ->where("create_time", '>=', $today)
                ->whereIn("business_typeid", [3,4])
                ->find();
            
            $profitTotal = $profitLog && $profitLog['profit'] ? number_format($profitLog['profit'], 2) : '0.00';
            
            // Get bot statistics
            $botStats = Db::name("nft_purchase")
                ->where("user_id", $userid)
                ->where("source", 'Trading Bot')
                ->field("COUNT(*) as total, SUM(IF(status='1', 1, 0)) as running")
                ->find();
            
            if ($botStats) {
                $runningBots = $botStats['running'] ?? 0;
                $totalBots = $botStats['total'] ?? 0;
            }
        }
        
        $this->success('', [
            'balanceTotal' => $balanceTotal,
            'profitTotal' => $profitTotal,
            'runningBots' => $runningBots,
            'totalBots' => $totalBots
        ]);
    }

    /**
     * Get trading transaction history
     */
    public function transactions(Request $request)
    {
        // 首先尝试从token获取userId
        $userid = $request->userId;
        
        // 如果没有userId，尝试从参数获取
        if (empty($userid)) {
            $userid = $request->param("userid");
            Log::info('Bot transactions: Using userid from param: ' . ($userid ?: 'none'));
        } else {
            Log::info('Bot transactions: Using userId from request: ' . $userid);
        }
        
        $page = $request->param("page", 1);
        $limit = $request->param("limit", 100);
        
        $transactions = [];
        $total = 0;
        
        if (!empty($userid)) {
            // Count total records for pagination
            $total = Db::name("user_money_log")->where('type',2)
                ->where("user_id", $userid)
                ->where(function ($query) {
                    $query->where("business_type", 'like', 'Buy %')
                        ->whereOr("business_type", 'like', 'Sell %');
                })
                ->count();
            
            // Get transaction records
            $records = Db::name("user_money_log")->where('type',2)
                ->where("user_id", $userid)
                ->where(function ($query) {
                    $query->where("business_type", 'like', 'Buy %')
                        ->whereOr("business_type", 'like', 'Sell %');
                })
                ->order("create_time", "desc")
                ->limit(($page - 1) * $limit, $limit)
                ->select()->toArray();
            
            foreach ($records as $record) {
                $date = date('ymd', $record['create_time']);
                $time = date('H:i', $record['create_time']);
                $isBuy = strpos($record['business_type'], 'Buy') === 0;
                
                $transactions[] = [
                    'date' => $date,
                    'time' => $time,
                    'operation' => $record['business_type'],
                    'amount' => abs($record['money']) . ' USDT',
                    'profit_rate' => $record['profit_rate'] ,
                    'isBuy' => $isBuy
                ];
            }
        }
        
        $this->success('', [
            'transactions' => $transactions,
            'total' => $total
        ]);
    }

    /**
     * Start the trading bot
     * 
     * 正确的请求方式:
     * 1. 使用 token: 
     *    - 请求头中必须包含 token 字段
     *    - 示例: header('token': 'xxxx-xxxx-xxxx')
     *    
     * 2. 使用 userid 参数: 
     *    - 请求参数中必须包含 userid 字段
     *    - 示例: {userid: 123}
     */
    public function start(Request $request)
    {      
            Log::info('Bot start: API called');
            Log::info('Bot start params: ' . json_encode($request->param()));
            Log::info('Bot start headers: ' . json_encode($request->header()));
            
            // 尝试多种方式获取 userid
            $userid = null;
            
            // 优先从中间件设置的 userId 获取
            if (isset($request->userId)) {
                $userid = $request->userId;
                Log::info('Bot start: Got userId from request object: ' . $userid);
            } 
            // 如果中间件未设置，尝试从请求参数获取 
            else if ($request->has('userid')) {
                $userid = $request->param('userid');
                Log::info('Bot start: Got userId from request param: ' . $userid);
            }
            
            // 检查 userid 是否存在
            if (empty($userid)) {
                Log::error('Bot start: No userId found in request');
                $response = Response::create([
                    'code' => 0,
                    'msg'  => __('Missing user ID parameter'),
                    'time' => time(),
                    'data' => null,
                ], 'json', 200);
                return $response;
            }
            
            // 确保 userid 是数字
            if (!is_numeric($userid)) {
                Log::error('Bot start: Invalid userId format: ' . $userid);
                $response = Response::create([
                    'code' => 0,
                    'msg'  => __('Invalid user ID format'),
                    'time' => time(),
                    'data' => null,
                ], 'json', 200);
                return $response;
            }

            // 检查用户当天是否已经启动过机器人

            $todayStart = strtotime(date('Y-m-d')); // 今天0点的时间戳
            $todayEnd = $todayStart + 86399; // 今天23:59:59的时间戳 
            $startedToday = Db::name("nft_purchase")
                ->where("user_id", $userid)
                ->where("source", 'Trading Bot')
                ->where("create_time", ">=", $todayStart)
                ->where("create_time", "<=", $todayEnd)
                ->find();
            
            // // print_r($startedToday);
            // if ($startedToday) {
            //     Log::info('Bot start: User already started bot today: ' . $userid);
            //     $response = Response::create([
            //         'code' => 0,
            //         'msg'  => __('You have already started the bot today. Please try again tomorrow.'),
            //         'time' => time(),
            //         'data' => null,
            //     ], 'json', 200);
            //     return $response;
            // }
            
            // 查找用户
            Log::info('Bot start: Attempting to find user with ID: ' . $userid);
            $user = Db::name("user")->where("id", $userid)->find();
            if (!$user) {
                Log::error('Bot start: User not found for ID: ' . $userid);
                $response = Response::create([
                    'code' => 0,
                    'msg'  => __('User not found'),
                    'time' => time(),
                    'data' => null,
                ], 'json', 200);
                return $response;
            }
            
            Log::info('Bot start: User found: ' . json_encode($user));
            
            // 检查用户余额是否满足最低要求
            if ($user['money'] < 200) {
                Log::error('Bot start: Insufficient balance for user: ' . $userid . ', balance: ' . $user['money']);
                
                $response = Response::create([
                    'code' => 0,
                    'msg'  => __('Insufficient balance. Minimum balance required is 200 USDT'),
                    'time' => time(),
                    'data' => null,
                ], 'json', 200);
                return $response;
            }
            
            // Check if bot is already running
            Log::info('Bot start: Checking if bot is already running for user: ' . $userid);
            $runningBot = Db::name("nft_purchase")
                ->where("user_id", $userid)
                ->where("source", 'Trading Bot')
                ->where("status", '1')
                ->find();
            
            if ($runningBot) {
                Log::info('Bot start: Bot already running for user: ' . $userid);
                $response = Response::create([
                    'code' => 0,
                    'msg'  => __('Bot is already running'),
                    'time' => time(),
                    'data' => null,
                ], 'json', 200);
                return $response;
            }
            
            // Create a bot purchase record
            Log::info('Bot start: Creating bot purchase record');
            try {
                $botId = Db::name("nft_purchase")->insertGetId([
                    'user_id' => $userid,
                    'reference' => $user['reference'],
                    'product_id' => 0, // No specific product ID for trading bot
                    'status' => '1', // Running   
                    'create_time' => time(), 
                    'botprice' => $user['money'], // Run
                    'source' => 'Trading Bot'
                ]);
                Log::info('Bot start: Created bot purchase record with ID: ' . $botId);
            } catch (\Exception $e) {
                Log::error('Bot start: Failed to create bot purchase record: ' . $e->getMessage());
                $response = Response::create([
                    'code' => 0,
                    'msg'  => __('Failed to create bot record'),
                    'time' => time(),
                    'data' => null,
                ], 'json', 200);
                return $response;
            }
            
              
            
            $response = Response::create([
                'code' => 1,
                'msg'  => __('Bot started successfully'),
                'time' => time(),
                'data' => null,
            ], 'json', 200);
            return $response;
    }

    /**
     * Stop the trading bot
     */
    public function stop(Request $request)
    {
        // 首先尝试从token获取userId
        $userid = $request->userId;
        
        // 如果没有userId，尝试从参数获取
        if (empty($userid)) {
            $userid = $request->param("userid");
            Log::info('Bot stop: Using userid from param: ' . ($userid ?: 'none'));
        } else {
            Log::info('Bot stop: Using userId from request: ' . $userid);
        }
        
        $sellBeforeStop = $request->param("sell_before_stop", true);
        
        if (empty($userid)) {
            $this->error(__('Please login first'));
        }
        
        $user = Db::name("user")->where("id", $userid)->find();
        if (!$user) {
            $this->error(__('User not found'));
        }
        
        // Find running bot
        $runningBot = Db::name("nft_purchase")
            ->where("user_id", $userid)
            ->where("source", 'Trading Bot')
            ->where("status", '1')
            ->find();
        
        if (!$runningBot) {
            $this->error(__('No running bot found'));
        }
        
         
        
        // Stop the bot
        Db::name("nft_purchase")
            ->where("id", $runningBot['id'])
            ->update([
                'status' => '0', // Stopped
                'create_time' => time(),
                'botprice' => 0, // Run
            ]);
        
        $this->success(__('Bot stopped successfully'));
    }

    /**
     * Manual sell operation
     */
    public function sell(Request $request)
    {
        // 首先尝试从token获取userId
        $userid = $request->userId;
        
        // 如果没有userId，尝试从参数获取
        if (empty($userid)) {
            $userid = $request->param("userid");
            Log::info('Bot sell: Using userid from param: ' . ($userid ?: 'none'));
        } else {
            Log::info('Bot sell: Using userId from request: ' . $userid);
        }
        
        if (empty($userid)) {
            $this->error(__('Please login first'));
        }
        
        $user = Db::name("user")->where("id", $userid)->find();
        if (!$user) {
            $this->error(__('User not found'));
        }
         
        
       
        // 获取请求参数
        $coinId = $request->param("coin_id");
        $coinSymbol = $request->param("coin_symbol");
        $amount = $request->param("amount", 0);
        $price = $request->param("price", 0);

         // Check if the last transaction was a buy
         $sumTransaction = Db::name("user_money_log")
         ->where("user_id", $userid)
         ->where("business_type", 'IN', ['Buy '.$coinSymbol, 'Sell '.$coinSymbol]) 
         ->where("type", 1)
         ->order("create_time", "desc")
         ->sum("currency_num");
       
        // Execute sell transaction 
        if($amount > $sumTransaction*$price){
            $this->error(__('Insufficient holding'.$sumTransaction.'-'.$amount));
        } 
        
        // 获取操作类型，默认为1（人工）
        $type = $request->param("type", 1);
        
        // 计算卖出的币数量
        $currency_num = $amount / $price;
        
        // Record the sell transaction
        Db::name("user_money_log")->insertGetId([
            'user_id' => $userid, 
            'money_type'=> "USDT",
            'business_type' => 'Sell ' . strtoupper($coinSymbol),
            'money' => -$amount, // Positive for earning
            'before' => $user['money'],
            'after' => $user['money'] + $amount,
            'create_time' => time(),
            'business_id' => 0,
            'memo' => 'Manual sell operation',
            'type' => $type, // 添加类型字段
            'currency' => strtoupper($coinSymbol), // Added currency field
            'price' => $price, // 卖出价格
            'currency_num' => $currency_num // 卖出币数量
        ]);
        
        // Update user balance
        Db::name("user")->where("id", $userid)->update([
            'money' => $user['money'] + $amount
        ]);
        
        $this->success(__('Sell operation completed successfully'), [
            'sellAmount' => $amount
        ]);
    }

    /**
     * Manual buy operation
     */
    public function buy(Request $request)
    {
        // 首先尝试从token获取userId
        $userid = $request->userId;
        
        // 如果没有userId，尝试从参数获取
        if (empty($userid)) {
            $userid = $request->param("userid");
            Log::info('Bot buy: Using userid from param: ' . ($userid ?: 'none'));
        } else {
            Log::info('Bot buy: Using userId from request: ' . $userid);
        }
        
        if (empty($userid)) {
            $this->error(__('Please login first'));
        }
        
        $user = Db::name("user")->where("id", $userid)->find();
        if (!$user) {
            $this->error(__('User not found'));
        }
        
        // 获取请求参数
        $coinId = $request->param("coin_id");
        $coinSymbol = $request->param("coin_symbol", "BTC");
        $amount = $request->param("amount", 0);
        $price = $request->param("price", 0);
        
        // 验证参数
        if (empty($coinId) || empty($coinSymbol) || $amount <= 0 || $price <= 0) {
            $this->error(__('Invalid parameters'));
        }
        
        // 检查用户余额
        if ($user['money'] < $amount) {
            $this->error(__('Insufficient balance'));
        } 
        
        // 获取操作类型，默认为1（人工）
        $type = $request->param("type", 1);
        
        // 计算购买的币数量
        $currency_num = $amount / $price;
        
        // 记录交易
        Db::name("user_money_log")->insertGetId([
            'user_id' => $userid,
            'money_type'=> "USDT",
            'business_type' => 'Buy ' . strtoupper($coinSymbol),
            'money' => $amount, // 负数表示支出
            'before' => $user['money'],
            'after' => $user['money'] - $amount,
            'create_time' => time(),
            'business_id' => 0,
            'memo' => 'Manual buy operation',
            'type' => $type, // 添加类型字段
            'currency' => strtoupper($coinSymbol), // Added currency field
            'price' => $price, // 当前交易价格
            'currency_num' => $currency_num // 购买的币数量
        ]);
        
        // 更新用户余额
        Db::name("user")->where("id", $userid)->update([
            'money' => $user['money'] - $amount
        ]);
        
        $this->success(__('Buy operation completed successfully'), [ 
            'amount' => $amount,
            'coinSymbol' => strtoupper($coinSymbol)
        ]);
    }

    /**
     * Check bot status
     */
    public function status(Request $request)
    {
        // 首先尝试从token获取userId
        $userid = $request->userId;
        
        // 如果没有userId，尝试从参数获取
        if (empty($userid)) {
            $userid = $request->param("userid");
            Log::info('Bot status: Using userid from param: ' . ($userid ?: 'none'));
        } else {
            Log::info('Bot status: Using userId from request: ' . $userid);
        }
        
        if (empty($userid)) {
            $this->error(__('Please login first'));
        }
        
        // Check if bot is running
        $runningBot = Db::name("nft_purchase")
            ->where("user_id", $userid)
            ->where("source", 'Trading Bot')
            ->where("status", '1')
            ->find();
        
        $isRunning = !empty($runningBot);
        $lastOperationBuy = false;
        $currentAmount = 0; // Default amount
        
        if ($isRunning) {
            // Check last operation
            $lastTransaction = Db::name("user_money_log")
                ->where("user_id", $userid) 
                ->where("business_id", $runningBot['id'])
                ->where("type", 2)
                ->order("create_time", "desc")
                ->find();
            
            if ($lastTransaction) {
             
                // Last operation was sell, find most recent buy amount
                $recentBuy = Db::name("user_money_log")
                    ->where("user_id", $userid)
                    ->where("business_id", $runningBot['id'])
                    ->order("create_time", "desc") 
                    ->find();
                
                $currentAmount = $recentBuy ? abs($recentBuy['money']) : 0; 
            }
        }
        
        $this->success('', [
            'isRunning' => $isRunning,
            'lastOperationBuy' => $lastOperationBuy,
            'currentAmount' => $currentAmount,
            'botId' => $runningBot ? $runningBot['id'] : 0
        ]);
    }

    /**
     * Get holding information
     * 计算用户当前持仓总额(类型为1的人工交易)
     */
    public function holding(Request $request)
    {
        // 首先尝试从token获取userId
        $userid = $request->userId;
        
        // 如果没有userId，尝试从参数获取
        if (empty($userid)) {
            $userid = $request->param("userid");
            Log::info('Bot holding: Using userid from param: ' . ($userid ?: 'none'));
        } else {
            Log::info('Bot holding: Using userId from request: ' . $userid);
        }
        
        if (empty($userid)) {
            $this->error(__('Please login first'));
        }
        
        // 默认值
        $totalHolding = 0.00;
        $holdings = [];
        
        if (!empty($userid)) {
            // 获取所有交易对列表
            $currencies = Db::name("user_money_log")
                ->where("user_id", $userid)
                ->where("type", 1) // 人工交易
                ->where("currency", "<>", "")
                ->where("currency", "<>", null)
                ->group("currency")
                ->column("currency");
            
            // 遍历每个交易对
            foreach ($currencies as $currency) {
                // 计算买入总币数
                $buyRecords = Db::name("user_money_log")
                    ->field("SUM(currency_num) as total_buy")
                    ->where("user_id", $userid)
                    ->where("business_type", 'like', 'Buy %')
                    ->where("currency", $currency)
                    ->where("type", 1) // 人工交易
                    ->find();
                
                $totalBuy = $buyRecords ? floatval($buyRecords['total_buy']) : 0;
                
                // 计算卖出总币数
                $sellRecords = Db::name("user_money_log")
                    ->field("SUM(currency_num) as total_sell")
                    ->where("user_id", $userid)
                    ->where("business_type", 'like', 'Sell %')
                    ->where("currency", $currency)
                    ->where("type", 1) // 人工交易
                    ->find();
                
                $totalSell = $sellRecords ? floatval($sellRecords['total_sell']) : 0;
                
                // 计算持仓数量 = 买入币数 - 卖出币数
                $holdingAmount = $totalBuy - $totalSell;
                
                if ($holdingAmount > 0) {
                    // 获取最近一次交易的价格作为当前价格
                    $latestPrice = Db::name("user_money_log")
                        ->where("user_id", $userid)
                        ->where("currency", $currency)
                        ->where("price", ">", 0)
                        ->where("type",1)
                        ->order("create_time", "desc")
                        ->value("price");
                    
                    // 如果没有价格记录，使用默认值
                    $currentPrice = $latestPrice ?: 0;
                    
                    // 计算持仓价值 = 持仓数量 * 当前价格
                    $holdingValue = $holdingAmount * $currentPrice;
                    
                    $holdings[] = [
                        'symbol' => $currency,
                        'amount' => number_format($holdingAmount, 8), // 币数量保留8位小数
                        'price' => number_format($currentPrice, 2), // 当前价格
                        'value' => number_format($holdingValue, 2) // 持仓价值（USDT）
                    ];
                    
                    $totalHolding += $holdingValue;
                }
            }
            
            // 格式化总持仓
            $totalHolding = number_format($totalHolding, 2);
        }
        
        $this->success('', [
            'totalHolding' => $totalHolding,
            'holdings' => $holdings
        ]);
    }

} 