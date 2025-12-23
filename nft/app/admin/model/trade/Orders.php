<?php

namespace app\admin\model\trade;

use think\Model;

/**
 * Orders
 */
class Orders extends Model
{
    // 表名
    protected $name = 'trade_orders';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;


    public function user(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\User::class, 'user_id', 'id');
    }

    public function coin(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\Soptcoin::class, 'coin_id', 'id');
    }
}