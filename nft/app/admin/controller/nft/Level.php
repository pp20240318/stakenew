<?php

namespace app\admin\controller\nft;

use app\common\controller\Backend;

/**
 * NFT等级管理
 */
class Level extends Backend
{
    /**
     * Level模型对象
     * @var object
     * @phpstan-var \app\admin\model\nft\Level
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id', 'create_time', 'update_time'];

    protected string|array $quickSearchField = ['name'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\nft\Level();
    }


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}