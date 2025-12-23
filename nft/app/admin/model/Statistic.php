<?php

namespace app\admin\model;

use think\Model;

/**
 * Statistic
 */
class Statistic extends Model
{
    // 表名
    protected $name = 'statistic';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;


    public function admin(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\Admin::class, 'admin_id', 'id')->where('salesman',1);
    }
}