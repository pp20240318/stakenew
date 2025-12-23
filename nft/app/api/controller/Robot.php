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

class Robot extends Frontend
{
    protected array $noNeedLogin = ['index','levellist','listdetail','buyproduct','myxbot'];

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
        $getlist=Db::name("nft_level")->select(); 
        $getmoney=0;
        $getsymoney=0;
        $getlockmoney=0;
        if(!empty($userid)){
            $todaytime=strtotime(date('Y-m-d')); 
            $getlog=Db::name("user_money_log")->field("sum(money) as allmoney")->where("create_time",'>=',$todaytime)->where("user_id",$userid)->whereIn('business_typeid',array(3))->select(); 
            if(count($getlog)>0) $getsymoney=$getlog[0]['allmoney']?$getlog[0]['allmoney']:0;
            $getthisUser=Db::name("user")->where("id",$userid)->find();  
            $getmoney=$getthisUser['money'];
            $getsumlockmoney=Db::name("nft_purchase")
            ->alias('a')
            ->leftjoin('nft_product b','b.id = a.product_id')
            ->field("sum(b.price) as lockmoney")
            ->where("user_id",$userid)
            ->where("a.status",1)->select(); 
            if(count($getsumlockmoney)>0) $getlockmoney=($getsumlockmoney[0]['lockmoney']?$getsumlockmoney[0]['lockmoney']:0);
        } 
        $this->success('', [
            'list'=>$getlist,
            'money'=>$getmoney,
            'symoney'=>$getsymoney,
            'getlockmoney'=>$getlockmoney
        ]);
    }
    public function levellist(Request $request)
    {
        $levelid = $request->param("levelid"); 
        $selectindex = $request->param("selectindex"); 
        $getlist=Db::name("nft_product")->where("level_id",$levelid)->where("status",1);
        if(!empty($selectindex)){
            if($selectindex==1) $getlist=$getlist->where("is_sale",1);
            else $getlist=$getlist->where("is_sale",0);
        }
        $getlist=$getlist->order('id','desc')->select(); 

        $this->success('查询成功', [ 'list'=>$getlist,
        ]);
    }
    public function listdetail(Request $request)
    {
        $userid = $request->param("userid");
        $productid = $request->param("productid");  
        $getlist=Db::name("nft_product")->where("id",$productid)->find(); 
        $getmoney=0;
        if(!empty($userid)){ 
            $getthisUser=Db::name("user")->where("id",$userid)->find();  
            $getmoney=$getthisUser['money'];
        } 
        $this->success('查询成功', [ 'list'=>$getlist,'getmoney'=>$getmoney
        ]);
    }
    public function buyproduct(Request $request)
    {
        $userid = $request->param("userid");
        $productid = $request->param("productid");  
        $getlist=Db::name("nft_product")->where("id",$productid)->find(); 
         
        if(!empty($userid)){ 
            $getthisUser=Db::name("user")->where("id",$userid)->find();  
            if($getlist['price']>$getthisUser['money']) $this->error('no enough money');
            $getcredit=$getlist['level_id'];
            
            $getnft=Db::name("nft_purchase")->where("user_id",$userid)->where("status",'1')->where("product_id",$productid)->count();   
            
            if($getnft>0) {
                $this->error("today you can't buy it again ",null,-1);
            }
            $getid=Db::name("nft_purchase")->insertGetId(['user_id'=>$userid,'reference'=>$getthisUser['reference'],'product_id'=>$productid,'status'=>'1','create_time'=>time(),'source'=>$getlist['level_id']."级"]);  
            Db::name("user_score_log")->insertGetId(['user_id'=>$userid,'score'=>$getcredit,'memo'=>'购买nft','before'=>$getthisUser['credit'],'after'=>$getthisUser['credit']+$getcredit,'create_time'=>time()]);  
            Db::name("user")->where("id",$userid)->update(['money'=>$getthisUser['money']-$getlist['price'],'credit'=>$getthisUser['credit']+$getcredit]);
            Db::name("user_money_log")->insertGetId(['user_id'=>$userid,'business_id'=>$productid,
            'business_typeid'=>'1','business_type'=>'购买NFT',
            'money'=>-$getlist['price'],'before'=>$getthisUser['money'],'after'=>$getthisUser['money']-$getlist['price'],
            'create_time'=>time(),'parent_id'=>$getid]); 
        } 
        $this->success('查询成功', [ 'list'=>$getlist ,'money'=>$getthisUser['money']-$getlist['price']
        ]);
    }
    public function myxbot(Request $request)
    {
        $userid = $request->param("userid"); 
        $page=$this->request->param('page',1);
        $limit=$this->request->param('limit',20); 
        $getlist=[];  
        $offset=($page-1)*$limit; 
        $totalcount=0;
        $getlastpage=1; 
        if(!empty($userid)){ 
            $getlist=Db::name("nft_purchase")
            ->alias('a')
            ->leftjoin('nft_product b','b.id = a.product_id')
            ->leftjoin('(select sum(money) as allmoney,parent_id from ba_user_money_log where business_typeid=3 and user_id='.$userid.' group by parent_id) c','c.parent_id = a.id')
            ->field("a.*,b.computer_name,b.image,b.price,b.earning_rate,ifnull(c.allmoney,0) as allmoney")
            ->where("user_id",$userid)->order('a.create_time','desc');  
            $totalcount=$getlist->count();
            $getlastpage=ceil($totalcount/20); 
            $getlist=$getlist->limit($offset,$limit)->select()->toArray(); 
        } 
        $this->success('查询成功', [ 'list'=>$getlist,'totalcount'=>$totalcount,'getlastpage'=>$getlastpage
        ]);
    }
}