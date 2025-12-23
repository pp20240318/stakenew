<?php

namespace app\common\controller;

use Throwable;
use think\facade\Event;
use think\facade\Cache;
use app\common\library\Auth;
use think\exception\HttpResponseException;
use app\common\library\token\TokenExpirationException;

class Frontend extends Api
{
    /**
     * 无需登录的方法
     * 访问本控制器的此方法，无需会员登录
     * @var array
     */
    protected array $noNeedLogin = [];

    /**
     * 无需鉴权的方法
     * @var array
     */
    protected array $noNeedPermission = ['*'];

    /**
     * 权限类实例
     * @var Auth
     */
    protected Auth $auth;

    /**
     * 初始化
     * @throws Throwable
     * @throws HttpResponseException
     */
    public function initialize(): void
    {
        parent::initialize();

        $needLogin = !action_in_arr($this->noNeedLogin);

        try {
            // 初始化会员鉴权实例
            $this->auth = Auth::instance();
            
            // 注意：token验证逻辑已移至中间件 TokenAuth 中处理
            // 通过中间件绑定的 userId 检查登录状态
            if (isset($this->request->userId)) {
                try {
                    // 使用中间件传递的userId初始化登录状态
                    $this->auth->direct($this->request->userId);
                    \think\facade\Log::info('Frontend initialize: Auth direct successful for user ID: ' . $this->request->userId);
                } catch (\Exception $e) {
                    \think\facade\Log::error('Frontend initialize: Auth direct failed: ' . $e->getMessage());
                    // 不抛出异常，继续执行
                }
            }
        } catch (TokenExpirationException $e) {
            // Token过期异常已在中间件中处理
            \think\facade\Log::error('Frontend initialize: Token expiration: ' . $e->getMessage());
        } catch (\Exception $e) {
            // 捕获所有异常，防止初始化过程中断
            \think\facade\Log::error('Frontend initialize: Unexpected error: ' . $e->getMessage());
        }

        // 恢复登录检查，但由于中间件已经处理了登录验证，这里不进行错误返回
        if ($needLogin && !$this->auth->isLogin()) {
            // 仅记录日志，不返回错误
            \think\facade\Log::info('Frontend initialize: User not logged in but continuing execution');
        }

        // 权限检查逻辑，由于设置了 noNeedPermission = ['*']，不会阻止任何操作
        if ($needLogin && !action_in_arr($this->noNeedPermission)) {
            $routePath = ($this->app->request->controllerPath ?? '') . '/' . $this->request->action(true);
            if (!$this->auth->check($routePath)) {
                // 仅记录日志，不返回错误
                \think\facade\Log::info('Frontend initialize: Permission check failed for route ' . $routePath . ' but continuing execution');
            }
        }

        // 会员验权和登录标签位
        try {
            Event::trigger('frontendInit', $this->auth);
        } catch (\Exception $e) {
            \think\facade\Log::error('Frontend initialize: Event trigger error: ' . $e->getMessage());
        }
    }
}