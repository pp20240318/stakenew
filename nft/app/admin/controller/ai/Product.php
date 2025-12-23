<?php

namespace app\admin\controller\ai;

use app\common\controller\Backend;

/**
 * 机器人产品列管理
 */
class Product extends Backend
{
    /**
     * Product模型对象
     * @var object
     * @phpstan-var \app\admin\model\ai\Product
     */
    protected object $model;

    protected string|array $defaultSortField = 'sort,desc';

    protected array|string $preExcludeFields = ['id', 'update_time'];

    protected string|array $quickSearchField = ['id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\ai\Product();
    }


    public function index(): void
    {
        if ($this->request->param('select')) {
            $this->select();
        }

        list($where, $alias, $limit, $order) = $this->queryBuilder();
        if ($this->request->param('purpose')) {
            $where =['product.id'=> ['=',$this->request->param('product_id')]];
        }
        $res = $this->model
            ->field($this->indexField)
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where($where)
            ->order($order)
            ->paginate($limit);

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