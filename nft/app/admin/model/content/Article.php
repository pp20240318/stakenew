<?php

namespace app\admin\model\content;

use think\Model;

/**
 * Article
 */
class Article extends Model
{
    // 表名
    protected $name = 'content_article';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;
    // 追加属性
    protected $append = [
        'user',
    ];
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

    public function sort(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\content\Sort::class, 'sort_id', 'id');
    }

    public function languageTable(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\Basicset::class, 'language', 'code');
    }


    public function getUserAttr($value, $row): array
    {
        return [
            'username' => \app\admin\model\User::whereIn('id', $row['user_ids'])->column('username'),
        ];
    }

    public function getUserIdsAttr($value): array
    {
        if ($value === '' || $value === null) return [];
        if (!is_array($value)) {
            $value = explode(',', $value);
            $result = array_filter($value);
            return  array_values($result);

        }
        return $value;
    }

    public function setUserIdsAttr($value): string
    {
        return is_array($value) ? ','.implode(',', $value).',' : $value;
    }
}