<?php

namespace app\admin\model\content;

use think\Model;

/**
 * Alter
 */
class Alter extends Model
{
    // 表名
    protected $name = 'content_alter';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;

    // 追加属性
    protected $append = [
        'user',
    ];

    // 字段类型转换
    protected $type = [
        'start_time' => 'timestamp:Y-m-d H:i:s',
        'end_time'   => 'timestamp:Y-m-d H:i:s',
    ];


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

    public function languageTable(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\Basicset::class, 'language', 'code');
    }
}