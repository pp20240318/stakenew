<?php

namespace app\admin\controller\user;

use Throwable;
use app\admin\model\User;
use app\admin\model\UserMoneyLog;
use app\common\controller\Backend;

class MoneyLog extends Backend
{
    /**
     * @var object
     * @phpstan-var UserMoneyLog
     */
    protected object $model;

    protected array $withJoinTable = ['user'];

    // 排除字段
    protected string|array $preExcludeFields = ['create_time'];

    protected string|array $quickSearchField = ['user.username', 'user.nickname'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new UserMoneyLog();
    }

    /**
     * 添加
     * @param int $userId
     * @throws Throwable
     */
    public function add(int $userId = 0): void
    {
        if ($this->request->isPost()) { 
            $data = $this->request->post();
            // $business_typeid = $data['business_typeid'] ; 
            //$business_typeid == 3;
            $user = User::where('id', $data['user_id'])->find();
            $buymoney = $data['money'];
            //$data['business_typeid'] = 3;
            //$data['type'] = 2;
            $data['business_type']= $data['business_type'];
            $data['money'] =  $buymoney;
            $data['memo'] = $data['memo'];
            $data['before'] = $user['money'];
            $data['after'] = $user['money']+$buymoney;
            $data['create_time'] = time(); //-rand(60,60*3)
            $result = $this->model->insert($data); 
            User::where('id', $data['user_id'])->update(["money"=>$data['after']]);

            //$user = User::where('id', $data['user_id'])->find();
            // $percent = rand(10, 20) /100;
            // $data['business_typeid'] = 4;
            // $data['business_type']= "SELL ".$data['currency'];
            // $data['money'] = $buymoney*(1+$percent);
            // $data['before'] = $user['money'];
            // $data['after'] = $user['money']+$data['money'];
            // $data['create_time'] = time();  
            // $data['profit_rate'] =  $percent*100;
            // $data['profit'] =  $percent*$buymoney;
            // $result = $this->model->insert($data); 
            // User::where('id', $data['user_id'])->update(["money"=>$data['after']]);
 
        }
        else {
            $user = User::where('id', $userId)->find();
            if (!$user) {
                $this->error(__("The user can't find it"));
            }
        }
       
        $this->success('', [
            'user' => $user
        ]);
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
        $admInfo=$this->auth->getInfo();
        list($where, $alias, $limit, $order) = $this->queryBuilder();


        //if ($admInfo['id'] == 1) {
            // 如果admin_id为1，查询user表所有记录

            $res = $this->model
                ->withJoin($this->withJoinTable, $this->withJoinType)
                ->alias($alias)
                ->where($where)
                ->order($order)
                ->paginate($limit);

        // } else {

        //     $res = $this->model
        //         ->withJoin($this->withJoinTable, $this->withJoinType)
        //         ->alias($alias)
        //         ->where($where)
        //         ->where('user.agent', '<>', '')
        //         ->where('user.agent', '=', $admInfo['id'])
        //         ->order($order)
        //         ->paginate($limit);

        // }

//        $res = $this->model
//            ->field($this->indexField)
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
}