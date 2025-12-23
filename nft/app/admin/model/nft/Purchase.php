<?php

namespace app\admin\model\nft;

use think\Model;

/**
 * Purchase
 */
class Purchase extends Model
{
    // 表名
    protected $name = 'nft_purchase';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;

    protected $type = [
        'expire_time' => 'timestamp:Y-m-d H:i:s',
    ];
    public function referenceTable(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\User::class, 'reference', 'id');
    }

    public function user(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\User::class, 'user_id', 'id');
    }

    public function product(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\nft\Product::class, 'product_id', 'id');
    }
}