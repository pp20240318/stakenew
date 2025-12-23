<?php

namespace app\admin\model\finance;

use think\Model;

/**
 * Recharge
 */
class Recharge extends Model
{
    // 表名
    protected $name = 'finance_recharge';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;


    public function getSourceNumAttr($value): float
    {
        return (float)$value;
    }

    public function getTargetNumAttr($value): float
    {
        return (float)$value;
    }

    public function getIntoAccountAttr($value): float
    {
        return (float)$value;
    }

    public function getExchangeRateAttr($value): float
    {
        return (float)$value;
    }

    public function user(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\User::class, 'user_id', 'id');
    }

   

    public function admin(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\Admin::class, 'salesman', 'id');
    }

}