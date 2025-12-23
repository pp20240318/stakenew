<?php

namespace app\api\controller;

use Throwable;
use ba\Captcha;
use ba\Random;
use ba\ClickCaptcha;
use think\facade\Config;
use think\facade\Db;
use think\facade\Cache;
use think\Request;
use app\common\facade\Token;
use app\admin\model\User as UserModel;
use app\common\controller\Frontend;
use app\api\validate\User as UserValidate;
use app\service\TokenService;

class User extends Frontend
{ 
    protected array $noNeedLogin = [ 'index','register','login','logout','share','truename','savetruename','truename2','savetruename2','changepassword','loanlist','borrownow','noticelist','noticedetail','invitecode','teaminfo','initGuest','guestBalance'];

    protected array $noNeedPermission = ['index'];

    protected $tokenService;

    public function initialize(): void
    {
        parent::initialize();
        $this->tokenService = new TokenService();
        
        // 临时调试：检查当前语言设置
        $currentLang = \think\facade\Lang::getLangSet();
        \think\facade\Log::info('User Controller: Current language is: ' . $currentLang);
        
        // 临时调试：检查请求头
        $acceptLang = request()->header('accept-language');
        \think\facade\Log::info('User Controller: Accept-Language header: ' . ($acceptLang ?: 'none'));
        
        // 临时调试：测试翻译
        $testTranslation = __('not exist');
        \think\facade\Log::info('User Controller: Translation test for "not exist": ' . $testTranslation);
    }
 
    function generateRandomCode() {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
    
        for ($i = 0; $i < 6; $i++) {
            $char = $characters[rand(0, strlen($characters) - 1)];
            $code .= $char;
        }
    
        return $code;
    }
    public function noticelist(Request $request)
    {
        $userid = $request->param("userid");
        $type = $request->param("type");
        $getusers=Db::name("user")->where("id",$userid)->select();
        if(count($getusers)==0){
            $this->error('User not found');
        }
        $getusers=$getusers[0];
        if(empty($type)){
            $getalter=Db::name("content_article")
            ->whereRaw("(ifnull(user_ids,'') = '' OR user_ids LIKE '%,".$userid.",%')") 
            ->where("status",1)
            ->order('weigh','asc')
            ->select();
        }else{
            $getalter=Db::name("content_alter")
        ->whereRaw("(ifnull(user_ids,'') = '' OR user_ids LIKE '%,".$userid.",%')") 
        ->where("show",1)
        ->order('create_time','desc')
        ->select();
        }
        
          
        $this->success('', [
            'list'=>$getalter 
        ]);
    }
    public function noticedetail(Request $request)
    {
        $id = $request->param("id");
        $userid = $request->param("userid");
        $type = $request->param("type");
        $getusers=Db::name("user")->where("id",$userid)->select();
        if(count($getusers)==0){
            $this->error('User not found');
        }
        $getusers=$getusers[0];
        if(empty($type)){
        $getalter=Db::name("content_article")->where('id',$id)->find(); 
    }else{
        $getalter=Db::name("content_alter")->where('id',$id)->find(); 
    }
        $this->success('', [
            'list'=>$getalter 
        ]);
    }
    public function truename(Request $request)
    {
        $userid = $request->param("userid");
        $getusers=Db::name("user")->where("id",$userid)->select();
        if(count($getusers)==0){
            $this->error('User not found');
        }
        $getusers=$getusers[0];
        $getuser_verify=Db::name("user_verify")->where("user_id",$userid)->select();
        if(count($getuser_verify)==0){
            $getuser_verify=array();
            $this->success('', [
                'name'=>'', 
                'certificate'=>'',
                'passport_image'=>'',
                'status'=>'', 
            ]);
        }
        $getuser_verify=$getuser_verify[0];
        $this->success('', [
            'name'=>$getuser_verify['name'], 
            'certificate'=>$getuser_verify['certificate'],
            'status'=>$getuser_verify['status2'],
            'passport_image'=>$getuser_verify['img_front'] ?? '',
        ]);
    }
    public function savetruename(Request $request)
    {
        $userid = $request->userId;
        $name = $request->param("name");
        $certificate = $request->param("certificate");
        $passport_image = $request->param("passport_image");
        
        $getusers=Db::name("user")->where("id",$userid)->select();
        if(count($getusers)==0){
            $this->error('User not found');
        }
        $getusers=$getusers[0];
        $getuser_verify=Db::name("user_verify")->where("user_id",$userid)->select();
        if(count($getuser_verify)==0){
            $res=Db::name("user_verify") ->insertGetId([
                'user_id'=>$userid,
                'name'=>$name,
                'certificate'=>$certificate,
                'img_front'=>$passport_image,
                'status'=>3 ,
                'create_time'=>time()
            ]);
        }else{
            Db::name("user_verify")->where("user_id",$userid) ->update([
                'name'=>$name,
                'certificate'=>$certificate,
                'img_front'=>$passport_image,
                'status'=>3
            ]);
        }
        $this->success('', [ 
        ]);
    }
    public function truename2(Request $request)
    {
        $userid = $request->param("userid");
        $getusers=Db::name("user")->where("id",$userid)->select();
        if(count($getusers)==0){
            $this->error('User not found');
        }
        $getusers=$getusers[0];
        $getuser_verify=Db::name("user_verify")->where("user_id",$userid)->select();
        if(count($getuser_verify)==0){
            $getuser_verify=array();
            $this->success('', [
                'img_front'=>'', 
                'img_back'=>'',
                'status'=>'',
            ]);
        }
        $getuser_verify=$getuser_verify[0];
        $this->success('', [
            'img_front'=>$getuser_verify['img_front'], 
            'img_back'=>$getuser_verify['img_back'],
            'status'=>$getuser_verify['status'],
        ]);
    }
    public function savetruename2(Request $request)
    {
        $userid = $request->param("userid"); 
        $img_front = $request->param("img_front");
        $img_back = $request->param("img_back");
        $getusers=Db::name("user")->where("id",$userid)->select();
        if(count($getusers)==0){
            $this->error('User not found');
        }
        $getusers=$getusers[0];
        $getuser_verify=Db::name("user_verify")->where("user_id",$userid)->select();
        if(count($getuser_verify)==0){
            $res=Db::name("user_verify") ->insertGetId([
                'user_id'=>$userid,
                'img_front'=>$img_front,
                'img_back'=>$img_back,
                'status'=>3 , 
                'create_time'=>time()
            ]);
        }else{
            Db::name("user_verify")->where("user_id",$userid) ->update(['img_front'=>$img_front,
                'img_back'=>$img_back,
                'status'=>3 ]);
        }
        $this->success('', [ 
        ]);
    }
    public function share(Request $request)
    {
        $userid = $request->param("userid");
        $getusers=Db::name("user")->where("id",$userid)->select();
        if(count($getusers)==0){
            $this->error('User not found');
        }
        $getusers=$getusers[0];
        $this->success('', [
            'invite_code'=>$getusers['invite_code'], 
        ]);
    }
    public function index(Request $request)
    {
        $userid = $request->userId;
        //Cache::tag('sys_config')->clear(); 
        $getthisday=strtotime(date('Y-m-d')); 
        $getmoney=0;
        $getsalecount=0;
        $getthisUser=[];
        $getthisUsermoney=[];
        $getreturnuser_verify=array('status'=>'3','status2'=>'3');
        
        // 初始化总收益率数据
        $profitRate = 0;
        $totalBuy = 0;
        $totalSell = 0;
        
        $levelLimits = [
            1 => 3,  // 一级：200U
            2 => 5,  // 二级：500U 
            3 => 10, // 三级：1000U
            4 => 25, // 四级：2000U
            5 => -1  // 五级：5000U
        ];
        $taskLimits = [
            1 => 200,  // 一级：200U
            2 => 500,  // 二级：500U 
            3 => 1000, // 三级：1000U
            4 => 2000, // 四级：2000U
            5 => 5000 // 五级：5000U
        ];
 
 
        if(!empty($userid)){
            $getthisUsermoney=Db::name("user_money_log")->field("sum((case when business_id in (3,4) then money else 0 end)) as allmoney,sum((case when business_id in (3,4) and create_time>=".$getthisday." then money else 0 end)) as todaymoney,sum((case when business_id=5 then money else 0 end)) as invitemoney")
            ->where("user_id",$userid)->whereIn('business_id',array(3,4,5))->select(); 
            $getthisUser=Db::name("user")->where('id',$userid)->withoutField(['password', 'password2', 'salt'])->find();
            $upgradenum = $levelLimits[ $getthisUser['level']];  
            if(count($getthisUsermoney)>0) $getthisUsermoney=$getthisUsermoney[0];
            $getnft=Db::name("nft_purchase")->where("user_id",$userid)->where("status",1)->select();
             
            $getuser_verify=Db::name("user_verify")->where("user_id",$userid)->select();
            if(count($getuser_verify)>0) $getreturnuser_verify=$getuser_verify[0];
            
            // 计算总收益率
            // 获取所有买入交易记录总金额
            $buyTransactions = Db::name("user_money_log")
                ->where("user_id", $userid)
                ->where("business_id", 3)
                ->sum('money');
            $totalBuy = abs($buyTransactions); // 买入为负数，需要取绝对值
            
            // 获取所有卖出交易记录总金额
            $sellTransactions = Db::name("user_money_log")
                ->where("user_id", $userid)
                ->where("business_id",4)
                ->sum('money');
            $totalSell = $sellTransactions; // 卖出为正数
            
            // 计算收益率
            if($totalBuy > 0) {
                $profitRate = number_format((($totalSell - $totalBuy) / $totalBuy) * 100, 2);
            }
        } 
        $kefu = get_sys_config('kefu');
        $this->success('', [
            'userid'=>$userid,
            'userdata'=>$getthisUser,
            'moneydata'=>$getthisUsermoney, 
            'kefu'=>$kefu,
            'upgradenum'=>$upgradenum,
            'tasklimit'=>$taskLimits[$getthisUser['level']],
            'getreturnuser_verify'=>$getreturnuser_verify,
            'profit_data' => [
                'total_buy' => $totalBuy,
                'total_sell' => $totalSell,
                'profit_rate' => $profitRate
            ]
        ]);
    }
    public function login(Request $request)
    {
        $emailtxt = $request->param("emailtxt");
        $mobilearea = $request->param("mobilearea");
        $mobiletxt = $request->param("mobiletxt");
        $password = $request->param("password");
        $password1 = $request->param("password1");
        $selecttype = $request->param("selecttype");
        $deviceId = $request->param("device_id"); // 设备ID
        $selecttypetxt=$selecttype=='2'?'mobile':'email';
        $thispassword="";

        // 生成唯一token
        $token = Random::uuid();

        $getusers=Db::name("user");
        if($selecttype=='1'){
            $getusers=$getusers->where("email",$emailtxt);
            $thispassword=$password;
        }else{
            $getusers=$getusers->where("mobile",$mobilearea.$mobiletxt);
            $thispassword=$password1;
        }
        $getusers=$getusers->find();
        if(!$getusers)
           return $this->error(__("not exist"));
        if($getusers['password'] != encrypt_password($thispassword, $getusers['salt'])) {
           return $this->error(__("password error"));
        }

        // 使用 TokenService 保存 token 和 userId 映射关系到 Redis
        $userId = $getusers['id'];
        $saveResult = $this->tokenService->saveToken($token, $userId);

        // 如果传入了device_id，更新用户的device_id
        if (!empty($deviceId)) {
            Db::name("user")->where("id", $userId)->update(['device_id' => $deviceId]);
        }

        // 获取用户数据（排除password和salt字段）
        $userData = Db::name("user")->where("id", $userId)->withoutField(['password', 'password2','salt'])->find();

        // 记录保存结果
        \think\facade\Log::info("Login - saveToken result: " . ($saveResult ? 'success' : 'failed') . " for user: $userId, token: $token");

        // 设置token缓存（保留原有的缓存逻辑作为备份）
        $keepTime = 86400; // token有效期：24小时
        $tokenData = [
            'user_id' => $userId,
            'token' => $token,
            'create_time' => time(),
            'expire_time' => time() + $keepTime,
            'last_active_time' => time()
        ];

        // 存储token到缓存
        $cacheKey = 'user_token_' . $token;
        Cache::set($cacheKey, $tokenData, $keepTime);

        // 清除该用户可能存在的其他token
        $userTokensKey = 'user_tokens_' . $userId;
        $userTokens = Cache::get($userTokensKey, []);
        foreach ($userTokens as $oldToken) {
            Cache::delete('user_token_' . $oldToken);
        }

        // 更新用户token列表
        $userTokens = [$token];
        Cache::set($userTokensKey, $userTokens, $keepTime);

        return $this->success(__('Login succeeded!'),['userid'=>$userId,'token'=>$token,'userdata'=>$userData]);
    }
    public function register(Request $request)
    {
       // 一级 ": "Level 1",
	// "二级": "Level 2 (requires inviting 3-5 people)",
	// "三级": "Level 3 (requires inviting 5-10 people)",
	// "四级": "Level 4 (requires inviting 10-15 people)",
	// "五级": "Level 5  (requires inviting 25+ people)",


        $emailtxt = $request->param("emailtxt");
        $mobilearea = $request->param("mobilearea");
        $mobiletxt = $request->param("mobiletxt");
        $passwordtxt = $request->param("passwordtxt"); // 登录密码
        $passwordtxt1 = $request->param("passwordtxt1"); // 支付密码
        $selecttype = $request->param("selecttype");
        $selecttypetxt=$selecttype=='2'?'mobile':'email';
        $codetxt = $request->param("codetxt");
        $deviceId = $request->param("device_id"); // 设备ID，用于合并临时用户
        $getparentuserid="";
        $getusername="";
        $agent = 0;

        // 邀请码为选填，只有填写了才验证
        if(!empty($codetxt)){
            $getcodeUser=Db::name("user")->field("id,agent")->where("invite_code",$codetxt)->select();
            if(count($getcodeUser)>0){
               $getparentuserid=$getcodeUser[0]['id'];
               if($getcodeUser[0]['agent'])
                    $agent = $getcodeUser[0]['agent'];
                else  {
                    $agent = $getcodeUser[0]['id'];
                }
            }
            else return $this->error(__("Invitation Code not found"));
        }

        // 检查是否有临时用户需要合并
        $guestUser = null;
        if (!empty($deviceId)) {
            $guestUser = Db::name("user")
                ->where("device_id", $deviceId)
                ->where("is_guest", 1)
                ->find();
        }

        $getusers=Db::name("user")->field("id");
        if($selecttype=='1'){
            $getusers=$getusers->where("email",$emailtxt)->where("is_guest", 0);
            $mobiletxt="";
            $mobilearea="";
            $getusername=$emailtxt;
        }else{
            $getusers=$getusers->where("mobile",$mobilearea.$mobiletxt)->where("is_guest", 0);
            $emailtxt="";
            $getusername=$mobilearea.$mobiletxt;
        }
        $getusers=$getusers->select();
        if(count($getusers)>0)
           return $this->error(__("Already Registered"));

        $salt   = Random::build('alnum', 16);
        $passwd = encrypt_password($passwordtxt, $salt);
        $passwd2 = encrypt_password($passwordtxt1, $salt); // 加密支付密码
        $token=Random::uuid();

        // 如果存在临时用户，更新为正式用户
        if ($guestUser) {
            // 更新临时用户为正式用户，保留虚拟余额和订单记录
            Db::name("user")->where("id", $guestUser['id'])->update([
                'username' => $getusername,
                'email' => $emailtxt,
                'mobile' => $mobilearea . $mobiletxt,
                'virtualmoney'=>10000,
                'salt' => $salt,
                'password' => $passwd,
                'password2' => $passwd2,
                'is_guest' => 0, // 标记为正式用户
                'agent' => $agent,
                'reference' => $getparentuserid,
                'update_time' => time()
            ]);
            $getlastid = $guestUser['id'];
        } else {
            // 生成唯一邀请码
            $getinvite_code = "";
            $codeExists = true;
            while ($codeExists) {
                $getinvite_code = $this->generateRandomCode();
                $result = Db::name("user")->where("invite_code", $getinvite_code)->find();
                $codeExists = ($result !== null);
            }

            // 创建新用户
            $getlastid = UserModel::insertGetId([
                'username' => $getusername,
                'email' => $emailtxt,
                'mobile' => $mobilearea . $mobiletxt,
                'invite_code' => $getinvite_code,
                'agent' => $agent,
                'virtualmoney'=>10000,
                'reference' => $getparentuserid,
                'salt' => $salt,
                'password' => $passwd,
                'password2' => $passwd2,
                'device_id' => $deviceId,
                'is_guest' => 0,
                'create_time' => time(),
                'level' => 1
            ]);
        }

        // 如果有上级推荐人，更新上级用户的等级
        if (!empty($getparentuserid)) {
            $this->updateParentLevel($getparentuserid);
        }

        if($agent) {
            $this->updateTeamNum($agent);
        }
        // $getthisuserdata=UserModel::withoutField(['salt','password','password2'])->find($getlastid);
        return $this->success(__('Registration succeeded!'),[]);
    }
    
    /**
     * 更新团队人数
     * 
     * @param int $userId 用户ID
     */
    private function updateTeamNum($userId)
    {
        // 获取该用户直接邀请的人数
        $inviteCount = Db::name("user")->where("agent", $userId)->count();
         
        Db::name("user")->where("id", $userId)->update(['team_num' => $inviteCount]); 

    }
    /**
     * 更新用户等级
     * 根据直接邀请的人数更新用户等级
     * 
     * @param int $userId 用户ID
     */
    private function updateParentLevel($userId)
    {
        // 获取该用户直接邀请的人数
        $inviteCount = Db::name("user")->where("reference", $userId)->count();
        
        // 根据邀请人数设置等级
        $newLevel = 1; // 默认等级
        
        if ($inviteCount >= 25) {
            $newLevel = 5; // 25人以上，5级
        } elseif ($inviteCount >= 10) {
            $newLevel = 4; // 10-15人，4级
        } elseif ($inviteCount >= 5) {
            $newLevel = 3; // 5-10人，3级
        } elseif ($inviteCount >= 3) {
            $newLevel = 2; // 3-5人，2级
        }
        
        // 获取当前用户的等级
        $currentLevel = Db::name("user")->where("id", $userId)->value("level");
        
        Db::name("user")->where("id", $userId)->update(['reference_num' => $inviteCount]);

        // 只有当新等级高于当前等级时才更新
        if ($newLevel > $currentLevel) {
            Db::name("user")->where("id", $userId)->update(['level' => $newLevel]);
        }

        


    }
    public function changepassword(Request $request)
    {
        // Use userId from token authentication
        $userid = $request->userId;
        $getusers=Db::name("user")->where("id",$userid)->select();
        if(count($getusers)==0){
            return $this->error('User not found');
        }
        $getusers=$getusers[0];

        // Get new password from request
        $newpassword = $request->param("newpassword");

        // Validate new password
        if(empty($newpassword)) {
            return $this->error("New password cannot be empty");
        }

        if(strlen($newpassword) < 6) {
            return $this->error("Password must be at least 6 characters");
        }

        // Encrypt and update password
        $passwd = encrypt_password($newpassword, $getusers['salt']);
        Db::name("user")->where("id",$userid)->update(['password'=>$passwd]);

        return $this->success('Password changed successfully');
    }

    /**
     * 修改支付密码
     */
    public function changePayPassword(Request $request)
    {
        // Use userId from token authentication
        $userid = $request->userId;
        $getusers = Db::name("user")->where("id", $userid)->find();
        if (!$getusers) {
            return $this->error('User not found');
        }

        // Get new password from request
        $newpassword = $request->param("newpassword");

        // Validate new password
        if (empty($newpassword)) {
            return $this->error(__("New payment password cannot be empty"));
        }

        if (strlen($newpassword) < 6) {
            return $this->error(__("Payment password must be at least 6 characters"));
        }

        // Encrypt and update payment password (password2 field)
        $passwd2 = encrypt_password($newpassword, $getusers['salt']);
        Db::name("user")->where("id", $userid)->update(['password2' => $passwd2]);

        return $this->success(__('Payment password changed successfully'));
    }

    public function loanlist(Request $request)
    {
        $userid = $request->param("userid");
        $getthisday=strtotime(date('Y-m-d')); 
        $getmoney=0;
        $getsalecount=0;
        $getthisUser=[];
        $getthisloans=[]; 
        $getthisloans=Db::name("loans")->where("user_id",$userid)->select();   
        $daypercent = get_sys_config('daypercent');
        $loan =get_sys_config('loan') ;
        $getloanlist=array(
            array('type'=>30,'min'=>$loan['0']['value'],'max'=>$loan['1']['value'],'title'=>'30天('.$loan['0']['value'].'-'.$loan['1']['value'].')'),
            array('type'=>60,'min'=>$loan['2']['value'],'max'=>$loan['3']['value'],'title'=>'60天('.$loan['2']['value'].'-'.$loan['3']['value'].')'),
            array('type'=>90,'min'=>$loan['4']['value'],'max'=>$loan['5']['value'],'title'=>'90天('.$loan['4']['value'].'-'.$loan['5']['value'].')')
        );
        // foreach ($loan as $item) {
        //     $config[$item['name']] = $item['value'];
        // }
        $getuser_verify=Db::name("user_verify")->where("user_id",$userid)->select();
        $getishaveverify=0;
        if(count($getuser_verify)>0){
           if($getuser_verify[0]['status']==1) $getishaveverify=1;
        }
        $userloanmoney=Db::name("loans")->where("user_id",$userid)->where('status',0)->sum("loan_amount");//待审核金额
        $userloanmoney1=Db::name("loans")->where("user_id",$userid)->where('status',1)->where('repayment_status',0)->sum("loan_amount");//待还款金额
        $getthisUser=Db::name("user")->where('id',$userid)->find();
        $gethaveloanmoney=$getthisUser['lonanmoney']-($userloanmoney+$userloanmoney1);
        $this->success('', [
            'userid'=>$userid,
            'getthisloans'=>$getthisloans,
            'daypercent'=>$daypercent,
            'getloanpick'=>$getloanlist,
            'getishaveverify'=>$getishaveverify,
            'gethaveloanmoney'=>$gethaveloanmoney,
        ]);
    }
    public function borrownow(Request $request)
    {
        $userid = $request->param("userid");
        $money = $request->param("money");
        $day = $request->param("day");
        $moneyp = $request->param("moneyp");
        $daypercent = $request->param("daypercent"); 
        Db::name("loans")->insert(['user_id'=>$userid,'loan_amount'=>$money,
        'interest_rate'=>$daypercent,'loan_term'=>$day, 'create_time'=>time(),
        'loan_interest'=>$moneyp
        ]);
        $getthisloans=Db::name("loans")->where("user_id",$userid)->select();   
        $this->success('', [
            'userid'=>$userid,
            'getthisloans'=>$getthisloans 
        ]);
    }
    public function logout(Request $request)
    {
        $token = $request->param("token");
        
        if (!$token) {
            return $this->error('Token required for logout');
        }
        
        // 清除token缓存
        $cacheKey = 'user_token_' . $token;
        Cache::delete($cacheKey);
        
        return $this->success('success');
    }

    /**
     * Get user transaction history including recharge and withdrawal records
     */
    public function transactions(Request $request)
    {
        // 获取用户ID
        $userid = $request->userId;
        
        if (empty($userid)) {
            $this->error('User ID is required');
        }
        
        $page = $request->param("page", 1);
        $limit = $request->param("limit", 10);
        $type = $request->param("type", ''); // 类型过滤：recharge, withdrawal, all(默认)
        
        // 起始记录
        $offset = ($page - 1) * $limit;
        
        // 准备结果数组
        $transactions = [];
        $total = 0;
        
        // 根据类型选择查询
        if ($type == 'recharge' || $type == '') {
            // 查询充值记录
            $rechargeRecords = Db::name("finance_recharge")
                ->field('id, create_time, target_num, status, "recharge" as type')
                ->where("user_id", $userid)
                ->select()
                ->toArray();
            
            $transactions = array_merge($transactions, $rechargeRecords);
        }
        
        if ($type == 'withdrawal' || $type == '') {
            // 查询提现记录
            $withdrawalRecords = Db::name("finance_withdrawl")
                ->field('id, create_time, target_num, status, "withdrawal" as type')
                ->where("user_id", $userid)
                ->select()
                ->toArray();
            
            $transactions = array_merge($transactions, $withdrawalRecords);
        }
        
        // 按时间排序
        usort($transactions, function($a, $b) {
            return $b['create_time'] - $a['create_time']; // 降序排列
        });
        
        // 获取总记录数
        $total = count($transactions);
        
        // 分页处理
        $transactions = array_slice($transactions, $offset, $limit);
        
        // 格式化数据
        foreach ($transactions as &$record) {
            // 格式化时间
            $record['formatted_time'] = date('Y-m-d H:i:s', $record['create_time']);
            
            // 格式化状态
            switch ($record['status']) {
                case 0:
                    $record['status_text'] = '待审核';
                    break;
                case 1:
                    $record['status_text'] = '已完成';
                    break;
                case 2:
                    $record['status_text'] = '已拒绝';
                    break;
                default:
                    $record['status_text'] = '未知状态';
            }
            
            // 交易类型文本
            $record['type_text'] = $record['type'] == 'recharge' ? '充值' : '提现';
        }
        
        $this->success('', [
            'transactions' => $transactions,
            'total' => $total
        ]);
    }

    /**
     * 获取用户邀请码和邀请统计信息
     */
    public function invitecode(Request $request)
    {
        $userid = $request->userId; 
        $user = Db::name("user")->where("id", $userid)->find();
        
        if (!$user) {
            return $this->error('User not found');
        }
        
        // 如果用户没有邀请码，生成一个
        if (empty($user['invite_code'])) {
            $codeExists = true;
            $inviteCode = '';
            
            while ($codeExists) {
                $inviteCode = $this->generateRandomCode();
                $result = Db::name("user")->where("invite_code", $inviteCode)->find();
                $codeExists = ($result !== null);
            }
            
            // 更新用户邀请码
            Db::name("user")->where("id", $userid)->update(['invite_code' => $inviteCode]);
            $user['invite_code'] = $inviteCode;
        }
        
        // 获取邀请统计
        $invitedCount = Db::name("user")->where("reference", $userid)->count();
        $registeredCount = Db::name("user")->where("reference", $userid)->count(); // 这里可以根据实际需求设置注册条件
        
        // 获取累计奖励
        $totalReward = Db::name("user_money_log")
            ->where("user_id", $userid)
            ->where("business_type", "Referral Reward")
            ->sum("money"); 
        
        // 如果没有累计奖励记录，设为0
        $totalReward = $totalReward ?: 0;
        
        $inviteStats = [
            'invitedCount' => $invitedCount,
            'registeredCount' => $registeredCount,
            'totalReward' => $totalReward
        ];
        
        return $this->success('', [
            'invite_code' => $user['invite_code'],
            'invite_stats' => $inviteStats
        ]);
    }

    /**
     * 获取用户团队信息
     */
    public function teaminfo(Request $request)
    {
        $userid = $request->userId;
        
        // 验证用户是否存在
        $user = Db::name("user")->where("id", $userid)->find();
        if (!$user) {
            return $this->error('User not found');
        }
        
        // 获取日期筛选参数
        $startDate = $request->param('start_date');
        $endDate = $request->param('end_date');
        
        // 获取团队统计数据
        $teamStats = $this->getSimpleTeamStatistics($userid, $startDate, $endDate);
        
        // 获取团队成员列表（直接下级）
        $teamMembers = $this->getDirectTeamMembers($userid, $startDate, $endDate);
        
        return $this->success('', [
            'stats' => $teamStats,
            'members' => $teamMembers 
        ]);
    }

    /**
     * 获取简化的团队统计数据
     */
    private function getSimpleTeamStatistics($userId, $startDate = null, $endDate = null)
    {
        // 初始化查询
        $u = Db::name("user") 
            ->where("reference", $userId)->column("id");


        // 初始化查询
        $query = Db::name("user_money_log")
            ->where("user_id","in", $u)
            ->where("business_id", 'in', [3, 4]);
        
        // 添加日期过滤条件
        if ($startDate) {
            $query->where('create_time', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('create_time', '<=', $endDate);
        }
        
        $q=  $query->select();
        // 获取团队总收益
        $totalReward = $query->sum('money');
        
        // 如果没有收益记录，设为0
        $totalReward = $totalReward ?: 0;
        
        return [ 
            'u'=>$u,
            'totalReward' => number_format($totalReward, 2, '.', '')
        ];
    }

    /**
     * 获取直接团队成员列表
     */
    private function getDirectTeamMembers($userId, $startDate = null, $endDate = null)
    {
        // 初始化查询
        $query = Db::name("user")
            ->field('id, username, create_time as register_time, level')
            ->where("reference", $userId);
        
        // 添加日期过滤条件 (如果设置了日期筛选，根据注册时间筛选成员)
        if ($startDate) {
            $query->where('create_time', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('create_time', '<=', $endDate);
        }
        
        // 执行查询并排序
        $members = $query->order('create_time', 'desc')
            ->select()
            ->toArray();
        
        // 为每个成员添加盈利率信息
        foreach ($members as &$member) {
            // 初始化买入卖出查询
            $buyQuery = Db::name("user_money_log")
                ->where("user_id", $member['id'])
                ->where("business_id", 'in', [3,4]); 
            
            // 添加日期过滤条件到交易记录查询
            if ($startDate) {
                $buyQuery->where('create_time', '>=', $startDate); 
            }
            
            if ($endDate) {
                $buyQuery->where('create_time', '<=', $endDate); 
            }
            
            // 获取所有买入交易记录总金额
            $buyTransactions = $buyQuery->sum('money');
            $totalBuy =  $buyTransactions ; // 买入为负数，需要取绝对值 
            
            // 计算收益率和盈利金额
            $profitRate = 0;
            $profitAmount = $totalBuy;
             
            // 添加到结果中
            $member['total_buy'] = 0;
            $member['total_sell'] = 0;
            $member['profit_amount'] = number_format($profitAmount, 2, '.', '');
            $member['profit_rate'] = 0;
        }
        
        return $members;
    }

    /**
     * 处理图片上传
     */
    public function uploadimg(Request $request)
    {
        // 使用公共的上传图片方法
        return $this->uploadImage($request);
    }

    /**
     * 获取用户余额信息
     * 返回真实余额(money)和模拟余额(virtualmoney)
     */
    public function balance(Request $request)
    {
        $userid = $request->userId;

        if (empty($userid)) {
            return $this->error('User ID is required');
        }

        // 查询用户余额信息
        $user = Db::name("user")
            ->field('id, money, virtualmoney')
            ->where("id", $userid)
            ->find();

        if (!$user) {
            return $this->error('User not found');
        }
 
        $money =$user['money'];
        $virtualmoney = $user['virtualmoney'];

        return $this->success('', [
            'money' => $money,
            'virtualmoney' => $virtualmoney,
            'money_raw' => $user['money'] ?? 0,
            'virtualmoney_raw' => $user['virtualmoney'] ?? 0
        ]);
    }

    /**
     * 初始化临时用户（游客模式）
     * 根据设备ID创建或获取临时用户，用于模拟交易
     */
    public function initGuest(Request $request)
    {
        $deviceId = $request->param("device_id");

        if (empty($deviceId)) {
            return $this->error('Device ID is required');
        }

        // 查找是否已存在该设备ID的临时用户
        $existingUser = Db::name("user")
            ->where("device_id", $deviceId)
            ->where("is_guest", 1)
            ->find();

        if ($existingUser) {
            // 已存在，返回用户信息
            $token = Random::uuid();

            // 保存token
            $this->tokenService->saveToken($token, $existingUser['id']);

            // 设置token缓存
            $keepTime = 86400 * 30; // 30天有效期
            $tokenData = [
                'user_id' => $existingUser['id'],
                'token' => $token,
                'create_time' => time(),
                'expire_time' => time() + $keepTime,
                'last_active_time' => time()
            ];
            Cache::set('user_token_' . $token, $tokenData, $keepTime);

            return $this->success('Guest user found', [
                'userid' => $existingUser['id'],
                'token' => $token,
                'virtualmoney' => $existingUser['virtualmoney'],
                'is_guest' => 1,
                'is_new' => false
            ]);
        }

        // 不存在，创建新的临时用户
        $salt = Random::build('alnum', 16);
        $guestUsername = 'guest_' . substr($deviceId, 0, 8) . '_' . time();
        $token = Random::uuid();

        // 生成唯一邀请码
        $inviteCode = '';
        $codeExists = true;
        while ($codeExists) {
            $inviteCode = $this->generateRandomCode();
            $result = Db::name("user")->where("invite_code", $inviteCode)->find();
            $codeExists = ($result !== null);
        }

        // 创建临时用户，默认10000虚拟余额
        $userId = UserModel::insertGetId([
            'username' => $guestUsername,
            'device_id' => $deviceId,
            'is_guest' => 1,
            'invite_code' => $inviteCode,
            'salt' => $salt,
            'password' => '',
            'password2' => '',
            'money' => 0,
            'virtualmoney' => 10000, // 默认10000虚拟余额
            'create_time' => time(),
            'level' => 1
        ]);

        // 保存token
        $this->tokenService->saveToken($token, $userId);

        // 设置token缓存
        $keepTime = 86400 * 30; // 30天有效期
        $tokenData = [
            'user_id' => $userId,
            'token' => $token,
            'create_time' => time(),
            'expire_time' => time() + $keepTime,
            'last_active_time' => time()
        ];
        Cache::set('user_token_' . $token, $tokenData, $keepTime);

        return $this->success('Guest user created', [
            'userid' => $userId,
            'token' => $token,
            'virtualmoney' => 10000,
            'is_guest' => 1,
            'is_new' => true
        ]);
    }

    /**
     * 获取临时用户余额
     */
    public function guestBalance(Request $request)
    {
        $deviceId = $request->param("device_id");

        if (empty($deviceId)) {
            return $this->error('Device ID is required');
        }

        $user = Db::name("user")
            ->where("device_id", $deviceId)
            ->where("is_guest", 1)
            ->find();

        if (!$user) {
            return $this->error('Guest user not found');
        }

        return $this->success('', [
            'userid' => $user['id'],
            'virtualmoney' => $user['virtualmoney'],
            'is_guest' => 1
        ]);
    }

    /**
     * 获取谷歌验证器状态
     */
    public function googleAuthStatus(Request $request)
    {
        $userid = $request->userId;

        $user = Db::name("user")->where("id", $userid)->find();
        if (!$user) {
            return $this->error('User not found');
        }

        // 检查是否已绑定
        $isBound = !empty($user['google_secret']) && $user['google_auth_enabled'] == 1;

        if ($isBound) {
            return $this->success('', [
                'is_bound' => true
            ]);
        }

        // 未绑定，生成新的密钥
        $secret = $this->generateGoogleAuthSecret();
        $appName = 'WebCoin';
        $userIdentifier = $user['email'] ?: $user['username'] ?: 'user_' . $userid;

        // 生成二维码URL
        $otpauthUrl = "otpauth://totp/{$appName}:{$userIdentifier}?secret={$secret}&issuer={$appName}";
        $qrcodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($otpauthUrl);

        return $this->success('', [
            'is_bound' => false,
            'secret_key' => $secret,
            'qrcode_url' => $qrcodeUrl
        ]);
    }

    /**
     * 绑定谷歌验证器
     */
    public function bindGoogleAuth(Request $request)
    {
        $userid = $request->userId;
        $code = $request->param('code', '');
        $secret = $request->param('secret', '');

        if (empty($code) || strlen($code) != 6) {
            return $this->error(__('Please enter 6-digit verification code'));
        }

        if (empty($secret)) {
            return $this->error(__('Secret key is required'));
        }

        $user = Db::name("user")->where("id", $userid)->find();
        if (!$user) {
            return $this->error('User not found');
        }

        // 检查是否已绑定
        if (!empty($user['google_secret']) && $user['google_auth_enabled'] == 1) {
            return $this->error(__('Google Authenticator already bound'));
        }

        // 验证验证码
        if (!$this->verifyGoogleAuthCode($secret, $code)) {
            return $this->error(__('Invalid verification code'));
        }

        // 保存密钥并启用
        Db::name("user")->where("id", $userid)->update([
            'google_secret' => $secret,
            'google_auth_enabled' => 1
        ]);

        return $this->success(__('Google Authenticator bound successfully'));
    }

    /**
     * 解绑谷歌验证器
     */
    public function unbindGoogleAuth(Request $request)
    {
        $userid = $request->userId;
        $code = $request->param('code', '');

        if (empty($code) || strlen($code) != 6) {
            return $this->error(__('Please enter 6-digit verification code'));
        }

        $user = Db::name("user")->where("id", $userid)->find();
        if (!$user) {
            return $this->error('User not found');
        }

        // 检查是否已绑定
        if (empty($user['google_secret']) || $user['google_auth_enabled'] != 1) {
            return $this->error(__('Google Authenticator not bound'));
        }

        // 验证验证码
        if (!$this->verifyGoogleAuthCode($user['google_secret'], $code)) {
            return $this->error(__('Invalid verification code'));
        }

        // 清除密钥并禁用
        Db::name("user")->where("id", $userid)->update([
            'google_secret' => '',
            'google_auth_enabled' => 0
        ]);

        return $this->success(__('Google Authenticator unbound successfully'));
    }

    /**
     * 生成谷歌验证器密钥
     */
    private function generateGoogleAuthSecret($length = 16)
    {
        $validChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $validChars[random_int(0, 31)];
        }
        return $secret;
    }

    /**
     * 验证谷歌验证码
     */
    private function verifyGoogleAuthCode($secret, $code, $discrepancy = 1)
    {
        $timeSlice = floor(time() / 30);

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = $this->getGoogleAuthCode($secret, $timeSlice + $i);
            if ($calculatedCode == $code) {
                return true;
            }
        }
        return false;
    }

    /**
     * 计算谷歌验证码
     */
    private function getGoogleAuthCode($secret, $timeSlice = null)
    {
        if ($timeSlice === null) {
            $timeSlice = floor(time() / 30);
        }

        // 将密钥从Base32解码
        $secretKey = $this->base32Decode($secret);

        // 将时间片打包为8字节
        $time = chr(0) . chr(0) . chr(0) . chr(0) . pack('N*', $timeSlice);

        // 计算HMAC-SHA1
        $hash = hash_hmac('SHA1', $time, $secretKey, true);

        // 获取偏移量
        $offset = ord(substr($hash, -1)) & 0x0F;

        // 获取4字节的代码
        $hashPart = substr($hash, $offset, 4);
        $value = unpack('N', $hashPart);
        $value = $value[1];
        $value = $value & 0x7FFFFFFF;

        $modulo = pow(10, 6);
        return str_pad($value % $modulo, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Base32解码
     */
    private function base32Decode($secret)
    {
        $base32chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $base32charsFlipped = array_flip(str_split($base32chars));

        $secret = strtoupper($secret);
        $secretLength = strlen($secret);
        $binaryString = '';

        for ($i = 0; $i < $secretLength; $i++) {
            if (!isset($base32charsFlipped[$secret[$i]])) {
                continue;
            }
            $binaryString .= str_pad(decbin($base32charsFlipped[$secret[$i]]), 5, '0', STR_PAD_LEFT);
        }

        $output = '';
        for ($i = 0; $i < strlen($binaryString); $i += 8) {
            $byte = substr($binaryString, $i, 8);
            if (strlen($byte) < 8) {
                break;
            }
            $output .= chr(bindec($byte));
        }

        return $output;
    }
}