<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 基础数据管理
 */
class Basicset extends Backend
{
    /**
     * Basicset模型对象
     * @var object
     * @phpstan-var \app\admin\model\Basicset
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id', 'create_time', 'update_time'];

    protected string|array $quickSearchField = ['id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\Basicset();
    }

    /**
     * 查看
     * @throws Throwable
     */
    public function index(): void
    {
        if ($this->request->param('select')) {
            $this->select();
        }



        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $res = $this->model
            ->field($this->indexField)
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where($where)
            ->where(function($query){
                if($this->request->param('type')!=null) {
                    $query->where('typelist','=', $this->request->param('type'));
                }
                // 在闭包中构建带括号的复杂条件
            })
            ->order($order)
            ->paginate($limit);

//        halt($res->toArray());
        $this->success('', [
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => get_route_remark(),
        ]);
    }
    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}