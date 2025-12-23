<?php

namespace app\admin\controller;


use think\facade\Db;
use Throwable;
use app\common\controller\Backend;

/**
 * 贷款信息管理
 */
class Loans extends Backend
{
    /**
     * Loans模型对象
     * @var object
     * @phpstan-var \app\admin\model\Loans
     */
    protected object $model;

    protected string|array $defaultSortField = 'loan_id,desc';

    protected array $withJoinTable = ['user'];

    protected array|string $preExcludeFields = ['create_time', 'update_time'];

    protected string|array $quickSearchField = ['loan_id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\Loans();
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
//        $res = $this->model
//            ->withJoin($this->withJoinTable, $this->withJoinType)
//            ->alias($alias)
//            ->where($where)
//            ->order($order)
//            ->paginate($limit);


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
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }
            $borrower = Db::name('user')->find($data['user_id']);
            $data['borrower_name'] = $borrower['username'];

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
            $borrower = Db::name('user')->find($data['user_id']);
            $data['borrower_name'] = $borrower['username'];
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
     * 审核
     * @throws Throwable
     */
    public function examine(): void
    {
        $status = $this->request->post('status');   //data传参
        $row = $this->request->post('row');   //data传参

        list($where, $alias, $limit, $order) = $this->queryBuilder();

        $loans = $this->model
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where('loan_id','=',$row['loan_id'])
            ->order($order)
            ->find();

        $loans->status = $status;
        if($status == 1)   //审核通过、加上款项
        {

            $money = $loans['user']['money'] + $loans['loan_amount'];

            $result1 = false;
            $result =  false;
            Db::startTrans();
            try {
                $result1 =  Db::name('user')->where('id','=',$loans['user']['id'])->update(['money'=>$money]);
                if($result1){
                    $result =  Db::name('loans')->where('loan_id','=',$row['loan_id'])->update(['status'=>$status]);

                    if (!$result) {
                        $this->error(__('No rows were Examined'));
                    }
                }
                else{
                    $this->error(__('Money is not enough'));
                }
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                $this->error(__('Error and Rollback').$e->getMessage());
            }
            if ($result !== false) {

                $this->success(__('Examined successfully'));
            } else {
                $this->error(__('No rows were Examined'));
            }

        }
        else{  //审核不通过
            $result =  false;
            Db::startTrans();
            try {
                $result =  Db::name('loans')->where('loan_id','=',$row['loan_id'])->update(['status'=>$status]);

                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {

                $this->success(__('Examined successfully'));
            } else {
                $this->error(__('No rows were Examined'));
            }
        }
    }
    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}