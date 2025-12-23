<?php
declare(strict_types=1);

namespace app\middleware;

use app\service\TokenService;
use think\Response;
use think\facade\Log;

class TokenAuth
{
    protected $tokenService;
    
    // 不需要验证token的接口白名单（小写格式，用于匹配）
    protected $noAuthEndpoints = [
        'user/login',
        'user/register',
        'user/forget',
        'user/sendcode',
        'user/checkcode',
        'user/guestbalance',           // 游客余额
        'user/initguest',              // 初始化游客用户
        'bot/index',
        'bot/status',
        'bot/transactions',
        'tradepair/list',              // 交易对列表（首页和交易页需要）
        'tradepair/detail',            // 交易对详情
        'tradeorder/placeorderguest',  // 游客下单
        'tradeorder/pendingordersguest', // 游客委托订单
        'tradeorder/historyordersguest', // 游客历史订单
        'tradeorder/checkorderstatus',   // 检查订单状态
        'notice/list',                 // 公告列表
        'notice/detail'                // 公告详情
    ];

    // 白名单路径，用于完整路径匹配（小写格式，用于匹配）
    protected $noAuthPaths = [
        'api/user/login',
        'api/user/register',
        'api/user/forget',
        'api/user/sendcode',
        'api/user/checkcode',
        'api/user/guestbalance',             // 游客余额
        'api/user/initguest',                // 初始化游客用户
        'api/bot/index',
        'api/bot/status',
        'api/bot/transactions',
        'api/tradepair/list',                // 交易对列表（首页和交易页需要）
        'api/tradepair/detail',              // 交易对详情
        'api/tradeorder/placeorderguest',    // 游客下单
        'api/tradeorder/pendingordersguest', // 游客委托订单
        'api/tradeorder/historyordersguest', // 游客历史订单
        'api/tradeorder/checkorderstatus',   // 检查订单状态
        'api/notice/list',                   // 公告列表
        'api/notice/detail'                  // 公告详情
    ];

    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    /**
     * 处理请求
     * @param \think\Request $request
     * @param \Closure $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        // 记录当前路由信息到日志
         
        
        // 获取请求路径
        $path = strtolower($request->pathinfo());
         
        
        // 尝试拆分路径获取控制器和方法
        $pathParts = explode('/', $path);
        $controller = isset($pathParts[1]) ? $pathParts[1] : '';
        $action = isset($pathParts[2]) ? $pathParts[2] : '';
        
        
        
        // 检查完整路径是否在白名单中
        $inPathWhitelist = in_array($path, $this->noAuthPaths); 
        
        // 检查控制器/方法是否在白名单中
        $endpoint = $controller . '/' . $action;
        $inEndpointWhitelist = in_array($endpoint, $this->noAuthEndpoints); 
        
        // 最终判断是否在白名单中
        $inWhitelist = $inPathWhitelist || $inEndpointWhitelist;
        
        // OPTIONS请求不需要验证token
        if ($request->method() == 'OPTIONS') { 
            return $next($request);
        }
        

        // 特殊处理登录接口
        if (strpos($path, 'admin')!==false || strpos($path, 'clickcaptcha')!==false ||strpos($path, 'user/login') !== false || strpos($path, 'api/user/login') !== false ||strpos($path, 'task/autotrade') !== false) {
            
            return $next($request);
        }
        
        // 标记是否需要检查token
        $needCheckToken = !$inWhitelist; 
        
        $token = $request->header('token'); 
        
        // 检查是否需要验证token
        if ($needCheckToken) {
            
            if (empty($token)) {
                // 特殊处理bot请求
                if ($controller == 'bot' && $request->has('userid')) {
                    $userid = $request->param('userid'); 
                    // 为请求添加userId参数
                    $request->userId = $userid; 
                } else { 
                    return json(['code' => 3, 'msg' => '请先登录']);
                }
            } else {
                // 从缓存中获取userid
                $userId = $this->tokenService->getUserIdByToken($token); 
                
                if (empty($userId)) { 
                    return json(['code' => 4, 'msg' => 'token已过期，请重新登录']);
                }

                // 刷新token过期时间
                $this->tokenService->refreshToken($token);

                // 将userId添加到请求对象中
                $request->userId = $userId; 
            }
        } else {
            // 即使在白名单中，也尝试获取用户ID
            if (!empty($token)) {
                $userId = $this->tokenService->getUserIdByToken($token);
                if (!empty($userId)) {
                    $request->userId = $userId; 
                    // 刷新token过期时间
                    $this->tokenService->refreshToken($token);
                }
            }
            
            // 如果没有token但有userid参数，也设置userId
            if (empty($request->userId) && $request->has('userid')) {
                $userid = $request->param('userid');
                $request->userId = $userid; 
            }
        }
         

        return $next($request);
    }
} 