<?php

namespace app\admin\model\ai;

use think\Model;

/**
 * Product
 */
class Product extends Model
{
    // 表名
    protected $name = 'ai_product';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $createTime = false;

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

    public function getBeginPriceAttr($value): float
    {
        return (float)$value;
    }

    public function getEndPriceAttr($value): float
    {
        return (float)$value;
    }

    public function getEarningRateAttr($value): float
    {
        return (float)$value;
    }
}