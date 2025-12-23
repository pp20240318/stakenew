<?php

namespace app\admin\model\nft;

use think\Model;

/**
 * Product
 */
class Product extends Model
{
    // 表名
    protected $name = 'nft_product';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $createTime = false;

    // 字段类型转换
    protected $type = [
        'begin_time' => 'timestamp:Y-m-d H:i:s',
        'end_time'   => 'timestamp:Y-m-d H:i:s',
    ];

    protected static function onAfterInsert($model): void
    {
        if ($model->sort == 0) {
            $pk = $model->getPk();
            if (strlen($model[$pk]) >= 19) {
                $model->where($pk, $model[$pk])->update(['sort' => $model->count()]);
            } else {
                $model->where($pk, $model[$pk])->update(['sort' => $model[$pk]]);
            }
        }
    }

    public function getPriceAttr($value): float
    {
        return (float)$value;
    }

    public function getEarningRateAttr($value): float
    {
        return (float)$value;
    }

    public function getDescriptionAttr($value): string
    {
        return !$value ? '' : htmlspecialchars_decode($value);
    }

    public function level(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\nft\Level::class, 'level_id', 'id');
    }
}