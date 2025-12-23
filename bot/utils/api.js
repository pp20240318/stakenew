/**
 * API utility for CoinGecko API calls - Without CORS Proxies
 */

// Base URL for CoinGecko API
const BASE_URL = 'https://api.coingecko.com/api/v3';

// 添加缓存系统
const cache = {
  data: {},
  
  // 设置缓存项，带过期时间（默认5分钟）
  set(key, value, ttlMinutes = 5) {
    this.data[key] = {
      value,
      expiry: Date.now() + (ttlMinutes * 60 * 1000)
    };
    // 保存到本地存储(持久化缓存)
    try {
      uni.setStorageSync(`api_cache_${key}`, JSON.stringify({
        value,
        expiry: Date.now() + (ttlMinutes * 60 * 1000)
      }));
    } catch (e) {
      console.error('Failed to store cache in storage:', e);
    }
  },
  
  // 获取缓存项，如果过期则返回null
  get(key) {
    // 先检查内存缓存
    let item = this.data[key];
    
    // 如果内存没有，尝试从存储获取
    if (!item) {
      try {
        const storedItem = uni.getStorageSync(`api_cache_${key}`);
        if (storedItem) {
          item = JSON.parse(storedItem);
          this.data[key] = item; // 存回内存
        }
      } catch (e) {
        console.error('Failed to get cache from storage:', e);
      }
    }
    
    if (!item) return null;
    
    if (Date.now() > item.expiry) {
      this.clear(key);
      return null;
    }
    
    return item.value;
  },
  
  // 检查是否有缓存且未过期
  has(key) {
    return this.get(key) !== null;
  },
  
  // 清除单个缓存
  clear(key) {
    delete this.data[key];
    try {
      uni.removeStorageSync(`api_cache_${key}`);
    } catch (e) {
      console.error('Failed to remove cache from storage:', e);
    }
  },
  
  // 清除所有缓存
  clearAll() {
    this.data = {};
    try {
      const keys = uni.getStorageInfoSync().keys;
      keys.forEach(key => {
        if (key.startsWith('api_cache_')) {
          uni.removeStorageSync(key);
        }
      });
    } catch (e) {
      console.error('Failed to clear all cache from storage:', e);
    }
  }
};

// 跟踪API请求以避免超出频率限制
const requestTracker = {
  requests: {},
  lastRequestTime: 0,
  
  // 添加请求记录
  trackRequest(endpoint) {
    const now = Date.now();
    this.lastRequestTime = now;
    
    if (!this.requests[endpoint]) {
      this.requests[endpoint] = [];
    }
    
    // 记录当前请求时间
    this.requests[endpoint].push(now);
    
    // 只保留最近30秒的请求记录
    const thirtySecondsAgo = now - 30000;
    this.requests[endpoint] = this.requests[endpoint].filter(time => time > thirtySecondsAgo);
  },
  
  // 检查是否应该限制请求（30秒内最多10次请求同一端点）
  shouldThrottle(endpoint) {
    if (!this.requests[endpoint]) return false;
    
    return this.requests[endpoint].length >= 10;
  },
  
  // 获取应等待的毫秒数
  getWaitTime() {
    const timeSinceLastRequest = Date.now() - this.lastRequestTime;
    // 确保请求间隔至少300ms
    return Math.max(0, 300 - timeSinceLastRequest);
  }
};

/**
 * 发起API请求，带缓存和频率控制，无CORS代理
 * @param {string} endpoint - API端点
 * @param {Object} options - 请求选项
 * @param {number} cacheTtl - 缓存有效期（分钟，0表示不缓存）
 * @returns {Promise} 带响应数据的Promise
 */
const makeRequest = (endpoint, options = {}, cacheTtl = 5) => {
  return new Promise((resolve, reject) => {
    // 生成缓存键
    const cacheKey = `${endpoint}_${JSON.stringify(options)}`;
    
    // 1. 检查缓存
    if (cacheTtl > 0 && cache.has(cacheKey)) {
      console.log(`[API] 使用缓存数据: ${endpoint}`);
      return resolve(cache.get(cacheKey));
    }
    
    // 2. 检查频率限制
    if (requestTracker.shouldThrottle(endpoint)) {
      console.log(`[API] 达到频率限制: ${endpoint}`);
      
      // 如果有缓存数据（即使过期），返回它
      const expiredCache = cache.data[cacheKey];
      if (expiredCache) {
        console.log(`[API] 使用过期缓存: ${endpoint}`);
        return resolve(expiredCache.value);
      }
      
      // 否则返回错误
      return reject(new Error('API请求频率限制，请稍后再试'));
    }
    
    // 3. 等待一段时间以确保请求间隔
    setTimeout(() => {
      // 缓存破坏参数，避免浏览器缓存
      const cacheBuster = `${endpoint.includes('?') ? '&' : '?'}_t=${Date.now()}`;
      const url = BASE_URL + endpoint + cacheBuster;
      
      // 跟踪请求
      requestTracker.trackRequest(endpoint);
      
      // 发起请求 - 直接请求，不使用代理
      uni.request({
        url,
        method: options.method || 'GET',
        data: options.data || {},
        // 添加重试逻辑
        success: (res) => {
          if (res.statusCode >= 200 && res.statusCode < 300) {
            // 缓存成功的响应
            if (cacheTtl > 0) {
              cache.set(cacheKey, res.data, cacheTtl);
            }
            resolve(res.data);
          } else if (res.statusCode === 429) {
            // 达到API速率限制，请求过于频繁
            console.error('[API] 达到速率限制，使用缓存数据');
            
            // 有缓存就用缓存
            const expiredCache = cache.data[cacheKey];
            if (expiredCache) {
              console.log(`[API] 使用过期缓存(429错误): ${endpoint}`);
              resolve(expiredCache.value);
            } else {
              // 递归重试一次，较长延迟
              setTimeout(() => {
                makeRequest(endpoint, options, cacheTtl)
                  .then(resolve)
                  .catch(reject);
              }, 2000); // 等待2秒再重试
            }
          } else {
            console.error(`[API] 错误 ${res.statusCode}:`, res.data);
            
            // 尝试返回缓存数据
            const expiredCache = cache.data[cacheKey];
            if (expiredCache) {
              console.log(`[API] 使用过期缓存(错误响应): ${endpoint}`);
              resolve(expiredCache.value);
            } else {
              reject(new Error(`API请求失败，状态码: ${res.statusCode}`));
            }
          }
        },
        fail: (err) => {
          console.error('[API] 请求失败:', err);
          
          // 返回缓存数据（如果有）
          const expiredCache = cache.data[cacheKey];
          if (expiredCache) {
            console.log(`[API] 使用过期缓存(请求失败): ${endpoint}`);
            resolve(expiredCache.value);
          } else {
            reject(err);
          }
        }
      });
    }, requestTracker.getWaitTime());
  });
};

// 将CoinGecko API封装为对象
export default {
  /**
   * 获取加密货币市场数据
   * @param {Object} params - 查询参数
   * @param {number} cacheTtl - 缓存时间（分钟，默认5分钟）
   * @returns {Promise} Promise with coin market data
   */
  getCoinMarkets: (params = {}, cacheTtl = 5) => {
    const defaultParams = {
      vs_currency: 'usd',
      order: 'market_cap_desc',
      per_page: 100,
      page: 1,
      sparkline: false
    };
    
    const queryParams = { ...defaultParams, ...params };
    const queryString = Object.entries(queryParams)
      .map(([key, value]) => `${key}=${value}`)
      .join('&');
    
    return makeRequest(`/coins/markets?${queryString}`, {}, cacheTtl);
  },
  
  /**
   * 获取特定币种的详细数据
   * @param {string} id - 币种ID
   * @param {number} cacheTtl - 缓存时间（分钟，默认5分钟）
   * @returns {Promise} Promise with coin data
   */
  getCoin: (id, cacheTtl = 5) => {
    return makeRequest(`/coins/${id}?localization=false&tickers=false&market_data=true&community_data=false&developer_data=false&sparkline=false`, {}, cacheTtl);
  },
  
  /**
   * 获取币种价格图表数据
   * @param {string} id - 币种ID
   * @param {Object} params - 查询参数
   * @param {number} cacheTtl - 缓存时间（分钟，默认15分钟）
   * @returns {Promise} Promise with chart data
   */
  getCoinMarketChart: (id, params = {}, cacheTtl = 15) => {
    const defaultParams = {
      vs_currency: 'usd',
      days: '1'
    };
    
    const queryParams = { ...defaultParams, ...params };
    const queryString = Object.entries(queryParams)
      .map(([key, value]) => `${key}=${value}`)
      .join('&');
    
    // 图表数据可以缓存更长时间
    return makeRequest(`/coins/${id}/market_chart?${queryString}`, {}, cacheTtl);
  },
  
  /**
   * 获取全球市场数据
   * @param {number} cacheTtl - 缓存时间（分钟，默认10分钟）
   * @returns {Promise} Promise with global market data
   */
  getGlobalMarketData: (cacheTtl = 10) => {
    return makeRequest('/global', {}, cacheTtl);
  },
  
  /**
   * 获取多个币种的价格数据
   * @param {Array} ids - 币种ID数组
   * @param {Array} vs_currencies - 计价货币数组
   * @param {number} cacheTtl - 缓存时间（分钟，默认5分钟）
   * @returns {Promise} Promise with price data
   */
  getSimplePrice: (ids, vs_currencies = ['usd'], cacheTtl = 5) => {
    const idsStr = Array.isArray(ids) ? ids.join(',') : ids;
    const currenciesStr = Array.isArray(vs_currencies) ? vs_currencies.join(',') : vs_currencies;
    
    return makeRequest(`/simple/price?ids=${idsStr}&vs_currencies=${currenciesStr}`, {}, cacheTtl);
  },
  
  /**
   * 清除API缓存
   * @param {string} endpoint - 可选，特定端点的缓存键（不提供则清除所有）
   */
  clearCache: (endpoint) => {
    if (endpoint) {
      // 清除特定端点的缓存
      Object.keys(cache.data).forEach(key => {
        if (key.startsWith(endpoint)) {
          cache.clear(key);
        }
      });
    } else {
      // 清除所有缓存
      cache.clearAll();
    }
  },
  
  /**
   * 预加载常用数据
   * 在应用启动时调用此方法可以提前缓存常用数据
   */
  preloadCommonData: () => {
    // 预加载市场头部币种
    makeRequest('/coins/markets?vs_currency=usd&order=market_cap_desc&per_page=50&page=1&sparkline=false', {}, 10)
      .then(() => console.log('[API] 预加载市场数据成功'))
      .catch(() => console.log('[API] 预加载市场数据失败'));
    
    // 预加载全球市场数据
    makeRequest('/global', {}, 10)
      .then(() => console.log('[API] 预加载全球市场数据成功'))
      .catch(() => console.log('[API] 预加载全球市场数据失败'));
  }
}; 