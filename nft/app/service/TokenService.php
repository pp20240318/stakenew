<?php
declare(strict_types=1);

namespace app\service;

use think\facade\Cache;
use think\facade\Log;
use think\facade\Config;

class TokenService
{
    private $tokenPrefix = 'user:token:';  // token key前缀
    private $tokenExpire = 7200;           // token过期时间，2小时
    
    public function __construct()
    {
        // 检查Redis配置
        $redisConfig = Config::get('cache.stores.redis'); 
        
        // 尝试连接Redis
        try {
            $testKey = 'test:redis:connection';
            $testValue = 'works-' . time();
            $result = Cache::store('redis')->set($testKey, $testValue, 60);
            $readValue = Cache::store('redis')->get($testKey);
            
            if ($result && $readValue === $testValue) { 
            } else { 
            }
        } catch (\Exception $e) {
            Log::error('TokenService - Redis connection test: EXCEPTION - ' . $e->getMessage());
        }
    }

    /**
     * 保存token和userid的映射关系
     * @param string $token
     * @param int $userId
     * @return bool
     */
    public function saveToken(string $token, int $userId): bool
    {
        try {
            $key = $this->tokenPrefix . $token;
            $result = Cache::store('redis')->set($key, $userId, $this->tokenExpire); 
            return $result;
        } catch (\Exception $e) {
            Log::error("TokenService - saveToken ERROR: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 根据token获取userid
     * @param string $token
     * @return mixed
     */
    public function getUserIdByToken(string $token)
    {
        try {
            $key = $this->tokenPrefix . $token;
            $userId = Cache::store('redis')->get($key); 
            return $userId;
        } catch (\Exception $e) {
            Log::error("TokenService - getUserIdByToken ERROR: " . $e->getMessage());
            return null;
        }
    }

    /**
     * 删除token
     * @param string $token
     * @return bool
     */
    public function deleteToken(string $token): bool
    {
        try {
            $key = $this->tokenPrefix . $token;
            $result = Cache::store('redis')->delete($key); 
            return $result;
        } catch (\Exception $e) {
            Log::error("TokenService - deleteToken ERROR: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 刷新token过期时间
     * @param string $token
     * @return bool
     */
    public function refreshToken(string $token): bool
    {
        try {
            $key = $this->tokenPrefix . $token;
            $userId = $this->getUserIdByToken($token);
            if ($userId) {
                $result = Cache::store('redis')->set($key, $userId, $this->tokenExpire); 
                return $result;
            } 
            return false;
        } catch (\Exception $e) {
            Log::error("TokenService - refreshToken ERROR: " . $e->getMessage());
            return false;
        }
    }
} 