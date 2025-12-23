<?php

namespace app\admin\controller;

use app\admin\model\finance\Recharge;
use Throwable;
use app\common\controller\Backend;
use think\facade\Db;

/**
 * 统计报管理
 */
class Statistic extends Backend
{
    /**
     * Statistic模型对象
     * @var object
     * @phpstan-var \app\admin\model\Statistic
     */
    protected object $model;
    protected object $rechargeModel;
    protected object $withdrawlModel;

    protected array|string $preExcludeFields = ['id', 'recharge_num'];

    protected array $withJoinTable = ['admin'];

    protected string|array $quickSearchField = ['id'];

    protected array $rechargewithJoinTable = ['user', 'sourceCurrencyTable', 'targetCurrencyTable','admin'];
    protected array $withdrawlwithJoinTable = ['user','targetCurrencyTable','admin'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\Statistic();
        $this->rechargeModel = new \app\admin\model\finance\Recharge();
        $this->withdrawlModel = new \app\admin\model\finance\Withdrawl();
    }

    /**
     * 查看
     * @throws Throwable
     */
    public function index(): void
    {
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
//        $res->visible(['admin' => ['username']]);
//
//        $this->success('', [
//            'list'   => $res->items(),
//            'total'  => $res->total(),
//            'remark' => get_route_remark(),
//        ]);
        $searchTime = $this->request->param('searchTime');
        $searchAgent = $this->request->param('searchAgent');                //param传参

        $totalAdmin = Db::name('admin')
            ->where('salesman',1)
            ->count();
        // 统计业务员充值总额
        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $rechargeTotal = $this->rechargeModel
            ->withJoin(['admin' => function ($query) {
                $query->field(' SUM(target_num) as recharge_total')
                    ->group('salesman');
            }], $this->withJoinType)
            ->whereNotNull('recharge.salesman')
            ->where(function($query){
                $searchTime = $this->request->param('searchTime');
                $searchAgent = $this->request->param('searchAgent');
                if($searchAgent!= null && $searchAgent!= "") {
                    $query->where('recharge.salesman', $searchAgent);
                }
                if($searchTime!= null && $searchTime!= "" && $searchTime!= []) {
                    $query->where('recharge.update_time', '>',strtotime($searchTime[0]));
                    $query->where('recharge.update_time', '<',strtotime($searchTime[1]));
                }
            })
            ->where('recharge.salesman','<>','')
            ->where('recharge.status',1)
            ->select();

        // 统计业务员提现总额
        $withdrawalTotal =  $this->withdrawlModel
            ->withJoin(['admin' => function ($query) {
                $query->field(' SUM(usdtold_num) as withdrawal_total')
                    ->group('salesman');
            }], $this->withJoinType)
            ->whereNotNull('withdrawl.salesman')
            ->where(function($query){
                $searchTime = $this->request->param('searchTime');
                $searchAgent = $this->request->param('searchAgent');
                if($searchAgent!= null && $searchAgent!= "") {
                    $query->where('withdrawl.salesman', $searchAgent);
                }
                if($searchTime!= null && $searchTime!= "" && $searchTime!= []) {
                    $query->where('withdrawl.update_time', '>',strtotime($searchTime[0]));
                    $query->where('withdrawl.update_time', '<',strtotime($searchTime[1]));
                }
            })
            ->where('withdrawl.salesman','<>','')
            ->where('withdrawl.status',1)
            ->select();

        $salesmanAll =[];
        // 遍历 rechargeTotal 数组
        // 创建一个关联数组，以 salesman 为键，存储 recharge_total 的值
        $rechargeMap = [];
        foreach ($rechargeTotal as $rechargeItem) {
            $rechargeMap[$rechargeItem['salesman']] = $rechargeItem;
        }

        // 创建一个关联数组，以 salesman 为键，存储 withdrawal_total 的值
        $withdrawalMap = [];
        foreach ($withdrawalTotal as $withdrawalItem) {
            $withdrawalMap[$withdrawalItem['salesman']] = $withdrawalItem;
        }

        $salesmen = array_unique(array_merge(array_keys($rechargeMap), array_keys($withdrawalMap)));

        $total = [];
        foreach ($salesmen as $salesman) {
            $recharge_amount = $rechargeMap[$salesman]?? null;
            $withdrawal_amount = $withdrawalMap[$salesman]?? null;

            $mergedItem = [
                "salesman" => $salesman,
                "recharge_total" => $recharge_amount,
                "withdrawal_total" => $withdrawal_amount
            ];
            $salesmanAll[] = $mergedItem;
        }




        $this->success('', [
            'list'   => $salesmanAll,
            'total'  => count($salesmanAll),
            'remark' => get_route_remark(),
        ]);
    }

    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}