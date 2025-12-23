<?php

namespace app\admin\controller\nft;


use think\facade\Db;
use Throwable;
use app\common\controller\Backend;

/**
 * NFT购买记录管理
 */
class Purchase extends Backend
{
    /**
     * Purchase模型对象
     * @var object
     * @phpstan-var \app\admin\model\nft\Purchase
     */
    protected object $model,$productModel;

    protected array|string $preExcludeFields = ['id', 'create_time'];

    protected array $withJoinTable = ['referenceTable', 'user', 'product'];

    protected string|array $quickSearchField = ['id'];

    protected array $noNeedLogin = ['checkExpire'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\nft\Purchase();
        $this->productModel = new \app\admin\model\nft\Product();
    }

    /**
     * 查看
     * @throws Throwable
     */
    public function index(): void
    {
        // 如果是 select 则转发到 select 方法，若未重写该方法，其实还是继续执行 index
        if ($this->request->param('select')) {
            $this->select();
        }

        /**
         * 1. withJoin 不可使用 alias 方法设置表别名，别名将自动使用关联模型名称（小写下划线命名规则）
         * 2. 以下的别名设置了主表别名，同时便于拼接查询参数等
         * 3. paginate 数据集可使用链式操作 each(function($item, $key) {}) 遍历处理
         */
        $admInfo=$this->auth->getInfo();
        list($where, $alias, $limit, $order) = $this->queryBuilder();
        if ($admInfo['id'] == 1) {
            // 如果admin_id为1，查询user表所有记录

            $res = $this->model
                ->withJoin($this->withJoinTable, $this->withJoinType)
                ->alias($alias)
                ->where($where)
                ->order($order)
                ->paginate($limit);

        } else {

            $res = $this->model
                ->withJoin($this->withJoinTable, $this->withJoinType)
                ->alias($alias)
                ->where($where)
                ->where('user.agent', '<>', '')
                ->where('user.agent', '=', $admInfo['id'])
                ->order($order)
                ->paginate($limit);

        }

        $this->success('', [
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => get_route_remark(),
        ]);
    }
    /**
     * 添加
     */
    public function add(): void
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $days = $data['valid_day'];
            $now = time();
            $data['expire_time'] =  $now + $days * 86400;
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $data = $this->excludeFields($data);
            if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                $data[$this->dataLimitField] = $this->auth->id;
            }

            $result = false;
            $this->model->startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate();
                        if ($this->modelSceneValidate) $validate->scene('add');
                        $validate->check($data);
                    }
                }
                $result = $this->model->save($data);
                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Added successfully'));
            } else {
                $this->error(__('No rows were added'));
            }
        }

        $this->error(__('Parameter error'));
    }

    /**
     * 编辑
     * @throws Throwable
     */
    public function edit(): void
    {
        $pk  = $this->model->getPk();
        $id  = $this->request->param($pk);
        $row = $this->model->find($id);
        if (!$row) {
            $this->error(__('Record not found'));
        }

        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds && !in_array($row[$this->dataLimitField], $dataLimitAdminIds)) {
            $this->error(__('You have no permission'));
        }

        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }
            $days = $data['valid_day'];
            $data['expire_time'] =  $data['create_time'] + $days * 86400;

            $data   = $this->excludeFields($data);
            $result = false;
            $this->model->startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate();
                        if ($this->modelSceneValidate) $validate->scene('edit');
                        $data[$pk] = $row[$pk];
                        $validate->check($data);
                    }
                }

                $result = $row->save($data);
                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {

                $this->success(__('Update successful'));
            } else {
                $this->error(__('No rows updated'));
            }
        }

        $this->success('', [
            'row' => $row
        ]);
    }

    /**
     * 查看
     * @throws Throwable
     */
    public function checkExpire(): void
    {
        if($this->auth->isLogin())      //登录执行
        {
            $product_id = Db::name('nft_purchase')
            ->where('expire_time','<',time())
            ->where('status',1)
            ->column('distinct product_id');


            Db::name('nft_product')->where('id','in',$product_id)->update(['is_sale'=>0,'status'=>0]);

            $this->success('', [
                'result'   => 0
            ]);
        }
    }


    public function computeEaring(): void
    {

        list($where, $alias, $limit, $order) = $this->queryBuilder();

        $res = $this->productModel
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where($where)
            ->where(['status'=> ['=','opt0']])
            ->order($order)
            ->paginate($limit);

        halt($res);

        $result = Db::name('nft_purchase')->where(
            'expire_time','<',time()
        )->update(['status'=>0]);

        $result1 = Db::name('ai_pledge')->where(
            'expire_time','<',time()
        )->update(['status'=>0]);

        $this->success('', [
            'result'   => $result,
            'result1'   => $result1,
        ]);
    }
    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}