<?php

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Db;

class SettleOrders extends Command
{
    protected function configure()
    {
        $this->setName('settle:orders')
            ->setDescription('自动结算到期的交易订单');
    }

    protected function execute(Input $input, Output $output)
    {
        // 进程锁文件
        $lockFile = runtime_path() . 'settle_orders.lock';

        // 检查是否已有进程在运行
        if (file_exists($lockFile)) {
            $pid = file_get_contents($lockFile);
            // 检查进程是否还在运行（Linux）
            if (file_exists("/proc/{$pid}")) {
                $output->writeln('已有结算进程在运行，退出');
                return 0;
            }
        }

        // 写入当前进程ID
        file_put_contents($lockFile, getmypid());

        $output->writeln('开始订单结算守护进程（运行60秒）...');

        try {
            // 运行60秒，每秒检查一次
            for ($i = 0; $i < 60; $i++) {
                $this->settleExpiredOrders($output);

                // 最后一次不需要等待
                if ($i < 59) {
                    sleep(1);
                }
            }
        } finally {
            // 删除锁文件
            @unlink($lockFile);
        }

        $output->writeln('本轮结算完成');
        return 0;
    }

    /**
     * 结算到期订单
     */
    private function settleExpiredOrders(Output $output)
    {
        // 获取所有到期但未结算的订单
        $expiredOrders = Db::name('trade_orders')
            ->whereIn('status', [0, 1])
            ->where('expired_at', '<=', date('Y-m-d H:i:s'))
            ->select()
            ->toArray();

        if (empty($expiredOrders)) {
            $output->writeln(date('H:i:s') . ' - 无到期订单');
            return;
        }

        $output->writeln(date('H:i:s') . ' - 找到 ' . count($expiredOrders) . ' 个到期订单');

        $settledCount = 0;
        $now = date('Y-m-d H:i:s');

        foreach ($expiredOrders as $order) {
            try {
                // 获取当前价格（从币安API）
                $exitPrice = $this->getCurrentPrice($order['trading_symbol']);

                if (!$exitPrice) {
                    $output->writeln("无法获取 {$order['trading_symbol']} 价格，跳过订单 {$order['id']}");
                    continue;
                }

                // 判断输赢
                $result = 'draw';
                $profitLoss = 0;

                if ($order['trade_type'] == 'up') {
                    // 买涨：平仓价 > 开仓价 则赢
                    if ($exitPrice > $order['entry_price']) {
                        $result = 'win';
                        $profitLoss = $order['amount'] * ($order['profit_rate'] / 100);
                    } elseif ($exitPrice < $order['entry_price']) {
                        $result = 'lose';
                        $profitLoss = -$order['amount'];
                    }
                } else {
                    // 买跌：平仓价 < 开仓价 则赢
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
                        ->whereIn('status', [0, 1])
                        ->update([
                            'exit_price' => $exitPrice,
                            'profit_loss' => $profitLoss,
                            'result' => $result,
                            'status' => 2, // 已结算
                            'settled_at' => $now
                        ]);

                    // 如果没有更新任何行，说明订单已被其他进程处理
                    if ($updateResult === 0) {
                        $output->writeln("订单 {$order['id']} 已被其他进程结算，跳过");
                        Db::rollback();
                        continue;
                    }

                    // 判断是否为真实交易
                    $isRealTrade = ($order['money_type'] == 1);
                    $balanceField = $isRealTrade ? 'money' : 'virtualmoney';

                    // 获取下单用户信息
                    $user = Db::name('user')->where('id', $order['user_id'])->find();

                    // 根据结果处理资金
                    if ($result == 'win') {
                        // 获胜返还本金 + 收益
                        $returnAmount = $order['amount'] + $profitLoss;
                        Db::name('user')->where('id', $order['user_id'])->inc($balanceField, $returnAmount)->update();

                        // 记录资金流水（只记录真实交易）
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
                        // 平局返还本金
                        $returnAmount = $order['amount'];
                        Db::name('user')->where('id', $order['user_id'])->inc($balanceField, $returnAmount)->update();

                        // 记录资金流水（只记录真实交易）
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
                        $this->processInviteRebate($order, $user, $output);
                    }
                    // lose 的情况不需要处理用户资金，本金已在下单时扣除

                    Db::commit();
                    $settledCount++;
                    $output->writeln("订单 {$order['id']} 结算成功: {$result}, 盈亏: {$profitLoss}");

                } catch (\Exception $e) {
                    Db::rollback();
                    $output->writeln("订单 {$order['id']} 结算失败: " . $e->getMessage());
                }

            } catch (\Exception $e) {
                $output->writeln("处理订单 {$order['id']} 出错: " . $e->getMessage());
            }
        }

        if ($settledCount > 0) {
            $output->writeln("结算完成，共结算 {$settledCount} 个订单");
        }
    }

    /**
     * 从币安合约API获取当前价格
     */
    private function getCurrentPrice($symbol)
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
     *
     * @param array $order 订单信息
     * @param array $user 下单用户信息
     * @param Output $output 输出对象
     */
    private function processInviteRebate($order, $user, $output)
    {
        // 检查用户是否有邀请人（reference字段存储邀请人ID）
        if (empty($user['reference'])) {
            return;
        }

        $inviterId = $user['reference'];

        // 获取邀请人信息
        $inviter = Db::name('user')->where('id', $inviterId)->find();
        if (!$inviter) {
            $output->writeln("邀请人 {$inviterId} 不存在，跳过返利");
            return;
        }

        // 计算返利金额：亏损金额的10%
        // 亏损金额就是订单金额（因为亏损时用户失去全部本金）
        $lossAmount = $order['amount'];
        $rebateRate = 0.10; // 10%返利比例
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

        $output->writeln("邀请返利成功: 邀请人 {$inviterId} 获得返利 $" . number_format($rebateAmount, 2) . " (来自 {$inviteeEmail})");
    }
}
