<?php

namespace app\api\controller;


use ba\Tree;
use Throwable;
use think\facade\Db;
use think\facade\Config;
use think\Request;
use app\common\controller\Api;
use app\common\controller\Frontend;
use app\common\library\token\TokenExpirationException;
use Symfony\Component\Finder\Finder;
use think\facade\Cache;
use think\facade\Log;
/**
 * NFT购买记录管理
 */
class Task extends Frontend
{
    protected array $noNeedLogin = ['runTask','runmyprice','runm_Task','autoTrade'];
    protected $allowedIps = ['127.0.0.1']; // 替换为您的 IP
    public function initialize(): void
    {
        parent::initialize(); 
    }
    
// 方式1：使用 cURL 获取价格（推荐）
function getCryptoPricesCurl() {
    // 设置 API URL 和参数
    $url = "https://api.coingecko.com/api/v3/simple/price";
    $params = http_build_query([
        'ids' => 'bitcoin,ethereum',
        'vs_currencies' => 'usd,cny',
        'include_24hr_change' => 'true'
    ]);
    
    // 初始化 cURL
    $ch = curl_init($url . "?" . $params);
    
    // 设置 cURL 选项
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => 'Mozilla/5.0' // 设置 User-Agent 避免被封
    ]);
    
    // 执行请求
    $response = curl_exec($ch);
    $error = curl_error($ch);
    
    // 关闭 cURL
    curl_close($ch);
    
    if ($error) {
        throw new \Exception("cURL Error: " . $error);
    }
    
    // 解析 JSON 响应
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new \Exception("JSON decode error: " . json_last_error_msg());
    }
    
    return $data;
}

// 方式2：使用 file_get_contents
function getCryptoPricesFileGet() {
    $url = "https://api.coingecko.com/api/v3/simple/price";
    $params = http_build_query([
        'ids' => 'bitcoin,ethereum',
        'vs_currencies' => 'usd,cny',
        'include_24hr_change' => 'true'
    ]);
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'User-Agent: Mozilla/5.0'
        ]
    ]);
    
    $response = file_get_contents($url . "?" . $params, false, $context);
    return json_decode($response, true);
}

// 获取更详细的数据
function getDetailedCryptoData() {
    $url = "https://api.coingecko.com/api/v3/coins/markets";
    $params = http_build_query([
        'vs_currency' => 'usd',
        'ids' => 'bitcoin,ethereum',
        'order' => 'market_cap_desc',
        'per_page' => 2,
        'page' => 1,
        'sparkline' => 'false',
        'price_change_percentage' => '1h,24h,7d'
    ]);
    
    $ch = curl_init($url . "?" . $params);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => 'Mozilla/5.0'
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}
    public function runmyprice()
    {
        
        // 使用示例
        $prices = $this->getCryptoPricesCurl();
            if(isset($prices['ethereum']))
              Db::name("config")->where('name','eth_percent')->update(['value'=>$prices['ethereum']['usd']]);
              
            if(isset($prices['bitcoin']))
              Db::name("config")->where('name','btc_percent')->update(['value'=>$prices['bitcoin']['usd']]);
           // Cache::tag('sys_config')->clear(); 
            return 'Task executed successfully!';//.$clientIp;
    }
    public function runTask()
    {
        // 获取当前请求的 IP 地址
        // $clientIp = request()->ip();
        // // 验证 IP 地址是否在允许的范围内
        // if ($clientIp !== '127.0.0.1' && $clientIp !== '162.158.179.28' && $clientIp !== '::1') {
        //      return 'Unauthorized access';
        // }
        // 获取当前时间
        $currentTime = date('H:i'); // 格式化为小时:分钟
        echo "当前时间".date('Y-m-d H:i:s')." ".$currentTime."<br>\r\n";
        // 设置时间范围
        $startTime = '00:00';
        $endTime = '00:02';

        // 判断当前时间是否在范围内
        if ($currentTime >= $startTime && $currentTime <= $endTime) {
            // 在 00:00 到 00:02 之间执行的代码 
            echo "当前时间".date('Y-m-d')." ".$currentTime.",在 00:00 到 00:02 之间，执行相关操作.<br>";
            try {
                // 开始事务
                Db::transaction(function () {
                    $yesterday = strtotime('yesterday');
                    $yesterday23 = strtotime(date('Y-m-d 23:00:00', $yesterday)); //判断购买时间是否超过前一天的23点
                    //nft 收益
                    Db::execute("insert into ba_user_money_log(user_id,business_type,business_typeid,money,`before`,`after`,business_id,memo,create_time,parent_id) 
                    select a.user_id,'nft收益',3,b.price*(earning_rate/100),c.money,c.money+b.price*(earning_rate/100),a.product_id,'每天计算收益',".time().",a.id 
                    from ba_nft_purchase a left join ba_nft_product b on a.product_id=b.id 
                    left join ba_user c on a.user_id=c.id
                    where a.status='1' and b.id is not null and a.create_time<".$yesterday23);
                    //更新nft收益到会员金额内
                    Db::execute("update ba_user a inner join(
                    select a.user_id,sum(b.price*(earning_rate/100)) as bmoney
                    from ba_nft_purchase a left join ba_nft_product b on a.product_id=b.id 
                    where a.status='1' and b.id is not null  and a.create_time<".$yesterday23." group by a.user_id) b
                    set a.money=a.money+b.bmoney where a.id=b.user_id");
                     
                    //把nft的金额返还给用户
                    Db::execute("insert into ba_user_money_log(user_id,business_type,business_typeid,money,business_id,memo,create_time,parent_id ) 
                    select a.user_id,'nft到期退还',6,a.price,a.product_id,'每天结束nft返还金额',".time()." ,a.id
                    from (select a.id,user_id,b.price,a.product_id from ba_nft_purchase a left join ba_nft_product b on a.product_id=b.id 
                    where a.status='1' and a.create_time<".$yesterday23.") a");
                    //把nft的金额返还给用户
                    Db::execute("update ba_user a inner join (select sum(b.price) as price,a.user_id from ba_nft_purchase a left join ba_nft_product b on a.product_id=b.id 
                    where a.status='1' and a.create_time<".$yesterday23." group by a.user_id) b 
                    set a.money =a.money+b.price where a.id=b.user_id" );
                    //每天结束nft
                    Db::execute("update ba_nft_purchase set status = '0' where create_time<".$yesterday23);
                  
                });
                exit('Task executed successfully!') ;//.$clientIp;
            // 在这里编写您的定时任务逻辑
            // 比如：发送邮件、清理缓存、更新数据等
            // ...
            } catch (\Exception $e) {
                // 捕获异常，事务会自动回滚
                return "Error occurred: " . $e->getMessage();
            }
        } else {
            // 不在范围内的逻辑
            echo "当前时间".date('Y-m-d')." ".$currentTime.",不执行.<br>\r\n";
        }
        
        
    }
    public function runm_Task()
    {
        // 获取当前请求的 IP 地址
        // $clientIp = request()->ip();
        // // 验证 IP 地址是否在允许的范围内
        // if ($clientIp !== '127.0.0.1' && $clientIp !== '162.158.179.28' && $clientIp !== '::1') {
        //      return 'Unauthorized access';
        // }
        $currentTime = date('H:i'); // 格式化为小时:分钟
        echo "当前时间".date('Y-m-d')." ".$currentTime.",执行相关操作.<br>\r\n";
        try {
            // 开始事务
            Db::transaction(function () {
                $thisaddtime=86400;//24小时的秒数
                $todaytime=strtotime(date('Y-m-d'));
                $todaytime1=strtotime(date('Y-m-d 23:59:59'));
                $yesterday = strtotime('yesterday');
                $yesterday23 = strtotime(date('Y-m-d 23:00:00', $yesterday)); //判断购买时间是否超过前一天的23点 
                // 当前时间的时间戳
                $currentTimestamp = time();
                $getaitable=Db::name("ai_pledge")
                ->alias('a')
                ->leftjoin('ai_product b','b.id = a.product_id')
                ->leftjoin('user c','c.id = a.user_id')
                ->leftjoin("(select id,business_id,user_id,parent_id from ba_user_money_log where business_typeid=4 and create_time>=".$todaytime." and create_time<=".$todaytime1.") d"
                ,'a.id=d.parent_id') 
                ->leftjoin("(select count(*) as sycount,parent_id from ba_user_money_log where business_typeid=4 group by parent_id) sytab"
                ,'a.id=sytab.parent_id') 
                ->whereRaw("a.expire_time>".$currentTimestamp."")
                ->whereRaw("a.status='1'")
                ->whereRaw("b.id is not null")
                ->whereRaw("d.id is null")
                ->field("a.*,c.money,a.price*(b.earning_rate/100) as addmoney,ifnull(sycount,0) as sycount")->select(); 
                
                
                foreach ($getaitable as $key => $value) { 
                    // 计算时间差（秒）
                    $timeDifference = $currentTimestamp - $value['confirm_time'];
                    // 将时间差转换为天数
                    $daysDifference = floor($timeDifference / (60 * 60 * 24)); // 每天86400秒
                    if($daysDifference>0&&$daysDifference>=($value['sycount']+1)){
                        echo "id:".$value['id'].",收益<br>\r\n"; 
                        Db::name("user_money_log")->insertGetId(['user_id'=>$value['user_id'],'business_id'=>$value['product_id'],
                        'business_typeid'=>'4','business_type'=>'ai收益',
                        'money'=>$value['addmoney'],'before'=>$value['money'],'after'=>$value['money']+$value['addmoney'],
                        'create_time'=>$currentTimestamp,'parent_id'=>$value['id']]); 
                        Db::name("user")->where("id",$value['user_id'])->update(['money'=>$value['money']+$value['addmoney']]);
                    }  
                }
                
                //把ai的金额返还给用户
                Db::execute("insert into ba_user_money_log(user_id,business_type,business_typeid,money,business_id,memo,create_time,`before`,`after`,parent_id) 
                select a.user_id,'ai到期退还',7,a.price,a.product_id,'每天结束nft返还金额',".$currentTimestamp." ,a.money,a.money+a.price,a.id
                from (select a.id,user_id,a.price,a.product_id,c.money from ba_ai_pledge a left join ba_ai_product b on a.product_id=b.id 
                left join ba_user c on a.user_id=c.id
                where a.status='1' and a.expire_time<".$currentTimestamp.") a");
                //把ai的金额返还给用户
                Db::execute("update ba_user a inner join (select sum(a.price) as price,a.user_id from ba_ai_pledge a left join ba_ai_product b on a.product_id=b.id 
                where a.status='1' and a.expire_time<".$currentTimestamp." group by a.user_id) b 
                set a.money =a.money+b.price where a.id=b.user_id" ); 
                //每分钟结束ai
                Db::execute("update ba_ai_pledge set status = '0' where expire_time<".$currentTimestamp);
            });
            exit('Task executed successfully!') ;//.$clientIp;
        // 在这里编写您的定时任务逻辑
        // 比如：发送邮件、清理缓存、更新数据等
        // ...
        } catch (\Exception $e) {
            // 捕获异常，事务会自动回滚
            return "Error occurred: " . $e->getMessage();
        }
        
    }
        // 将今天的日期与时间字符串结合，转换为时间戳
    function timeToTimestamp($timeStr) {
        $today = date('Y-m-d');
        return strtotime("$today $timeStr");
    }

    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
    /**
     * Auto Trade - Creates random trading records for active bots
     * This method will be called by a cron job every few minutes
     * It randomly selects active bots and creates buy/sell transactions for them
     */
    public function autoTrade()
    { 
    // "一级开启200U ": "Level 1 unlocks 200U",
	// "二级开启500U（需要介绍3-5人才能开启）": "Level 2 unlocks 500U (requires inviting 3-5 people)",
	// "三级开启1000U（需要介绍5-10人才能开启）": "Level 3 unlocks 1000U (requires inviting 5-10 people)",
	// "四级开启2000U（需要介绍10-15人才能开启）": "Level 4 unlocks 2000U (requires inviting 10-15 people)",
	// "五级开启5000U（需要介绍25人以上）": "Level 5 unlocks 5000U (requires inviting 25+ people)",
        // Log start of auto trading
        $this->loginfo('8999999999999999'); 
        // 使用不同的日志记录方式
        // Top 50 popular cryptocurrency symbols
        $popularCoins = [
            'BTC', 'ETH', 'USDT', 'BNB', 'SOL', 'XRP', 'USDC', 'ADA', 'AVAX', 'DOGE',
            'DOT', 'MATIC', 'LTC', 'LINK', 'SHIB', 'TRX', 'UNI', 'ETC', 'TON', 'NEAR',
            'BCH', 'XLM', 'ATOM', 'ICP', 'FIL', 'HBAR', 'APT', 'XMR', 'VET', 'ALGO',
            'QNT', 'GRT', 'AAVE', 'EOS', 'FLOW', 'THETA', 'AXS', 'XTZ', 'SAND', 'MANA',
            'APE', 'EGLD', 'KCS', 'NEO', 'FTM', 'CAKE', 'CHZ', 'KLAY', 'RUNE', 'AR'
        ];

        // 定义不同等级的交易金额限制
        $levelLimits = [
            1 => 200,  // 一级：200U
            2 => 500,  // 二级：500U 
            3 => 1000, // 三级：1000U
            4 => 2000, // 四级：2000U
            5 => 5000  // 五级：5000U
        ];
        
        // 获取停止时间和停止点百分比设置
        $stopTime = $this->timeToTimestamp(get_sys_config('stoptime', "23:00")) ;
        $stopPercent = floatval(get_sys_config('stoppercent', 5)); // 默认5%
        $winpercent = floatval(get_sys_config('winpercent', 3)); // 默认10%
        $losepercent = floatval(get_sys_config('losepercent', 3)); // 默认5%
        
        // 获取当前时间
        $currentTime = $this->timeToTimestamp(date('H:i'));
        $isAfterStopTime = false;
        
        $this->loginfo("66666666666666666666Auto Trade: Current time {$currentTime} is after stop time {$stopTime}, will force stop bots with profit target");
        // 判断当前时间是否超过停止时间
        if (!empty($stopTime) && $currentTime >= $stopTime) {
            $isAfterStopTime = true;
            $this->loginfo("Auto Trade: Current time {$currentTime} is after stop time {$stopTime}, will force stop bots with profit target");
        }

        // Log start of auto trading
        $this->loginfo('Auto Trade: Starting automatic trading for random active bots');
 
        Log::write('777777788888888', 'info');
        
        // $this->loginfo('7777777777777777777777');
        // Find active bots with limit of 5-15 random bots
        $limit = 100;
        Log::write("Auto Trade: Selecting {$limit} random active bots", 'info');
         

        $activeBots = Db::name("nft_purchase")
            ->where("source", 'Trading Bot')
            ->where("status", '1')
            ->orderRaw('RAND()')
            ->limit($limit)
            ->select()
            ->toArray();

        $this->loginfo('Auto Trade: Found ' . count($activeBots) . ' active bots');

        if (empty($activeBots)) {
            Log::error("Auto Trade: No active bots found, exiting"); 
            return;
        }
 
        foreach ($activeBots as $bot) {
            // 获取用户信息和用户等级
            $user = Db::name("user")->where("id", $bot['user_id'])->find();
            if (!$user) {
                Log::error("Auto Trade: User not found for ID {$bot['user_id']}, skipping");
                continue;
            }
            
            // 获取用户等级
            $userLevel = isset($user['level']) ? intval($user['level']) : 1;
            if ($userLevel < 1 || $userLevel > 5) {
                $userLevel = 1; // 默认为1级
            }
            
            // 确定用户可交易的最大金额（基于等级）
            $maxLevelAmount = $levelLimits[$userLevel];
            
            // 确保用户有足够的余额进行交易
            $maxPossibleAmount = min($maxLevelAmount, $user['money']);
            
            // 如果是停止时间之后，且机器人未达到停止点，则强制达到停止点并停止机器人
            if ($isAfterStopTime) {
                // 获取今天的开始时间戳
                $todayStart = strtotime(date('Y-m-d 00:00:00'));
                
                // 检查机器人的创建时间是否为今天，且创建时间晚于stopTime
                $botCreateTime = $bot['create_time'];
                $isBotCreatedTodayAfterStopTime = ($botCreateTime >= $todayStart) && ($botCreateTime >= $stopTime);
                
                // 如果机器人是今天在停止时间之后创建的，则跳过停止操作，进行正常交易
                if ($isBotCreatedTodayAfterStopTime) {
                    $this->loginfo("Auto Trade: Bot ID {$bot['id']} was created today after stop time, skipping stop operation");
                    // 跳过停止逻辑，继续正常交易
                    goto normalTrade;
                }
                
                // 计算目标余额（停止点）
                $targetBalance = $bot['botprice'] + $maxPossibleAmount * ($stopPercent / 100);
                
                $this->loginfo("Bot ID {$bot['id']} "."停止时间：".$stopTime." 最大层级金额：".$maxLevelAmount.'--当前玩家金额：'.$user['money'].'--开始金额：'.$bot['botprice'].'--停止比例：'.$stopPercent);
                $currentBalance = $user['money'];
                
                // 如果当前余额已经达到或超过目标余额，则直接停止机器人
                if ($currentBalance >= $targetBalance ) {
                    $this->loginfo("Auto Trade: Bot ID {$bot['id']} already reached stop target, stopping bot");
                    
                    // 停止机器人
                    Db::name("nft_purchase")->where("id", $bot['id'])->update([
                        'status' => 0,
                        'botprice' => 0,
                    ]);
                    
                    continue;
                }
                
                // 计算需要获取的利润
                $neededProfit = $targetBalance - $currentBalance;
                
                // 设置合理的买入金额，不超过用户级别限制和当前余额
                $buyAmount = min($maxPossibleAmount, $maxLevelAmount);
                
                // 确保买入金额大于0
                if ($buyAmount <= 0) {
                    $this->loginfo("Auto Trade: User ID {$bot['user_id']} has insufficient balance for forced stop");
                    continue;
                }
                
                // 计算所需利润率
                $requiredProfitRate = $neededProfit / $buyAmount;
                
                // 设置卖出金额
                $sellAmount = $buyAmount + $neededProfit;
                
                // 选择随机币种
                $coinIndex = mt_rand(0, 49);
                $selectedCoin = $popularCoins[$coinIndex];
                
                // 生成时间（近期）
                $buyTime = time() - mt_rand(300, 900); // 3-10分钟前
                $sellTime = time() - mt_rand(60, 180); // 30秒到2分钟后
                
                $this->loginfo("Auto Trade: Forcing bot ID {$bot['id']} to reach stop target of {$stopPercent}%");
                $this->loginfo("Auto Trade: Buy amount: {$buyAmount}, Needed profit: {$neededProfit}, Profit rate: " . ($requiredProfitRate * 100) . "%");
                
                // 执行交易和停止操作
                Db::startTrans();
                try {
                    // 记录买入交易
                    Db::name("user_money_log")->insertGetId([
                        'user_id' => $bot['user_id'],
                        'business_type' => "Buy {$selectedCoin}",
                        'money_type' => "USDT",
                        'business_typeid' => "3",   
                        'money' => -$buyAmount,
                        'before' => $user['money'],
                        'after' => $user['money'] - $buyAmount,
                        'create_time' => $buyTime,
                        'business_id' => $bot['id'], 
                        'type' => 2, 
                        'currency' => $selectedCoin,  
                        'profit_rate' => 0,
                        'memo' => "Auto buy {$selectedCoin} (reach stop target)"
                    ]);
                    
                    // 记录卖出交易
                    Db::name("user_money_log")->insertGetId([
                        'user_id' => $bot['user_id'],
                        'business_type' => "Sell {$selectedCoin}",
                        'money_type' => "USDT",
                        'business_typeid' => "4",
                        'money' => $sellAmount,
                        'before' => $user['money'] - $buyAmount,
                        'after' => $user['money'] - $buyAmount + $sellAmount,
                        'create_time' => $sellTime,
                        'business_id' => $bot['id'],
                        'type' => 2,
                        'currency' => $selectedCoin,  
                        'profit' => number_format($neededProfit, 4),
                        'profit_rate' => number_format($requiredProfitRate * 100, 2),
                        'memo' => "Auto sell {$selectedCoin} (reach stop target)"
                    ]);
                    
                    // 更新用户余额
                    Db::name("user")->where("id", $bot['user_id'])->update([
                        'money' => $targetBalance // 直接设置为目标余额
                    ]);
                    
                    // 停止机器人
                    Db::name("nft_purchase")->where("id", $bot['id'])->update([
                        'status' => 0,
                        'botprice' => 0,
                    ]);
                    
                    Db::commit();
                    $this->loginfo("Auto Trade: Successfully forced bot ID {$bot['id']} to reach stop target and stopped bot");
                } catch (\Exception $e) {
                    Db::rollback();
                    Log::error("Auto Trade: Failed to process stop target for bot ID {$bot['id']}: " . $e->getMessage());
                }
                
                continue; // 处理下一个机器人
            }
            
            // 这里是正常交易逻辑的标记点
            normalTrade:
            
            // 如果可用金额小于该等级的最低限额，则跳过
            // if ($maxPossibleAmount < $levelLimits[$userLevel]) {
            //     $this->loginfo("Auto Trade: User ID {$bot['user_id']} has insufficient balance for level {$userLevel}, skipping");
            //     continue;
            // }
            
            // Generate random time for buy transaction (3-10 minutes ago) 
            $buyTime = time() - mt_rand(600,1200);
            
            // Sell time is 30-60 seconds after buy time
            // $randomSeconds = $randomMinutes* mt_rand(30, 60); 
            $sellTime = time() - mt_rand(60, 300); // 30秒到2分钟后
            
            // Select random coin
            $coinIndex = mt_rand(0, 49); 
            $selectedCoin = $popularCoins[$coinIndex];
            
            // 生成符合用户等级限制的随机交易金额
            if($userLevel==1) $minAmount = 10; // 最小为10U或等级限额的10%
            else $minAmount = $levelLimits[$userLevel-1];// 最小为10U或等级限额的10%
            $maxAmount =  min($levelLimits[$userLevel],$maxPossibleAmount); // 不超过200U和用户可用最大额度
            
            // 确保最小值不大于最大值
            if ($minAmount > $maxAmount) {
                $minAmount = $maxAmount * 0.5;
            }
            
            $buyAmount = mt_rand(ceil($minAmount), floor($maxAmount));
            
            // 检查用户当前余额相对于初始资金的盈亏情况
            $currentBalance = $user['money'];
            $initialBalance = $bot['botprice'];
            $currentProfitRate = ($currentBalance - $initialBalance) / $initialBalance * 100;
            
            // 根据当前盈亏情况调整随机利润率的范围
            $profitPercentage = 0;
            
            if ($currentProfitRate >= $winpercent) {
                // 已经达到止盈点，随机生成亏损交易使其回落
                $profitPercentage = mt_rand(-300, -50) / 10000; // -8% to -1%
                $this->loginfo("Auto Trade: Bot ID {$bot['id']} above win threshold, generating loss trade to stabilize");
            } else if ($currentProfitRate <= -$losepercent) {
                // 已经达到止损点，随机生成盈利交易使其回升
                $profitPercentage = mt_rand(50, 300) / 10000; // 1% to 8%
                $this->loginfo("Auto Trade: Bot ID {$bot['id']} below loss threshold, generating profit trade to recover");
            } else {
                // 在正常范围内，生成随机盈亏
                $profitPercentage = mt_rand(-300, 300) / 10000; // -8% to 8%
            }
            
            $sellAmount = $buyAmount * (1 + $profitPercentage);
            $profit = $sellAmount - $buyAmount;
            
            $this->loginfo("Auto Trade: Processing bot ID {$bot['id']} for user ID {$bot['user_id']}, Level: {$userLevel}");
            $this->loginfo("Auto Trade: Selected coin: {$selectedCoin}, Buy amount: {$buyAmount}, Profit: {$profitPercentage}%");
            
            // Transaction descriptions
            $buyMemo = "Auto buy {$selectedCoin}";
            $sellMemo = "Auto sell {$selectedCoin} with " . number_format($profitPercentage * 100, 2) . "% profit";
            
            // 移除停止机器人的逻辑，改为在盈亏范围内震荡
            $nowuser = Db::name("user")->where("id", $bot['user_id'])->find();
            $nowbalance = $nowuser['money'] - $buyAmount + $sellAmount;
            
            $this->loginfo("Auto Trade: User balance after trade will be: {$nowbalance}, relative to initial: " . 
                number_format(($nowbalance - $bot['botprice']) / $bot['botprice'] * 100, 2) . "%");
            
            // Begin transaction to ensure data consistency
            Db::startTrans();
            try {
                // Record buy transaction
                Db::name("user_money_log")->insertGetId([
                    'user_id' => $bot['user_id'],
                    'business_type' => "Buy {$selectedCoin}",
                    'money_type'=> "USDT",
                    'business_typeid' => "3",   
                    'money' => -$buyAmount, // Negative for spending
                    'before' => $user['money'],
                    'after' => $user['money'] - $buyAmount,
                    'create_time' => $buyTime,
                    'business_id' => $bot['id'], 
                    'currency' => $selectedCoin ,  
                    'type'=>2,
                    'profit_rate' => 0,
                    'memo' => $buyMemo
                ]);
                
                // Record sell transaction
                Db::name("user_money_log")->insertGetId([
                    'user_id' => $bot['user_id'],
                    'business_type' => "Sell {$selectedCoin}",
                    'business_typeid' => "4",
                    'money' => $sellAmount, // Positive for earning
                    'before' => $user['money'] - $buyAmount,
                    'after' => $user['money'] - $buyAmount + $sellAmount,
                    'create_time' => $sellTime,
                    'business_id' => $bot['id'],
                    'money_type'=> "USDT",
                    'currency' => $selectedCoin ,  
                    'type'=>2,
                    'profit' => number_format(($sellAmount - $buyAmount),4), // Calculate profit rate as percentage
                    'profit_rate' => number_format(($sellAmount - $buyAmount) / $buyAmount * 100, 2), // Calculate profit rate as percentage
                    'memo' => $sellMemo
                ]);
                
                // Update user balance
                Db::name("user")->where("id", $bot['user_id'])->update([
                    'money' => $user['money'] - $buyAmount + $sellAmount
                ]);
                
                Db::commit();
                $this->loginfo("Auto Trade: Successfully created transactions for bot ID {$bot['id']}, profit: {$profit} USDT");
            } catch (\Exception $e) {
                Db::rollback();
                Log::error("Auto Trade: Failed to create transactions for bot ID {$bot['id']}: " . $e->getMessage());
            }
        }
        
        $this->loginfo('Auto Trade: Completed automatic trading process');
        exit('Auto trading completed successfully!');
        // $this->success('Auto trading success: ');
    }
    public function loginfo($msg){
        Log::write($msg);
        error_log(date('Y-m-d H:i:s')." ".$msg."\r\n",3,"autotrade.log");
        
    } 
}