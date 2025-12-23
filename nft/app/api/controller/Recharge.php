<?php

namespace app\api\controller;

use Throwable;
use ba\Captcha;
use ba\Random;
use ba\ClickCaptcha;
use think\facade\Config;
use think\facade\Db;
use think\Request;
use app\common\facade\Token;
use app\admin\model\User as UserModel;
use app\common\controller\Frontend;
use app\api\validate\User as UserValidate;

class Recharge extends Frontend
{
    protected array $noNeedLogin = [ 'index','uploadimg','login','depositdetail','depositlist','withdrawal','withdrawalrecord','savewithdrawal'];

    protected array $noNeedPermission = ['index'];

    public function initialize(): void
    {
        parent::initialize(); 
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
    public function withdrawal(Request $request)
    {
        $userid = $request->param("userid");
        $getusers=Db::name("user")->where("id",$userid)->select();
        if(count($getusers)==0){
            $this->error('User not found');
        }
        $getusers=$getusers[0];
        $handling_fee = get_sys_config('handling_fee');
        $minwithmoney = get_sys_config('minwithmoney');
        $maxwithmoney = get_sys_config('maxwithmoney');
        if(!$handling_fee) $handling_fee=0;
        if(!$minwithmoney) $minwithmoney=0;
        if(!$maxwithmoney) $maxwithmoney=0; 
        $usdt_percent = 1; 
        $this->success('', [
            'handling_fee'=>$handling_fee ,
            'minwithmoney'=>$minwithmoney ,
            'maxwithmoney'=>$maxwithmoney ,
            'usermoney'=>$getusers['money'], 
            'usdt_percent'=>$usdt_percent, 
        ]);
    }
    public function savewithdrawal(Request $request)
    {
        $userid = $request->param("userid");
        $selecttype = $request->param("selecttype");
        $money = $request->param("money");
        $address = $request->param("address");
        $name = $request->param("name");
        $bankno = $request->param("bankno");
        $bankname = $request->param("bankname");
        $banktype = $request->param("banktype");
        $bankdname = $request->param("bankdname");
        $bankcode = $request->param("bankcode");
        $paypassword = $request->param("paypassword");
        $google_code = $request->param("google_code");
        $handling_fee = get_sys_config('handling_fee');
        $show_percent = $request->param("show_percent");
        if(!$handling_fee) $handling_fee=0;
        if(!$show_percent) $show_percent=1;

        $getusers=Db::name("user")->where("id",$userid)->select();
        if(count($getusers)==0){
            $this->error('User not found');
        }
        //最多只能同时有3条未审核提现记录
        $gethavewithdrawl=DB::name("finance_withdrawl")->where('user_id',$userid)->where('status','opt0')->count();
        if($gethavewithdrawl>=3){
            return $this->error("最多只能同时有3条未审核提现记录",[$gethavewithdrawl]);
        }
        $getusers=$getusers[0];
        if($getusers['password2'] != encrypt_password($paypassword, $getusers['salt'])) {
            return $this->error("payment password error");
        }

        // 验证谷歌验证码（如果已绑定）
        if (!empty($getusers['google_secret']) && $getusers['google_auth_enabled'] == 1) {
            if (empty($google_code) || strlen($google_code) != 6) {
                return $this->error(__('Please enter 6-digit verification code'));
            }
            if (!$this->verifyGoogleAuthCode($getusers['google_secret'], $google_code)) {
                return $this->error(__('Invalid verification code'));
            }
        }

        // 检查余额是否足够
        $withdrawAmount = $money * $show_percent;
        if($withdrawAmount > $getusers['money']) {
            return $this->error("余额不足");
        }

        if($selecttype=='2'){//bank
            // 扣除用户余额
            Db::name('user')->where('id', $userid)->update(['money' => $getusers['money'] - $withdrawAmount]);

            $res=DB::name("finance_withdrawl")
            ->insertGetId([
                'user_id'=>$userid,
                'exchange_rate'=>$show_percent,
                'target_currency'=>'USDT',
                'target_num'=>$money-($money*$handling_fee),
                'username'=>$getusers['username'],
                'status'=>'opt0',
                'name'=>$name,
                'bank_name'=>$bankname,
                'bank_account'=>$bankno,
                'legal_tender'=>$banktype,
                'branch'=>$bankdname,
                'memo'=>$bankcode,
                'create_time'=>time()
            ]);
        }else{
            // 扣除用户余额
            Db::name('user')->where('id', $userid)->update(['money' => $getusers['money'] - $withdrawAmount]);

            $res=DB::name("finance_withdrawl")
            ->insertGetId([
                'user_id'=>$userid,
                'exchange_rate'=>$show_percent,
                'target_currency'=>'USDT',
                'target_num'=>$money-($money*$handling_fee),
                'username'=>$getusers['username'],
                'deposit_address'=>$address,
                'status'=>'opt0',
                'create_time'=>time()
            ]);
        }
        if($res){
            $this->success('保存成功');
        }else{
            $this->error('保存失败');
        }
    }
    public function withdrawalrecord(Request $request)
    {
        $userid = $request->param("userid");
         
        $getusers=Db::name("user")->where("id",$userid)->select();
        if(count($getusers)==0){
            $this->error('User not found');
        }
        $res=DB::name("finance_withdrawl")->where("user_id",$userid)->select();
        $this->success('成功',['list'=>$res]);
        
    }
    public function index(Request $request)
    {
        $usdt_address = get_sys_config('usdt_address'); 
        $usdt_percent = 1; 
        $this->success('', [ 
            'usdt_address'=>$usdt_address, 
            'usdt_percent'=>$usdt_percent, 
        ]);
    }
    public function depositdetail(Request $request)
    {
        $userid = $request->userId; 
        $count = $request->param("count");
        $type = $request->param("type");
        $address = $request->param("address");
        $imgurl = $request->param("imgurl");
        $getusers=Db::name("user")->where("id",$userid)->select();
        if(count($getusers)==0){
            $this->error('User not found');
        }
        $thisrate="";
        $ethpercent = get_sys_config('eth_percent');
        $btcpercent = get_sys_config('btc_percent');
        $usdcpercent = get_sys_config('usdc_percent');
        if($type=='1'){
            $thisrate="1";
        }elseif($type=='2'){
            $thisrate=$btcpercent;
        }elseif($type=='5'){
            $thisrate=$usdcpercent;
        }elseif($type=='4'){
            $thisrate=$ethpercent; 
        }else $thisrate="1";
        $exchange_rate=$count*$thisrate;
        $res=DB::name("finance_recharge")->insertGetId(['user_id'=>$userid,'status'=>'0','username'=>$getusers[0]['username'],
        'source_num'=>$count,
        'source_currency'=>'USDT',
        'target_num'=>$exchange_rate,
        'target_currency'=>'USDT',
        'into_account'=>$exchange_rate,
        'exchange_rate'=>$thisrate,
        'payout_address'=>$address,
        'image'=>$imgurl,
        'create_time'=>time()]);
        if($res){
            $this->success('保存成功');
        }else{
            $this->error('保存失败'); 
        }
        
    }
    public function depositlist(Request $request)
    {
        $userid = $request->param("userid");
         
        $getusers=Db::name("user")->where("id",$userid)->select();
        if(count($getusers)==0){
            $this->error('User not found');
        }
        $res=DB::name("finance_recharge")->where("user_id",$userid)->select();
        $this->success('成功',['list'=>$res]);
       
        
    }
    public function uploadimg(Request $request)
    {
        // 使用公共的上传图片方法
        return $this->uploadImage($request);
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

        $secretKey = $this->base32Decode($secret);
        $time = chr(0) . chr(0) . chr(0) . chr(0) . pack('N*', $timeSlice);
        $hash = hash_hmac('SHA1', $time, $secretKey, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
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