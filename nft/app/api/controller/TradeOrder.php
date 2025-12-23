<?php

namespace app\api\controller;

use think\facade\Db;
use think\Request;
use app\common\controller\Frontend;
use think\facade\Log;

class TradeOrder extends Frontend
{
    protected array $noNeedLogin = ['placeOrderGuest', 'pendingOrdersGuest', 'historyOrdersGuest', 'checkOrderStatus'];

    protected array $noNeedPermission = ['*'];

    /**
     * 交易时间配置 设置不同时间不同倍率
     */
    private $tradeTimeConfig = [ 
        'M1' => ['seconds' => 60, 'rate' => 92],
        'M2' => ['seconds' => 120, 'rate' => 92],
        'M3' => ['seconds' => 180, 'rate' => 92],
        'M5' => ['seconds' => 300, 'rate' => 92],
        'M10' => ['seconds' => 600, 'rate' => 92],
        'M15' => ['seconds' => 900, 'rate' => 92],
    ];

    /**
     * 下单接口
     * 创建买涨/买跌订单
     */
    public function placeOrder(Request $request)
    {
        $userId = $request->userId;

        if (empty($userId)) {
            return $this->error('User ID is required');
        }

        // 获取请求参数
        $coinId = $request->param('coin_id', '');
        $tradingSymbol = $request->param('trading_symbol', '');
        $coinSymbol = $request->param('coin_symbol', '');
        $tradeType = $request->param('trade_type', ''); // up 或 down
        $amount = $request->param('amount', 0);
        $tradeTime = $request->param('trade_time', '30S'); // 30S, M1, M2, M3
        $moneyType = $request->param('money_type', 1); // 1=真实交易, 2=模拟交易

        // 参数验证
        if (empty($coinId)) {
            return $this->error('Coin ID is required');
        }
        if (empty($tradingSymbol)) {
            return $this->error('Trading symbol is required');
        }
        if (empty($coinSymbol)) {
            return $this->error('Coin symbol is required');
        }
        if (!in_array($tradeType, ['up', 'down'])) {
            return $this->error('Invalid trade type');
        }
        if ($amount <= 0) {
            return $this->error('Amount must be greater than 0');
        }
        if (!isset($this->tradeTimeConfig[$tradeTime])) {
            return $this->error('Invalid trade time');
        }
        if (!in_array($moneyType, [1, 2])) {
            return $this->error('Invalid money type');
        }

        // 从 Binance API 获取当前价格（防止前端篡改）
        $entryPrice = $this->getBinancePrice($tradingSymbol);
        if (!$entryPrice || $entryPrice <= 0) {
            return $this->error('Failed to get current price, please try again');
        }

        // 获取用户信息，检查余额
        $user = Db::name('user')->where('id', $userId)->find();
        if (!$user) {
            return $this->error('User not found');
        }

        // 根据交易类型检查对应余额
        $balanceField = $moneyType == 1 ? 'money' : 'virtualmoney';
        $userBalance = $user[$balanceField];
        if ($userBalance-$amount< 0) {
            return $this->error($moneyType == 1 ? 'Insufficient balance' : 'Insufficient virtual balance');
        }

        // 获取交易时间配置
        $timeConfig = $this->tradeTimeConfig[$tradeTime];
        $totalSeconds = $timeConfig['seconds'];
        $profitRate = $timeConfig['rate'];

        // 计算到期时间
        $now = time();
        $expiredAt = date('Y-m-d H:i:s', $now + $totalSeconds);

        // 开启事务
        Db::startTrans();
        try {
             
            $deductAmount = $amount;
            Db::name('user')->where('id', $userId)->dec($balanceField, $deductAmount)->update();

            // 创建订单
            $orderId = Db::name('trade_orders')->insertGetId([
                'user_id' => $userId,
                'coin_id' => $coinId,
                'trading_symbol' => $tradingSymbol,
                'coin_symbol' => $coinSymbol,
                'trade_type' => $tradeType,
                'amount' => $amount,
                'profit_rate' => $profitRate,
                'trade_time' => $tradeTime,
                'total_seconds' => $totalSeconds,
                'remaining_seconds' => $totalSeconds,
                'entry_price' => $entryPrice,
                'money_type' => $moneyType, // 1=真实交易, 2=模拟交易
                'status' => 1, // 进行中
                'created_at' => date('Y-m-d H:i:s', $now),
                'expired_at' => $expiredAt
            ]);

            // 记录资金流水（只记录真实交易）
            if ($moneyType == 1) {
                Db::name('user_money_log')->insert([
                    'user_id' => $userId,
                    'money' => -$deductAmount,
                    'before' => $user['money'],
                    'after' => $user['money'] - $deductAmount,
                    'business_type' => 'Trade Order',
                    'business_id' => $orderId,
                    'memo' => ($tradeType == 'up' ? '买涨' : '买跌') . ' ' . $coinSymbol . ' $' . $amount,
                    'create_time' => $now
                ]);
            }

            Db::commit();

            // 返回订单信息
            return $this->success('Order placed successfully', [
                'order_id' => $orderId,
                'total_seconds' => $totalSeconds,
                'remaining_seconds' => $totalSeconds,
                'expired_at' => $expiredAt,
                'profit_rate' => $profitRate,
                'entry_price' => $entryPrice
            ]);

        } catch (\think\exception\HttpResponseException $e) {
            // 正常的响应异常，直接抛出
            throw $e;
        } catch (\Exception $e) {
            Db::rollback();
            // 记录详细错误日志
            trace('Order failed - User: ' . $userId . ', Error: ' . $e->getMessage() . ', File: ' . $e->getFile() . ', Line: ' . $e->getLine(), 'error');
            return $this->error('Order failed: ' . $e->getMessage());
        }
    }

    /**
     * 获取当前委托订单
     * 返回进行中的订单列表
     */
    public function pendingOrders(Request $request)
    {
        $userId = $request->userId;

        if (empty($userId)) {
            return $this->error('User ID is required');
        }

        $coinId = $request->param('coin_id', '');
        $moneyType = $request->param('money_type', 1); // 1=真实交易, 2=模拟交易

        // 先自动结算该用户到期的订单
        $this->autoSettleExpiredOrders($userId);

        // 查询进行中的订单
        $query = Db::name('trade_orders')
            ->where('user_id', $userId)
            //->where('money_type', $moneyType)
            ->whereIn('status', [0, 1]); // 待处理和进行中

        if (!empty($coinId)) {
            $query->where('coin_id', $coinId);
        }

        $orders = $query->order('created_at', 'asc')
            ->select()
            ->toArray();

        // 更新剩余时间
        $now = time();
        foreach ($orders as &$order) {
            $expiredAt = strtotime($order['expired_at']);
            $remaining = max(0, $expiredAt - $now);
            $order['remaining_seconds'] = $remaining;

            // 格式化时间
            $order['created_at_formatted'] = date('Y-m-d H:i:s', strtotime($order['created_at']));
        }

        return $this->success('', [
            'orders' => $orders,
            'total' => count($orders)
        ]);
    }

    /**
     * 获取历史订单
     * 返回已结算或已取消的订单列表
     */
    public function historyOrders(Request $request)
    {
        $userId = $request->userId;

        if (empty($userId)) {
            return $this->error('User ID is required');
        }

        $page = $request->param('page', 1);
        $limit = $request->param('limit', 20);
        $coinId = $request->param('coin_id', '');
        $moneyType = $request->param('money_type', 0); // 1=真实交易, 2=模拟交易, 0=全部

        $offset = ($page - 1) * $limit;

        // 构建查询
        $query = Db::name('trade_orders')
            ->where('user_id', $userId)
            ->whereIn('status', [2, 3]); // 已结算和已取消

        // 根据交易类型过滤
        // if (!empty($moneyType) && in_array($moneyType, [1, 2])) {
        //     $query->where('money_type', $moneyType);
        // }

        if (!empty($coinId)) {
            $query->where('coin_id', $coinId);
        }

        // 获取总数
        $total = $query->count();

        // 获取订单列表
        $orders = $query->order('created_at', 'desc')
            ->limit($offset, $limit)
            ->select()
            ->toArray();

        // 格式化订单数据
        foreach ($orders as &$order) {
            $order['created_at_formatted'] = date('Y-m-d H:i:s', strtotime($order['created_at']));
            if ($order['settled_at']) {
                $order['settled_at_formatted'] = date('Y-m-d H:i:s', strtotime($order['settled_at']));
            }

            // 计算实际盈亏
            if ($order['result'] == 'win') {
                $order['profit_loss_display'] = '+' . number_format($order['profit_loss'], 2);
            } elseif ($order['result'] == 'lose') {
                $order['profit_loss_display'] = number_format($order['profit_loss'], 2);
            } else {
                $order['profit_loss_display'] = '0.00';
            }
        }

        return $this->success('', [
            'orders' => $orders,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ]);
    }

    /**
     * 获取订单详情
     */
    public function orderDetail(Request $request)
    {
        $userId = $request->userId;

        if (empty($userId)) {
            return $this->error('User ID is required');
        }

        $orderId = $request->param('order_id', 0);

        if (empty($orderId)) {
            return $this->error('Order ID is required');
        }

        $order = Db::name('trade_orders')
            ->where('id', $orderId)
            ->where('user_id', $userId)
            ->find();

        if (!$order) {
            return $this->error('Order not found');
        }

        // 更新剩余时间
        if (in_array($order['status'], [0, 1])) {
            $now = time();
            $expiredAt = strtotime($order['expired_at']);
            $order['remaining_seconds'] = max(0, $expiredAt - $now);
        }

        // 格式化时间
        $order['created_at_formatted'] = date('Y-m-d H:i:s', strtotime($order['created_at']));
        if ($order['settled_at']) {
            $order['settled_at_formatted'] = date('Y-m-d H:i:s', strtotime($order['settled_at']));
        }

        return $this->success('', [
            'order' => $order
        ]);
    }

    /**
     * 取消订单
     * 只能取消待处理的订单
     */
    public function cancelOrder(Request $request)
    {
        $userId = $request->userId;

        if (empty($userId)) {
            return $this->error('User ID is required');
        }

        $orderId = $request->param('order_id', 0);

        if (empty($orderId)) {
            return $this->error('Order ID is required');
        }

        // 查询订单
        $order = Db::name('trade_orders')
            ->where('id', $orderId)
            ->where('user_id', $userId)
            ->find();

        if (!$order) {
            return $this->error('Order not found');
        }

        // 只能取消待处理的订单
        if ($order['status'] != 0) {
            return $this->error('Only pending orders can be cancelled');
        }

        // 开启事务
        Db::startTrans();
        try {
            // 更新订单状态
            Db::name('trade_orders')
                ->where('id', $orderId)
                ->update([
                    'status' => 3,
                    'settled_at' => date('Y-m-d H:i:s')
                ]);

            // 退还用户余额
            $refundAmount = $order['amount'];
            $user = Db::name('user')->where('id', $userId)->find();

            Db::name('user')->where('id', $userId)->inc('money', $refundAmount)->update();

            // 记录资金流水
            Db::name('user_money_log')->insert([
                'user_id' => $userId,
                'money' => $refundAmount,
                'before' => $user['money'],
                'after' => $user['money'] + $refundAmount,
                'business_type' => 'Order Cancelled',
                'business_typeid' => 7,
                'memo' => '订单取消退款 #' . $orderId,
                'create_time' => time()
            ]);

            Db::commit();

            return $this->success('Order cancelled successfully');

        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('Cancel failed: ' . $e->getMessage());
        }
    }

    /**
     * 自动结算用户到期的订单
     * 在获取委托订单时调用，确保到期订单被及时结算（作为cron的备用方案）
     */
    private function autoSettleExpiredOrders($userId)
    {
        $debug = [];
        $currentTime = date('Y-m-d H:i:s');
        $debug['current_time'] = $currentTime;
        $debug['user_id'] = $userId;

        try {
            // 获取该用户所有到期但未结算的订单
            $expiredOrders = Db::name('trade_orders')
                ->where('user_id', $userId)
                ->whereIn('status', [0, 1])
                ->where('expired_at', '<=', $currentTime)
                ->select()
                ->toArray();

            $debug['expired_count'] = count($expiredOrders);
            $debug['expired_ids'] = array_column($expiredOrders, 'id');
            $debug['expired_times'] = array_column($expiredOrders, 'expired_at');

            if (empty($expiredOrders)) {
                $debug['result'] = 'no_expired_orders';
                return $debug;
            }

        $now = date('Y-m-d H:i:s');
        $debug['settled'] = [];
        $debug['errors'] = [];

        foreach ($expiredOrders as $order) {
            try {
                // 获取当前价格
                $exitPrice = $this->getBinancePrice($order['trading_symbol']);

                if (!$exitPrice) {
                    $debug['errors'][] = [
                        'order_id' => $order['id'],
                        'type' => 'price_fetch_failed',
                        'symbol' => $order['trading_symbol']
                    ];
                    continue;
                }

                // 判断输赢
                $result = 'draw';
                $profitLoss = 0;

                if ($order['trade_type'] == 'up') {
                    if ($exitPrice > $order['entry_price']) {
                        $result = 'win';
                        $profitLoss = $order['amount'] * ($order['profit_rate'] / 100);
                    } elseif ($exitPrice < $order['entry_price']) {
                        $result = 'lose';
                        $profitLoss = -$order['amount'];
                    }
                } else {
                    if ($exitPrice < $order['entry_price']) {
                        $result = 'win';
                        $profitLoss = $order['amount'] * ($order['profit_rate'] / 100);
                    } elseif ($exitPrice > $order['entry_price']) {
                        $result = 'lose';
                        $profitLoss = -$order['amount'];
                    }
                }

                // 开启事务
                Db::startTrans();
                try {
                    // 使用乐观锁更新订单，只有状态为0或1时才更新（防止并发重复结算）
                    $updateResult = Db::name('trade_orders')
                        ->where('id', $order['id'])
                        ->whereIn('status', [0, 1])  // 关键：添加状态检查防止并发
                        ->update([
                            'exit_price' => $exitPrice,
                            'profit_loss' => $profitLoss,
                            'result' => $result,
                            'status' => 2,
                            'settled_at' => $now
                        ]);

                    // 如果没有更新任何行，说明订单已被其他请求处理
                    if ($updateResult === 0) {
                        $debug['errors'][] = [
                            'order_id' => $order['id'],
                            'type' => 'already_processed'
                        ];
                        Db::rollback();
                        continue;
                    }

                    // 判断是否为真实交易
                    $isRealTrade = ($order['money_type'] == 1);
                    $balanceField = $isRealTrade ? 'money' : 'virtualmoney';

                    // 获取用户信息
                    $user = Db::name('user')->where('id', $order['user_id'])->find();

                    // 根据结果处理资金
                    if ($result == 'win') {
                        $returnAmount = $order['amount'] + $profitLoss;
                        Db::name('user')->where('id', $order['user_id'])->inc($balanceField, $returnAmount)->update();

                        if ($isRealTrade) {
                            Db::name('user_money_log')->insert([
                                'user_id' => $order['user_id'],
                                'money' => $returnAmount,
                                'before' => $user['money'],
                                'after' => $user['money'] + $returnAmount,
                                'business_type' => 'Trade Win',
                                'business_id' => $order['id'],
                                'memo' => '交易获胜 ' . $order['coin_symbol'] . ' +$' . number_format($order['amount'] + $profitLoss, 2),
                                'create_time' => time()
                            ]);
                        }
                    } elseif ($result == 'draw') {
                        $returnAmount = $order['amount'];
                        Db::name('user')->where('id', $order['user_id'])->inc($balanceField, $returnAmount)->update();

                        if ($isRealTrade) {
                            Db::name('user_money_log')->insert([
                                'user_id' => $order['user_id'],
                                'money' => $returnAmount,
                                'before' => $user['money'],
                                'after' => $user['money'] + $returnAmount,
                                'business_type' => 'Trade Draw',
                                'business_id' => $order['id'],
                                'memo' => '交易平局返还 ' . $order['coin_symbol'] . ' $' . $order['amount'],
                                'create_time' => time()
                            ]);
                        }
                    } elseif ($result == 'lose' && $isRealTrade) {
                        // 用户亏损时，给邀请人返利（仅真实交易）
                        $this->processInviteRebate($order, $user);
                    }

                    Db::commit();
                    $debug['settled'][] = [
                        'order_id' => $order['id'],
                        'result' => $result,
                        'exit_price' => $exitPrice,
                        'profit_loss' => $profitLoss
                    ];

                } catch (\Exception $e) {
                    Db::rollback();
                    $debug['errors'][] = [
                        'order_id' => $order['id'],
                        'type' => 'transaction_error',
                        'message' => $e->getMessage()
                    ];
                }

            } catch (\Exception $e) {
                $debug['errors'][] = [
                    'order_id' => $order['id'],
                    'type' => 'process_error',
                    'message' => $e->getMessage()
                ];
            }
        }

        $debug['result'] = 'completed';
        return $debug;

        } catch (\Exception $e) {
            $debug['result'] = 'fatal_error';
            $debug['error'] = $e->getMessage();
            return $debug;
        }
    }

    /**
     * 从币安合约API获取当前价格
     */
    private function getBinancePrice($symbol)
    {
        try {
            // 使用合约API (fapi) 而不是现货API (api)
            $url = "https://fapi.binance.com/fapi/v1/ticker/price?symbol=" . $symbol;
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5
                ]
            ]);
            $response = @file_get_contents($url, false, $context);
            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['price'])) {
                    return floatval($data['price']);
                }
            }
        } catch (\Exception $e) {
            // 记录错误日志
            trace("获取币安合约价格失败: " . $e->getMessage(), 'error');
        }
        return null;
    }

    /**
     * 处理邀请返利
     * 当被邀请用户亏损时，给邀请人返利亏损金额的10%
     */
    private function processInviteRebate($order, $user)
    {
        // 检查用户是否有邀请人（reference字段存储邀请人ID）
        if (empty($user['reference'])) {
            return;
        }

        $inviterId = $user['reference'];

        // 获取邀请人信息
        $inviter = Db::name('user')->where('id', $inviterId)->find();
        if (!$inviter) {
            return;
        }

        // 计算返利金额：亏损金额的10%
        $lossAmount = $order['amount'];
        $rebateRate = 0.10;
        $rebateAmount = $lossAmount * $rebateRate;

        if ($rebateAmount <= 0) {
            return;
        }

        // 获取被邀请用户的邮箱（用于memo记录）
        $inviteeEmail = $user['email'] ?: $user['username'] ?: ('用户ID:' . $user['id']);

        // 给邀请人增加余额
        $inviterBalanceBefore = $inviter['money'];
        $inviterBalanceAfter = $inviterBalanceBefore + $rebateAmount;

        Db::name('user')->where('id', $inviterId)->inc('money', $rebateAmount)->update();

        // 记录邀请人的资金流水
        Db::name('user_money_log')->insert([
            'user_id' => $inviterId,
            'money' => $rebateAmount,
            'before' => $inviterBalanceBefore,
            'after' => $inviterBalanceAfter,
            'business_type' => 'Referral Reward',
            'business_id' => $order['id'],
            'memo' => '邀请返利 - 来自 ' . $inviteeEmail . ' 的交易亏损 $' . number_format($lossAmount, 2) . ' (返利10%: +$' . number_format($rebateAmount, 2) . ')',
            'create_time' => time()
        ]);
    }

    /**
     * 游客下单接口（模拟交易）
     * 根据device_id获取临时用户并下单
     */
    public function placeOrderGuest(Request $request)
    {
        $deviceId = $request->param('device_id', '');

        if (empty($deviceId)) {
            return $this->error('Device ID is required');
        }

        // 根据device_id查找临时用户
        $user = Db::name('user')
            ->where('device_id', $deviceId)
            ->where('is_guest', 1)
            ->find();

        if (!$user) {
            return $this->error('Guest user not found, please init first');
        }

        $userId = $user['id'];

        // 获取请求参数
        $coinId = $request->param('coin_id', '');
        $tradingSymbol = $request->param('trading_symbol', '');
        $coinSymbol = $request->param('coin_symbol', '');
        $tradeType = $request->param('trade_type', ''); // up 或 down
        $amount = $request->param('amount', 0);
        $tradeTime = $request->param('trade_time', 'M1');

        // 参数验证
        if (empty($coinId)) {
            return $this->error('Coin ID is required');
        }
        if (empty($tradingSymbol)) {
            return $this->error('Trading symbol is required');
        }
        if (empty($coinSymbol)) {
            return $this->error('Coin symbol is required');
        }
        if (!in_array($tradeType, ['up', 'down'])) {
            return $this->error('Invalid trade type');
        }
        if ($amount <= 0) {
            return $this->error('Amount must be greater than 0');
        }
        if (!isset($this->tradeTimeConfig[$tradeTime])) {
            return $this->error('Invalid trade time');
        }

        // 从 Binance API 获取当前价格（防止前端篡改）
        $entryPrice = $this->getBinancePrice($tradingSymbol);
        if (!$entryPrice || $entryPrice <= 0) {
            return $this->error('Failed to get current price, please try again');
        }

        // 检查虚拟余额
        $userBalance = floatval($user['virtualmoney']);
        $amount = floatval($amount);
        if ($userBalance - $amount < 0) {
            return $this->error('Insufficient virtual balance, current: ' . $userBalance . ', need: ' . $amount);
        }

        // 获取交易时间配置
        $timeConfig = $this->tradeTimeConfig[$tradeTime];
        $totalSeconds = $timeConfig['seconds'];
        $profitRate = $timeConfig['rate'];

        // 计算到期时间
        $now = time();
        $expiredAt = date('Y-m-d H:i:s', $now + $totalSeconds);

        // 开启事务
        Db::startTrans();
        try {
            // 扣减虚拟余额
            Db::name('user')->where('id', $userId)->dec('virtualmoney', $amount)->update();

            // 创建订单（模拟交易）
            $orderData = [
                'user_id' => $userId,
                'coin_id' => $coinId,
                'trading_symbol' => $tradingSymbol,
                'coin_symbol' => $coinSymbol,
                'trade_type' => $tradeType,
                'amount' => $amount,
                'profit_rate' => $profitRate,
                'trade_time' => $tradeTime,
                'total_seconds' => $totalSeconds,
                'remaining_seconds' => $totalSeconds,
                'entry_price' => $entryPrice,
                'money_type' => 2, // 模拟交易
                'status' => 1, // 进行中
                'created_at' => date('Y-m-d H:i:s', $now),
                'expired_at' => $expiredAt
            ];

            $orderId = Db::name('trade_orders')->insertGetId($orderData);

            Db::commit();

            // 获取更新后的余额
            $newBalance = Db::name('user')->where('id', $userId)->value('virtualmoney');

            // 返回订单信息
            return $this->success('Order placed successfully', [
                'order_id' => $orderId,
                'total_seconds' => $totalSeconds,
                'remaining_seconds' => $totalSeconds,
                'expired_at' => $expiredAt,
                'profit_rate' => $profitRate,
                'virtualmoney' => $newBalance,
                'entry_price' => $entryPrice
            ]);

        } catch (\think\exception\HttpResponseException $e) {
            // 正常的响应异常，直接抛出（不回滚，因为事务已提交）
            throw $e;
        } catch (\think\db\exception\DbException $e) {
            Db::rollback();
            trace('Guest order DB error: ' . $e->getMessage(), 'error');
            return $this->error('Order failed (DB): ' . $e->getMessage());
        } catch (\PDOException $e) {
            Db::rollback();
            trace('Guest order PDO error: ' . $e->getMessage(), 'error');
            return $this->error('Order failed (PDO): ' . $e->getMessage());
        } catch (\Exception $e) {
            Db::rollback();
            // 记录详细错误日志
            trace('Guest order failed - DeviceId: ' . $deviceId . ', UserId: ' . $userId . ', Error: ' . $e->getMessage() . ', File: ' . $e->getFile() . ', Line: ' . $e->getLine(), 'error');
            return $this->error('Order failed: ' . $e->getMessage());
        }
    }

    /**
     * 游客获取当前委托订单
     */
    public function pendingOrdersGuest(Request $request)
    {
        $deviceId = $request->param('device_id', '');

        if (empty($deviceId)) {
            return $this->error('Device ID is required');
        }

        // 根据device_id查找临时用户
        $user = Db::name('user')
            ->where('device_id', $deviceId)
            ->where('is_guest', 1)
            ->find();

        if (!$user) {
            // 游客用户不存在时，返回空订单列表（而不是报错）
            return $this->success('', [
                'orders' => [],
                'total' => 0,
                'virtualmoney' => 0
            ]);
        }

        $userId = $user['id'];
        $coinId = $request->param('coin_id', '');

        // 先自动结算该用户到期的订单（游客也需要自动结算）
        $this->autoSettleExpiredOrdersGuest($userId);

        // 查询进行中的订单（仅模拟交易）
        $query = Db::name('trade_orders')
            ->where('user_id', $userId)
            ->where('money_type', 2)
            ->whereIn('status', [0, 1]);

        if (!empty($coinId)) {
            $query->where('coin_id', $coinId);
        }

        $orders = $query->order('created_at', 'asc')
            ->select()
            ->toArray();

        // 更新剩余时间
        $now = time();
        foreach ($orders as &$order) {
            $expiredAt = strtotime($order['expired_at']);
            $remaining = max(0, $expiredAt - $now);
            $order['remaining_seconds'] = $remaining;
            $order['created_at_formatted'] = date('Y-m-d H:i:s', strtotime($order['created_at']));
        }

        // 获取最新的虚拟余额
        $currentBalance = Db::name('user')->where('id', $userId)->value('virtualmoney');

        return $this->success('', [
            'orders' => $orders,
            'total' => count($orders),
            'virtualmoney' => $currentBalance
        ]);
    }

    /**
     * 检查单个订单状态
     * 用于前端轮询确认订单是否已结算
     */
    public function checkOrderStatus(Request $request)
    {
        $orderId = $request->param('order_id', '');
        $deviceId = $request->param('device_id', '');

        if (empty($orderId)) {
            return $this->error('Order ID is required');
        }

        // 构建查询
        $query = Db::name('trade_orders')->where('id', $orderId);

        // 如果提供了 device_id，验证是游客订单
        if (!empty($deviceId)) {
            $user = Db::name('user')
                ->where('device_id', $deviceId)
                ->where('is_guest', 1)
                ->find();

            if (!$user) {
                return $this->error('Guest user not found');
            }
            $query->where('user_id', $user['id']);
        } else {
            // 登录用户，从 token 获取 user_id
            $userId = $this->auth->id ?? 0;
            if ($userId > 0) {
                $query->where('user_id', $userId);
            }
        }

        $order = $query->find();

        if (!$order) {
            return $this->error('Order not found');
        }

        // 返回订单状态
        return $this->success('', [
            'id' => $order['id'],
            'status' => $order['status'],
            'result' => $order['result'] ?? null,
            'profit_loss' => $order['profit_loss'] ?? 0,
            'exit_price' => $order['exit_price'] ?? null,
            'settled_at' => $order['settled_at'] ?? null
        ]);
    }

    /**
     * 自动结算游客到期的订单（仅模拟交易）
     */
    private function autoSettleExpiredOrdersGuest($userId)
    {
        $currentTime = date('Y-m-d H:i:s');

        try {
            // 获取该用户所有到期但未结算的模拟交易订单
            $expiredOrders = Db::name('trade_orders')
                ->where('user_id', $userId)
                ->where('money_type', 2) // 仅模拟交易
                ->whereIn('status', [0, 1])
                ->where('expired_at', '<=', $currentTime)
                ->select()
                ->toArray();

            if (empty($expiredOrders)) {
                return;
            }

            $now = date('Y-m-d H:i:s');

            foreach ($expiredOrders as $order) {
                try {
                    // 获取当前价格
                    $exitPrice = $this->getBinancePrice($order['trading_symbol']);

                    if (!$exitPrice) {
                        continue;
                    }

                    // 判断输赢
                    $result = 'draw';
                    $profitLoss = 0;

                    if ($order['trade_type'] == 'up') {
                        if ($exitPrice > $order['entry_price']) {
                            $result = 'win';
                            $profitLoss = $order['amount'] * ($order['profit_rate'] / 100);
                        } elseif ($exitPrice < $order['entry_price']) {
                            $result = 'lose';
                            $profitLoss = -$order['amount'];
                        }
                    } else {
                        if ($exitPrice < $order['entry_price']) {
                            $result = 'win';
                            $profitLoss = $order['amount'] * ($order['profit_rate'] / 100);
                        } elseif ($exitPrice > $order['entry_price']) {
                            $result = 'lose';
                            $profitLoss = -$order['amount'];
                        }
                    }

                    // 开启事务
                    Db::startTrans();
                    try {
                        // 使用乐观锁更新订单
                        $updateResult = Db::name('trade_orders')
                            ->where('id', $order['id'])
                            ->whereIn('status', [0, 1])
                            ->update([
                                'exit_price' => $exitPrice,
                                'profit_loss' => $profitLoss,
                                'result' => $result,
                                'status' => 2,
                                'settled_at' => $now
                            ]);

                        if ($updateResult === 0) {
                            Db::rollback();
                            continue;
                        }

                        // 根据结果处理虚拟资金
                        if ($result == 'win') {
                            $returnAmount = $order['amount'] + $profitLoss;
                            Db::name('user')->where('id', $userId)->inc('virtualmoney', $returnAmount)->update();
                        } elseif ($result == 'draw') {
                            $returnAmount = $order['amount'];
                            Db::name('user')->where('id', $userId)->inc('virtualmoney', $returnAmount)->update();
                        }
                        // lose 的情况不需要返还

                        Db::commit();

                    } catch (\Exception $e) {
                        Db::rollback();
                    }

                } catch (\Exception $e) {
                    // 忽略单个订单的错误，继续处理其他订单
                }
            }

        } catch (\Exception $e) {
            // 忽略错误
        }
    }

    /**
     * 游客获取历史订单
     */
    public function historyOrdersGuest(Request $request)
    {
        $deviceId = $request->param('device_id', '');

        if (empty($deviceId)) {
            return $this->error('Device ID is required');
        }

        // 根据device_id查找临时用户
        $user = Db::name('user')
            ->where('device_id', $deviceId)
            ->where('is_guest', 1)
            ->find();

        if (!$user) {
            // 游客用户不存在时，返回空订单列表（而不是报错）
            return $this->success('', [
                'orders' => [],
                'total' => 0,
                'page' => 1,
                'limit' => 20,
                'virtualmoney' => 0
            ]);
        }

        $userId = $user['id'];
        $page = $request->param('page', 1);
        $limit = $request->param('limit', 20);
        $coinId = $request->param('coin_id', '');

        $offset = ($page - 1) * $limit;

        // 构建查询（仅模拟交易）
        $query = Db::name('trade_orders')
            ->where('user_id', $userId)
            ->where('money_type', 2)
            ->whereIn('status', [2, 3]);

        if (!empty($coinId)) {
            $query->where('coin_id', $coinId);
        }

        // 获取总数
        $total = $query->count();

        // 获取订单列表
        $orders = $query->order('created_at', 'desc')
            ->limit($offset, $limit)
            ->select()
            ->toArray();

        // 格式化订单数据
        foreach ($orders as &$order) {
            $order['created_at_formatted'] = date('Y-m-d H:i:s', strtotime($order['created_at']));
            if ($order['settled_at']) {
                $order['settled_at_formatted'] = date('Y-m-d H:i:s', strtotime($order['settled_at']));
            }

            if ($order['result'] == 'win') {
                $order['profit_loss_display'] = '+' . number_format($order['profit_loss'], 2);
            } elseif ($order['result'] == 'lose') {
                $order['profit_loss_display'] = number_format($order['profit_loss'], 2);
            } else {
                $order['profit_loss_display'] = '0.00';
            }
        }

        return $this->success('', [
            'orders' => $orders,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'virtualmoney' => $user['virtualmoney']
        ]);
    }
}
