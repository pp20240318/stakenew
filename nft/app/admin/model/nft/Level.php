<?php

namespace app\admin\model\nft;

use think\Model;

/**
 * Level
 */
class Level extends Model
{
    // 表名
    protected $name = 'nft_level';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;


    public function getBeginPriceAttr($value): float
    {
        return (float)$value;
    }

    public function getEndPriceAttr($value): float
    {
        return (float)$value;
    }
}