<?php

namespace app\admin\model;

use think\Model;

/**
 * Loans
 */
class Loans extends Model
{
    // 表主键
    protected $pk = 'loan_id';

    // 表名
    protected $name = 'loans';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
// 字段类型转换
    protected $type = [
        'start_date'     => 'timestamp:Y-m-d H:i:s',
        'end_date'   => 'timestamp:Y-m-d H:i:s',
    ];

    public function user(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\User::class, 'user_id', 'id');
    }
}