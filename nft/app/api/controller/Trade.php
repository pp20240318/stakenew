<?php

namespace app\api\controller;

use Throwable;
use think\facade\Db;
use think\facade\Config;
use think\Request;
use app\common\controller\Frontend;
use think\facade\Log;
use think\Response;

class Trade extends Frontend
{
    protected array $noNeedLogin = [];

    public function initialize(): void
    {
        parent::initialize();
        // 记录所有请求头信息，帮助调试
        Log::info('Trade Controller Request Headers: ' . json_encode(request()->header()));
        Log::info('Trade Controller All Params: ' . json_encode(request()->param()));
        
        // 检查 token 是否存在
        $token = request()->header('token');
        Log::info('Trade Controller Token: ' . ($token ?: 'none'));
        
        // 检查 request 对象是否有 userId 属性
        $userId = request()->userId ?? null;
        Log::info('Trade Controller userId from request: ' . ($userId ?: 'none'));
        
        // 检查请求参数是否有 userid
        $userIdParam = request()->param('userid');
        Log::info('Trade Controller userid from param: ' . ($userIdParam ?: 'none'));
    }

    /**
     * Get trade records with pagination
     * @throws Throwable
     */
    public function records(Request $request)
    {
        // 首先尝试从token获取userId
        $userid = $request->userId;
        
        // 如果没有userId，尝试从参数获取
        if (empty($userid)) {
            $userid = $request->param("userid");
            Log::info('Trade records: Using userid from param: ' . ($userid ?: 'none'));
        } else {
            Log::info('Trade records: Using userId from request: ' . $userid);
        }
        
        if (empty($userid)) {
            $this->error(__('Please login first'));
        }
        
        // 获取分页参数
        $page = $request->param("page", 1);
        $limit = $request->param("limit", 10);
        
        // 获取交易类型参数
        $type = $request->param("type", 1); // 默认为手动交易
        
        // 获取时间筛选参数
        $startTime = $request->param("start_time");
        $endTime = $request->param("end_time");
        
        $trades = [];
        $total = 0;
        
        if (!empty($userid)) {
            // 构建查询条件
            $query = Db::name("user_money_log")
                ->where("user_id", $userid) 
                ->where(function ($query) {
                    $query->where("business_type", 'like', 'Buy %')
                        ->whereOr("business_type", 'like', 'Sell %');
                });
            
            // 添加时间筛选条件
            if (!empty($startTime)) {
                $query->where("create_time", ">=", $startTime);
                Log::info('Trade records: Filtering by start_time: ' . $startTime);
            }
            
            if (!empty($endTime)) {
                $query->where("create_time", "<=", $endTime);
                Log::info('Trade records: Filtering by end_time: ' . $endTime);
            }
            
            // 计算总记录数
            $total = $query->count();
            
            // 获取交易记录
            $records = $query
                ->order("create_time", "desc")
                ->limit(($page - 1) * $limit, $limit)
                ->select()
                ->toArray();
            
            // 处理交易记录
            foreach ($records as $record) {
                // 判断是买入还是卖出
                $isBuy = strpos($record['business_type'], 'Buy') === 0;
                
                // 计算盈亏
                $profit = 0;
                if (!$isBuy) {
                    // 查找对应的买入记录
                    $buyRecord = Db::name("user_money_log")
                        ->where("user_id", $userid)
                        ->where("business_type", 'Buy ' . $record['currency'])
                        ->where("type", $type)
                        ->where("create_time", "<", $record['create_time'])
                        ->order("create_time", "desc")
                        ->find();
                    
                    if ($buyRecord) {
                        // 计算盈亏
                        $buyPrice = $buyRecord['price'];
                        $sellPrice = $record['price'];
                        $amount = $record['currency_num'];
                        $profit = ($sellPrice - $buyPrice) * $amount;
                    }
                }
                
                // 构建交易记录
                $trades[] = [
                    'id' => $record['id'],
                    'symbol' => $record['currency'] . '/USDT',
                    'side' => $isBuy ? 'buy' : 'sell',
                    'amount' => $record['currency_num'],
                    'price' => $record['price'],
                    'money' => $record['money'],
                    'time' => $record['create_time'],
                    'create_time' => $record['create_time'],
                    'profit' => $profit,
                    'status' => 'completed'
                ];
            }
        }
        
        $this->success('', [
            'list' => $trades,
            'total' => $total
        ]);
    }
    
    /**
     * Get trade detail by ID
     * @throws Throwable
     */
    public function detail(Request $request)
    {
        // 首先尝试从token获取userId
        $userid = $request->userId;
        
        // 如果没有userId，尝试从参数获取
        if (empty($userid)) {
            $userid = $request->param("userid");
            Log::info('Trade detail: Using userid from param: ' . ($userid ?: 'none'));
        } else {
            Log::info('Trade detail: Using userId from request: ' . $userid);
        }
        
        if (empty($userid)) {
            $this->error(__('Please login first'));
        }
        
        // 获取交易ID
        $id = $request->param("id");
        if (empty($id)) {
            $this->error(__('Invalid trade ID'));
        }
        
        // 获取交易记录
        $record = Db::name("user_money_log")
            ->where("id", $id)
            ->where("user_id", $userid)
            ->find();
        
        if (!$record) {
            $this->error(__('Trade record not found'));
        }
        
        // 判断是买入还是卖出
        $isBuy = strpos($record['business_type'], 'Buy') === 0;
        
        // 计算盈亏
        $profit = 0;
        if (!$isBuy) {
            // 查找对应的买入记录
            $buyRecord = Db::name("user_money_log")
                ->where("user_id", $userid)
                ->where("business_type", 'Buy ' . $record['currency'])
                ->where("type", $record['type'])
                ->where("create_time", "<", $record['create_time'])
                ->order("create_time", "desc")
                ->find();
            
            if ($buyRecord) {
                // 计算盈亏
                $buyPrice = $buyRecord['price'];
                $sellPrice = $record['price'];
                $amount = $record['currency_num'];
                $profit = ($sellPrice - $buyPrice) * $amount;
            }
        }
        
        // 构建交易详情
        $tradeDetail = [
            'id' => $record['id'],
            'symbol' => $record['currency'] . '/USDT',
            'side' => $isBuy ? 'buy' : 'sell',
            'amount' => $record['currency_num'],
            'price' => $record['price'],
            'time' => $record['create_time'],
            'create_time' => $record['create_time'],
            'profit' => $profit,
            'status' => 'completed',
            'memo' => $record['memo']
        ];
        
        $this->success('', $tradeDetail);
    }
} 