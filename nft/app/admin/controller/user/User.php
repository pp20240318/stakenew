<?php

namespace app\admin\controller\user;

use think\facade\Db;
use ba\Random;
use Throwable;
use app\common\controller\Backend;
use app\admin\model\User as UserModel; 
use think\facade\Log;


/**
 * 会员管理
 */
class User extends Backend
{
    /**
     * User模型对象
     * @var object
     * @phpstan-var \app\admin\model\User
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id', 'update_time', 'create_time','last_login_time', 'login_failure', 'password', 'salt','password2'];

    protected array $withJoinTable = ['referenceTable','admin'];

    protected string|array $quickSearchField = ['id','username'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\User();
    }

    /**
     * 查看
     * @throws Throwable
     */
    public function index(): void
    {
     
        // 如果是 select 则转发到 select 方法，若未重写该方法，其实还是继续执行 index
        if ($this->request->param('select')) {
            $this->agentindex();    
        } 
        $admInfo = $this->auth->getInfo();
        list($where, $alias, $limit, $order) = $this->queryBuilder();
        //if ($admInfo['id'] == 1) {
            // 如果admin_id为1，查询user表所有记录
            $res = $this->model
                ->withJoin($this->withJoinTable, $this->withJoinType)
                ->alias($alias)
                ->where($where)
                ->order($order)
                ->paginate($limit);
            $res->visible(['referenceTable' => ['id','username','mobile']]);
        // } else {
        //     $res = $this->model
        //         ->withJoin($this->withJoinTable, $this->withJoinType)
        //         ->alias($alias)
        //         ->where('user.agent', '<>', '')
        //         ->where('user.agent', '=', $admInfo['id'])
        //         ->order($order)
        //         ->paginate($limit);
        //     $res->visible(['referenceTable' => ['id','username','mobile']]);
        // }

        /**
         * 1. withJoin 不可使用 alias 方法设置表别名，别名将自动使用关联模型名称（小写下划线命名规则）
         * 2. 以下的别名设置了主表别名，同时便于拼接查询参数等
         * 3. paginate 数据集可使用链式操作 each(function($item, $key) {}) 遍历处理
         */

        // 获取所有用户ID，用于统计
        $items = $res->items();
        $userIds = array_column($items, 'id');
        // print_r("aasasa");
        if (!empty($userIds)) {
            // 获取当前页面所有用户的统计数据
            $this->batchCalculateSubordinateStats($items, $userIds);
        }

        $this->success('', [
            'list'   => $items,
            'total'  => $res->total(),
            'remark' => get_route_remark(),
            
        ]);
    
    }


    /**
     * 查看
     * @throws Throwable
     */
    public function agentindex(): void
    {
     
        $admInfo = $this->auth->getInfo();
        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $res = $this->model 
        ->alias($alias)
        ->where($where)
        //->where('salesman',1)
        ->order($order)
        ->paginate($limit);
        $res->visible(['referenceTable' => ['id','username','mobile']]);

        /**
         * 1. withJoin 不可使用 alias 方法设置表别名，别名将自动使用关联模型名称（小写下划线命名规则）
         * 2. 以下的别名设置了主表别名，同时便于拼接查询参数等
         * 3. paginate 数据集可使用链式操作 each(function($item, $key) {}) 遍历处理
         */

        // 获取所有用户ID，用于统计
        $items = $res->items();
        $userIds = array_column($items, 'id');
         
        $this->success('', [
            'list'   => $items,
            'total'  => $res->total(),
            'remark' => get_route_remark(), 
        ]);
    
    }

   
    public function loginfo($msg){
        Log::write($msg);
    } 
    /**
     * 批量计算下级统计数据（性能优化版本）
     * @param array &$items 用户数据数组（引用传递，直接修改）
     * @param array $userIds 需要计算的用户ID列表
     */
    private function batchCalculateSubordinateStats(array &$items, array $userIds): void
    {
        // 如果没有用户ID，直接返回
        if (empty($userIds)) {
            return;
        }

        // 缓存key
        $cacheKey = 'subordinate_relation_' . md5(implode(',', $userIds));
        $cacheTime = 600; // 缓存10分钟
        
        // 尝试从缓存获取用户关系数据
        $relationMap = cache($cacheKey);
        
        if (!$relationMap) {
            // 缓存不存在，构建关系图
            $relationMap = [];
            
            // 获取所有用户关系
            $allUsers = Db::name('user')
                ->field('id, reference')
                ->where('reference', '>', 0)
                ->select()
                ->toArray();
            
            // 构建用户关系图
            $userRelations = [];
            foreach ($allUsers as $user) {
                if (!isset($userRelations[$user['reference']])) {
                    $userRelations[$user['reference']] = [];
                }
                $userRelations[$user['reference']][] = $user['id'];
            }
            
            // 对每个查询的用户ID，获取其所有下级
            foreach ($userIds as $userId) {
                $relationMap[$userId] = $this->getAllSubordinatesNonRecursive($userId, $userRelations);
            }
            
            // 存入缓存
            cache($cacheKey, $relationMap, $cacheTime);
        }
        
        // 计算今日开始时间戳
        $today = strtotime(date('Y-m-d'));
        
        try {
            // 批量获取所有充值数据
            $rechargeData = $this->batchGetFinanceData('finance_recharge', array_keys($relationMap), $today);
            
            // 批量获取所有提现数据
            $withdrawalData = $this->batchGetFinanceData('finance_withdrawl', array_keys($relationMap), $today);
            
            // 将数据填充到用户记录中
            foreach ($items as $key => $item) {
                $userId = $item['id'];
                
                // 默认值
                $items[$key]['subordinate_total_recharge'] = 0;
                $items[$key]['subordinate_total_withdrawal'] = 0;
                $items[$key]['subordinate_today_recharge'] = 0;
                $items[$key]['subordinate_today_withdrawal'] = 0;
                
                // 如果有下级数据，则更新统计
                if (isset($relationMap[$userId]) && !empty($relationMap[$userId])) {
                    // 充值数据
                    if (isset($rechargeData[$userId])) {
                        $items[$key]['subordinate_total_recharge'] = $rechargeData[$userId]['total'] ?? 0;
                        $items[$key]['subordinate_today_recharge'] = $rechargeData[$userId]['today'] ?? 0;
                    }
                    
                    // 提现数据
                    if (isset($withdrawalData[$userId])) {
                        $items[$key]['subordinate_total_withdrawal'] = $withdrawalData[$userId]['total'] ?? 0;
                        $items[$key]['subordinate_today_withdrawal'] = $withdrawalData[$userId]['today'] ?? 0;
                    }
                }
            }
        } catch (\Exception $e) {
            // 记录错误日志
            \think\facade\Log::error('获取下级统计数据出错: ' . $e->getMessage());
            // 清除可能损坏的缓存
            cache($cacheKey, null);
        }
    }
    
    /**
     * 批量获取财务数据统计
     * @param string $tableName 表名
     * @param array $userIds 用户ID列表
     * @param int $todayTimestamp 今日开始时间戳
     * @return array 财务数据统计
     */
    private function batchGetFinanceData(string $tableName, array $userIds, int $todayTimestamp): array
    {
        $result = [];
        
        if (empty($userIds)) {
            return $result;
        }
        
        // 构建查询条件
        $whereClauses = [];
        
        foreach ($userIds as $userId) {
            if (empty($userId)) continue;
            
            // 为每个用户ID使用正确的缓存键
            $cacheKey = 'subordinate_relation_' . md5(implode(',', $userIds));
            $subordinates = cache($cacheKey)[$userId] ?? [];
            
            if (!empty($subordinates)) {
                // 确保 $subordinates 包含有效的整数ID
                $validSubordinates = array_filter($subordinates, function($id) {
                    return is_numeric($id) && $id > 0;
                });
                
                if (!empty($validSubordinates)) {
                    $whereClauses[] = "WHEN user_id IN (" . implode(',', $validSubordinates) . ") THEN " . intval($userId);
                }
            }
        }
        
        if (empty($whereClauses)) {
            return $result;
        }
        
        try {
            // 获取数据库表前缀
            $prefix = config('database.connections.mysql.prefix');
                        // 针对不同表名使用不同的status条件
            $statusCondition = '';
            if ($tableName == 'finance_recharge') {
                $statusCondition = "status = 2";
            } else if ($tableName == 'finance_withdrawl') {
                $statusCondition = "status = '2'"; // 提现成功状态
            } else {
                $statusCondition = "1=1"; // 默认条件，匹配所有
            }
            
            // 执行一次性查询获取所有统计数据
            $sql = "SELECT 
                        CASE " . implode(' ', $whereClauses) . " END as parent_id, 
                        SUM(target_num) as total_amount,
                        SUM(CASE WHEN create_time >= {$todayTimestamp} THEN target_num ELSE 0 END) as today_amount
                    FROM 
                        {$prefix}{$tableName}
                    WHERE 
                        {$statusCondition}
                    GROUP BY 
                        parent_id
                    HAVING 
                        parent_id IS NOT NULL";
            
            $stats = Db::query($sql);
            
            // 整理结果
            if (!empty($stats) && is_array($stats)) {
                foreach ($stats as $stat) {
                    if (isset($stat['parent_id']) && is_numeric($stat['parent_id'])) {
                        $result[$stat['parent_id']] = [
                            'total' => floatval($stat['total_amount'] ?? 0),
                            'today' => floatval($stat['today_amount'] ?? 0)
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            // 记录错误日志
            $this->loginfo('获取财务数据统计出错 ['.$tableName.']: ' . $e->getMessage());
            \think\facade\Log::error('获取财务数据统计出错 ['.$tableName.']: ' . $e->getMessage());
        }
        
        return $result;
    }
    
    /**
     * 使用非递归方式获取所有下级ID
     * @param int $userId 用户ID
     * @param array $userRelations 用户关系图
     * @return array 所有下级ID
     */
    private function getAllSubordinatesNonRecursive(int $userId, array $userRelations): array
    {
        $result = [];
        $queue = [];
        
        // 初始化队列
        if (isset($userRelations[$userId])) {
            foreach ($userRelations[$userId] as $directSubId) {
                $queue[] = $directSubId;
                $result[] = $directSubId;
            }
        }
        
        // 广度优先搜索
        while (!empty($queue)) {
            $currentId = array_shift($queue);
            
            if (isset($userRelations[$currentId])) {
                foreach ($userRelations[$currentId] as $subId) {
                    if (!in_array($subId, $result)) {
                        $queue[] = $subId;
                        $result[] = $subId;
                    }
                }
            }
        }
        
        return $result;
    }

    /**
     * 获取用户的所有下级ID (原来的递归实现，不再使用)
     * @param int $userId 用户ID
     * @return array 下级用户ID数组
     */
    private function getSubordinateIds(int $userId): array
    {
        // 查找直接下级
        $directSubordinates = Db::name('user')
            ->where('reference', $userId)
            ->column('id');
        
        $allSubordinates = $directSubordinates;
        
        // 递归查找所有下级
        foreach ($directSubordinates as $subId) {
            $subSubordinates = $this->getSubordinateIds($subId);
            if (!empty($subSubordinates)) {
                $allSubordinates = array_merge($allSubordinates, $subSubordinates);
            }
        }
        
        return $allSubordinates;
    }

    /**
     * 添加
     * @throws Throwable
     */
    public function add(): void
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

             //手机号是否被注册
             if(!empty($data['mobile'])){
                $exists = Db::name('user')
                ->where('mobile', $data['mobile'])
                
                ->find();
               if ($exists) {
                   $this->error(__('this mobile is registered'));
               }
             }

            $salt   = Random::build('alnum', 16);
            $passwd = encrypt_password($data['password'], $salt);

            $data   = $this->excludeFields($data);
            $result = false;
            $this->model->startTrans();
            try {
                $data['salt']     = $salt;
                $data['password'] = $passwd;
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

        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            //手机号是否被注册
            if(!empty($data['mobile'])){
                $exists = Db::name('user')
                ->where('mobile', $data['mobile'])
                ->where('id', '<>', $data['id'])
                ->find();
               if ($exists) {
                   $this->error(__('this mobile is registered'));
               }
             }

            $password = $this->request->post('password', '');
            $password2 = $this->request->post('password2', '');

            if ($password) {
                
                $this->model->resetPassword($id, $password);
            }
            if ($password2) {
                
                $this->model->resetPassword2($id, $password2);
            }
            parent::edit();
        }

        unset($row->salt);
        $row->password = '';
        $row->password2 = '';
        $this->success('', [
            'row' => $row
        ]);
    }


    /**
     * 重写select
     * @throws Throwable
     */
    public function select(): void
    {
        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $res = $this->model
            ->withoutField('password,salt')
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where($where)
            //->where('salesman',1)
            ->order($order)
            ->paginate($limit);

        foreach ($res as $re) {
            $re->nickname_text = $re->username . '(ID:' . $re->id . ')';
        }

        $this->success('', [
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => get_route_remark(),
            
        ]);
    }

    public function count():void
    {
        $count = $this->model->count();
        $this->success('', ['count' => $count]);
    }
    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}