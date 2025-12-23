<?php

namespace app\admin\controller\user;

use think\facade\Db;
use Throwable;
use app\common\controller\Backend;

/**
 * 会员实名申请管理
 */
class Verify extends Backend
{
    /**
     * Verify模型对象
     * @var object
     * @phpstan-var \app\admin\model\user\Verify
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id', 'create_time', 'update_time'];

    protected array $withJoinTable = ['user'];

    protected string|array $quickSearchField = ['id','user.username'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\user\Verify();
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
     * 普通认证审核
     * @throws Throwable
     */
    public function examine2(): void
    {
        $status = $this->request->post('status');   //data传参
        $row = $this->request->post('row');   //data传参

        list($where, $alias, $limit, $order) = $this->queryBuilder();

        $recharge = $this->model
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where('verify.id','=',$row['id'])
            ->order($order)
            ->find();

        $recharge->status = $status;
        if($status == 1)   //审核通过
        {
            $result =  false;
            $result1 = false;
            Db::startTrans();
            try {
                $result =  Db::name('user_verify')->where('id','=',$row['id'])->update(['status2'=>$status]);

                if($result){
                    $currentTrueName = Db::name('user')->where('id', '=', $row['user_id'])->value('true_name');
                    $status = Db::name('user_verify')->where('id', '=', $row['id'])->value('status');
                    $result1 = Db::name('user')->where('id', '=', $row['user_id'])->update(['true_name' => 1]);

                    if ($currentTrueName!== 1  &&$status==1) {
                        if (!$result1) {
                            $this->error(__('No rows were Examined'));
                        }
                    }
                }
                else if (!$result) {
                    $this->error(__('No rows were Examined'));
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
                $result =  Db::name('user_verify')->where('id','=',$row['id'])->update(['status2'=>$status]);

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
     * 高级认证审核
     * @throws Throwable
     */
    public function examine(): void
    {
        $status = $this->request->post('status');   //data传参
        $row = $this->request->post('row');   //data传参

        list($where, $alias, $limit, $order) = $this->queryBuilder();

        $recharge = $this->model
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where('verify.id','=',$row['id'])
            ->order($order)
            ->find();

        $recharge->status = $status;
        if($status == 1)   //审核通过
        {
            $result =  false;
            $result1 = false;
            Db::startTrans();
            try {
                    $result =  Db::name('user_verify')->where('id','=',$row['id'])->update(['status'=>$status]);

                    if($result){
                        $currentTrueName = Db::name('user')->where('id', '=', $row['user_id'])->value('true_name');
                        $status2 = Db::name('user_verify')->where('id', '=', $row['id'])->value('status2');

                        if ($currentTrueName!== 1 && $status2==1) {
                            $result1 = Db::name('user')->where('id', '=', $row['user_id'])->update(['true_name' => 1]);

                            if (!$result1) {
                                $this->error(__('No rows were Examined'));
                            }
                        }
                    }
                    else if (!$result) {
                        $this->error(__('No rows were Examined'));
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
                $result =  Db::name('user_verify')->where('id','=',$row['id'])->update(['status'=>$status]);

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