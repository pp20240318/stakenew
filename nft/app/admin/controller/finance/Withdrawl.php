<?php

namespace app\admin\controller\finance;

use think\facade\Cache;
use think\facade\Db;
use Throwable;
use app\common\controller\Backend;

/**
 * 提现列表管理
 */
class Withdrawl extends Backend
{
    /**
     * Withdrawl模型对象
     * @var object
     * @phpstan-var \app\admin\model\finance\Withdrawl
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id', 'create_time', 'update_time'];

    protected array $withJoinTable = ['user','admin'];

    protected string|array $quickSearchField = ['id','user.username'];
    protected array $noNeedLogin = ['searchNewRecharge'];
    public function initialize(): void
    {
        parent::initialize();
        Db::instance();
        $this->model = new \app\admin\model\finance\Withdrawl();
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
     * 审核
     * @throws Throwable
     */
    public function examine(): void
    {
        $status = $this->request->post('status');   //data传参
        $row = $this->request->post('row');   //data传参

        list($where, $alias, $limit, $order) = $this->queryBuilder();

        $withdrawl = $this->model
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where('withdrawl.id','=',$row['id'])
            ->order($order)
            ->find();
        $withdrawl->visible(['user' => ['username','money','id']]);
        $withdrawl->status = $status;
        $admininfo = $this->auth->getInfo();
//    if($status == 2)   //审核不通过、退回款项
//    {
//
//            $money = $withdrawl['user']['money'] + $withdrawl['target_num'] * $withdrawl['exchange_rate'];
//
//            $result1 = false;
//            $result =  false;
//            Db::startTrans();
//            try {
//                $result1 =  Db::name('user')->where('id','=',$withdrawl['user']['id'])->update(['money'=>$money]);
//                if($result1){
//                    $result =  Db::name('finance_withdrawl')->where('id','=',$row['id'])->update(['status'=>$status,'salesman'=>$admininfo['id']]);
//
//                    if (!$result) {
//                        $this->error(__('No rows were Examined'));
//                    }
//                }
//                else{
//                    $this->error(__('Money is not enough'));
//                }
//                // 提交事务
//                Db::commit();
//            } catch (\Exception $e) {
//                // 回滚事务
//                Db::rollback();
//                $this->error(__('Error and Rollback').$e->getMessage());
//            }
//            if ($result !== false) {
//
//                $this->success(__('Examined successfully'));
//            } else {
//                $this->error(__('No rows were Examined'));
//            }
//
//    }
        if($status == 1)   //审核通过、扣除款项
        { 
            $money = $withdrawl['user']['money'] + $withdrawl['usdtold_num'];
 
            $result =  false;
            Db::startTrans();
            try {
                //$result1 =  Db::name('user')->where('id','=',$withdrawl['user']['id'])->update(['money'=>$money]);
                 
                    $result =  Db::name('finance_withdrawl')->where('id','=',$row['id'])->update(['status'=>'opt1','salesman'=>$admininfo['id'],'update_time'=>time()]);

                    if (!$result) {
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
                $money = $withdrawl['user']['money'] + $withdrawl['usdtold_num'];
                $result =  Db::name('finance_withdrawl')->where('id','=',$row['id'])->update(['status'=>'opt2','salesman'=>$admininfo['id'],'update_time'=>time()]);
                $result1 =  Db::name('user')->where('id','=',$withdrawl['user']['id'])->update(['money'=>$money]);
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

    public function editBank(): void
    {
        $data = $this->request->post('data');   //data传参
        $row = $this->request->post('row');   //data传参
        $id  = $row['id'];
        $row = $this->model->find($id);
        if (!$row) {
            $this->error(__('Record not found'));
        }

        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds && !in_array($row[$this->dataLimitField], $dataLimitAdminIds)) {
            $this->error(__('You have no permission'));
        }

        if (!$data) {
            $this->error(__('Parameter %s can not be empty', ['']));
        }

        $data   = $this->excludeFields($data);
        $result = false;
        Db::startTrans();
        try {
            $result =  Db::name('finance_withdrawl')->where('id','=',$row['id'])->update(['bank_name'=>$data['bank_name'],'name'=>$data['name'],'bank_account'=>$data['bank_account'],'legal_tender'=>$data['legal_tender'],'branch'=>$data['branch']]);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result !== false) {
            $this->success(__('Update successful'));
        } else {
            $this->error(__('No rows updated'));
        }
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

            $data = $this->excludeFields($data);
            $admininfo = $this->auth->getInfo();
            $data['salesman'] = $admininfo['id'];
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
                Cache::set('newWithdrawl_date',$this->model->create_time);

                $maxId = $this->model::max('id');
                Cache::set('newWithdrawl_id',$maxId);
                $this->success(__('Added successfully'));
            } else {
                $this->error(__('No rows were added'));
            }
        }

        $this->error(__('Parameter error'));
    }

    public function searchNewWithdrawl(): void
    {
        if($this->auth->isLogin())      //登录执行
        {
            $newWithdrawl_date = Cache::get('newWithdrawl_date');
            $newWithdrawl_id = Cache::get('newWithdrawl_id');

            if (!$newWithdrawl_id) {
                $this->success('无新提现', false);
            }

            $now = time();
//       $new_date = strtotime($new_date);

//       halt($new_date);
//       $diff = $newDateTime->diff($now);
            //一分钟内的新数据
            if ($now - $newWithdrawl_date < 3000) {
                $this->success($newWithdrawl_id, true);
            } else {
                $this->success($newWithdrawl_id, false);
            }
        }
    }

    public function count():void
    {
        $admininfo = $this->auth->getInfo();
        $count = $this->model->where('status', 1)->where('salesman',$admininfo['id'])->sum('usdtold_num');
        if($count){
            $this->success('', ['count' => $count]);
        }else{
            $this->success('', ['count' => 0]);
        }
    }
    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}