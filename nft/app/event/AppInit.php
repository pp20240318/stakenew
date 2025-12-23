<?php
declare(strict_types=1);

namespace app\event;

use think\facade\Log;

class AppInit
{
    public function handle()
    {
        // 记录应用初始化事件
        // Log::info('=== 应用初始化事件触发，检查中间件注册 ===');
        // Log::info('中间件配置: ' . json_encode(config('middleware')));
    }
} 