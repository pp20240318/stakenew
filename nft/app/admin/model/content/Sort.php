<?php

namespace app\admin\model\content;

use think\Model;

/**
 * Sort
 */
class Sort extends Model
{
    // 表名
    protected $name = 'content_sort';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;

    protected static function onAfterInsert($model): void
    {
        if ($model->weigh == 0) {
            $pk = $model->getPk();
            if (strlen($model[$pk]) >= 19) {
                $model->where($pk, $model[$pk])->update(['weigh' => $model->count()]);
            } else {
                $model->where($pk, $model[$pk])->update(['weigh' => $model[$pk]]);
            }
        }
    }

    public function pidTable(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\content\Sort::class, 'pid', 'id');
    }
}