<?php

namespace app\admin\controller\user;

use Throwable;
use app\admin\model\User;
use app\admin\model\UserScoreLog;
use app\common\controller\Backend;

class ScoreLog extends Backend
{
    /**
     * @var object
     * @phpstan-var UserScoreLog
     */
    protected object $model;

    protected array $withJoinTable = ['user'];

    // 排除字段
    protected string|array $preExcludeFields = ['create_time'];

    protected string|array $quickSearchField = ['user.username', 'user.nickname'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new UserScoreLog();
    }

    public function index(): void
    {
        // 如果是 select 则转发到 select 方法，若未重写该方法，其实还是继续执行 index
        if ($this->request->param('select')) {
            $this->select();
        }

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
     * 添加
     * @param int $userId
     * @throws Throwable
     */
    public function add(int $userId = 0): void
    {
        if ($this->request->isPost()) {
            parent::add();
        }

        $user = User::where('id', $userId)->find();
        if (!$user) {
            $this->error(__("The user can't find it"));
        }
        $this->success('', [
            'user' => $user
        ]);
    }
}