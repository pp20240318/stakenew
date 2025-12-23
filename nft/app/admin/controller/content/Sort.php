<?php

namespace app\admin\controller\content;

use Throwable;
use app\common\controller\Backend;
use ba\Tree;
/**
 * 分类管理
 */
class Sort extends Backend
{
    /**
     * @var ?Tree
     */
    protected ?Tree $tree;
    /**
     * Sort模型对象
     * @var object
     * @phpstan-var \app\admin\model\content\Sort
     */
    protected object $model;

    protected string|array $defaultSortField = 'weigh,desc';

    protected array|string $preExcludeFields = ['id', 'create_time'];

    protected array $withJoinTable = ['pidTable'];

    protected string|array $quickSearchField = ['id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->tree  = Tree::instance();
        $this->model = new \app\admin\model\content\Sort();
    }

//    /**
//     * 查看
//     * @throws Throwable
//     */
//    public function index(): void
//    {
//        // 如果是 select 则转发到 select 方法，若未重写该方法，其实还是继续执行 index
//        if ($this->request->param('select')) {
//            $this->select();
//        }
//
//        /**
//         * 1. withJoin 不可使用 alias 方法设置表别名，别名将自动使用关联模型名称（小写下划线命名规则）
//         * 2. 以下的别名设置了主表别名，同时便于拼接查询参数等
//         * 3. paginate 数据集可使用链式操作 each(function($item, $key) {}) 遍历处理
//         */
//        list($where, $alias, $limit, $order) = $this->queryBuilder();
//        $res = $this->model
//            ->withJoin($this->withJoinTable, $this->withJoinType)
//            ->alias($alias)
//            ->where($where)
//            ->order($order)
//            ->paginate($limit);
//        $res->visible(['pidTable' => ['name']]);
//
//        $this->success('', [
//            'list'   => $res->items(),
//            'total'  => $res->total(),
//            'remark' => get_route_remark(),
//        ]);
//    }

    public function index(): void
    {
        $this->request->filter(['strip_tags', 'trim']);
        list($where, $alias) = $this->queryBuilder();
        $res = $this->model
            ->alias($alias)
            ->where($where)
            ->select()
            ->toArray();
        /* 数据库中初始res数据 */
        /**
         * 树状表格必看注释一
         * 1. 获取表格数据（没有分页，所以简化了以上的数据查询代码）
         * 2. 递归的根据指定字段组装 children 数组，此时直接给前端，表格就可以正常的渲染为树状了，一个方法搞定
         */
        $res = $this->tree->assembleChild($res);

        if ($this->request->param('select')) {
            /**
             * 树状表格必看注释二
             * 1. 在远程 select 中，数据要成树状显示，需要对数据做一些改动
             * 2. 通过已组装好 children 的数据，建立`树枝`结构，并最终合并为一个二维数组方便渲染
             * 3. 简单讲就是把组装好 children 的数据，给以下这两个方法即可
             */
            $res = $this->tree->assembleTree($this->tree->getTreeArray($res));

        }
        $this->success('', [
            'list'   => $res,
            'remark' => get_route_remark(),
        ]);
    }

            /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}