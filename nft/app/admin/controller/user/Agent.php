<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;

/**
 * 会员代理列表管理
 */
class Agent extends Backend
{
    /**
     * Agent模型对象
     * @var object
     * @phpstan-var \app\admin\model\user\Agent
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id', 'last_login_ip', 'create_time'];

    protected string|array $quickSearchField = ['id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\user\Agent();
    }


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}