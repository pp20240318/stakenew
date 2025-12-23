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

class Index extends Frontend
{
    protected array $noNeedLogin = ['index','abcc'];

    public function initialize(): void
    {
        parent::initialize();
    }

    /**
     * 前台和会员中心的初始化请求
     * @throws Throwable
     */
    public function index(Request $request)
    {
        $userid = $request->param("userid");
        $getthisday=strtotime(date('Y-m-d'));
        $getthisUser=array();
        $content_alter=array();
        if(!empty($userid)){
            $getthisUser=Db::name("user_money_log")->field("sum(money) as allmoney")->where("user_id",$userid)->whereIn('business_typeid',array(3,4,5))->select(); 
            $content_alter=Db::name("content_alter")->where("start_time",'<=',time())
            ->where("end_time",'>=',time())
            ->where("user_ids",'like','%,'.$userid.',%')
            ->where("show",1)->order('id','desc')->select(); 
        }
          
        $getmoney=0;
        if(count($getthisUser)>0) $getmoney=$getthisUser[0]['allmoney'];
        if(!$getmoney) $getmoney=0;
        $advert=Db::name("advert")->field("imgurl as img,title")->where("type",1)->order('sort','asc')->select();  
        
        $this->success('', [
            'money'=>$getmoney,
            'advert'=>$advert,
            'content_alter'=>$content_alter,
        ]);
    }
    public function abcc(Request $request)
    {
        $this->success('aaaa', [ 
        ]);
    }
}