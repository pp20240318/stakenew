<?php

namespace app\admin\model\finance;

use think\Model;

/**
 * Withdrawl
 */
class Withdrawl extends Model
{
    // 表名
    protected $name = 'finance_withdrawl';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;


    public function getTargetNumAttr($value): float
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