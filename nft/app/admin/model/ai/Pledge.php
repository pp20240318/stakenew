<?php

namespace app\admin\model\ai;

use think\Model;

/**
 * Pledge
 */
class Pledge extends Model
{
    // 表名
    protected $name = 'ai_pledge';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;

    // 字段类型转换
    protected $type = [
        'create_time' => 'timestamp:Y-m-d H:i:s',
        'confirm_time' => 'timestamp:Y-m-d H:i:s',
        'expire_time' => 'timestamp:Y-m-d H:i:s',
    ];


    public function getPriceAttr($value): float
    {
        return (float)$value;
    }

    public function referenceTable(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\User::class, 'reference', 'id');
    }
    public function user(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\User::class, 'user_id', 'id');
    }
    public function productNameTable(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\ai\Product::class, 'product_id', 'id');
    }
}