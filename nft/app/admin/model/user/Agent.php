<?php

namespace app\admin\model\user;

use think\Model;

/**
 * Agent
 */
class Agent extends Model
{
    // 表名
    protected $name = 'user_agent';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;


    public function getMoneyAttr($value): float
    {
        return (float)$value;
    }
}