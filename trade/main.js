import App from './App'

/* 全局挂载请求库 */
import GraceRequest from '@/Grace6/js/request.js'
uni.gRequest = GraceRequest;

import i18n from './locale/index.js'

// #ifndef VUE3
import Vue from 'vue'
Vue.config.productionTip = false
App.mpType = 'app'

const app = new Vue({
    ...App,
	i18n,
})
app.$mount()
// #endif

// #ifdef VUE3
import { createSSRApp } from 'vue'
 
export function createApp() {
  const app = createSSRApp(App)
  app.use(i18n)
  app.config.globalProperties.qiniuak="5rmkM6MaAmb4wR059a6c2Im4aH-wKH5b8XJ79yfB";
  app.config.globalProperties.qiniusk="nPty7qhXr0537OJKYij-2J3OJl_VoqbQEBucf0ER";
  
  app.config.globalProperties.siteBaseUrl = 'http://localhost:8088/api/';
  app.config.globalProperties.H5BaseUrl = 'http://localhost:8088/';  
//   app.config.globalProperties.siteBaseUrl = 'https://stake.bobloot.live/api/';
//    app.config.globalProperties.H5BaseUrl = 'https://stake.bobloot.live/';   
  app.config.globalProperties.$formatDate = function(timestamp, type = 1) {
      const date = new Date(timestamp * 1000); // 转换为毫秒并创建Date对象
      const year = date.getFullYear(); // 获取年份
      const month = String(date.getMonth() + 1).padStart(2, '0'); // 获取月份，并补零
      const day = String(date.getDate()).padStart(2, '0'); // 获取日期，并补零
  
      if (type === 2) {
          const hours = String(date.getHours()).padStart(2, '0'); // 获取时，并补零
          const minutes = String(date.getMinutes()).padStart(2, '0'); // 获取分，并补零
          const seconds = String(date.getSeconds()).padStart(2, '0'); // 获取秒，并补零
          return `${year}/${month}/${day} ${hours}:${minutes}:${seconds}`; // 年/月/日 时:分:秒格式
      } else {
          return `${year}/${month}/${day}`; // 年/月/日格式
      }
  };
  app.config.globalProperties.req = function(e) {
  	uni.getNetworkType({
  		success: function(e) {
  			"none" != e.networkType || uni.showToast({
  				title: "network error",
  				icon: "none"
  			})
  		}
  	});
  	var o = e.url,
  		i = e.method || "POST",
  		r = {},
  		a = e.data || {},
  		c = e.Loading || !1,	
  		s = uni.getStorageSync("token");
  	
	console.log("token",s);
	
	// 获取当前语言设置
	let currentLanguage = 'en'; // 默认语言
	try {
		// 尝试从存储中获取用户设置的语言
		const userLanguage = uni.getStorageSync("userlanguage") || 
							localStorage?.getItem("userlanguage") ||
							'en';
		currentLanguage = userLanguage;
	} catch (error) {
		console.log("获取语言设置失败，使用默认语言:", error);
	}
	
	// 不需要验证token的接口白名单
	const noAuthEndpoints = [
		'user/login',    // 登录
		'user/register', // 注册
		'user/forget',   // 忘记密码
		'user/sendcode', // 发送验证码
		'user/checkcode', // 验证验证码
		'tradePair/list' // 交易对列表（首页和交易页需要）
	];
	
	// 检查是否需要验证token
	const needAuth = !noAuthEndpoints.some(endpoint => o.startsWith(endpoint));
	
	// 如果需要验证token且token不存在
	if (needAuth && !s) {
		uni.showModal({
			title: 'Tip',
			content: this.$t('请先登录'),
			confirmText: this.$t('登录'), 
			cancelText: this.$t('取消'),
			success: (res) => {
				if (res.confirm) {
					uni.navigateTo({
						url: '/pages/login/index'
					});
				}else{
					uni.switchTab({
						url: '/pages/market/detail'
					});
				}
				
			}
		});
		return; 
	}
	
	var p = this.siteBaseUrl + o;
	i = i.toUpperCase();
	
	// 统一设置请求头
	r = {
		"content-type": i === "POST" ? "application/x-www-form-urlencoded" : "application/json",
		"Accept-Language": currentLanguage, // 添加语言头
		"X-Requested-Lang": currentLanguage // 自定义语言头，确保后端能获取到
	};
	
	// 如果需要验证token，添加到请求头
	if (needAuth && s) {
		r["token"] = s;
	}
	
	// 如果请求中传入了自定义header，则合并
	if (e.header) {
		r = { ...r, ...e.header };
	}
	
	c || uni.showLoading({
		title: "loading..."
	});
	
	uni.request({
		url: p,
		method: i,
		header: r,
		data: a,
		success: function(t) {
			 
			if (t.data.code == 1) {
				// 如果返回数据中包含userid，自动保存
				if (t.data.data && t.data.data.userid) {
					uni.setStorageSync('userid', t.data.data.userid);
				}
				"function" == typeof e.success && e.success(t.data);
			} else if (t.data.code == 3 || t.data.code == 4) {
				// token失效，清除token和userid，并跳转到登录页
				uni.setStorageSync("token", "");
				uni.setStorageSync("userid", "");
				uni.navigateTo({
					url: "/pages/login/index"
				});
			} else {
				"function" == typeof e.success && e.success(t.data);
			}
		},
		fail: function(t) {
			console.log("fail:" + JSON.stringify(t));
			"function" == typeof e.fail && e.fail(t.data);
			// 只有需要认证的接口失败时才跳转登录页
			// 白名单接口失败不跳转
			if (needAuth) {
				uni.setStorageSync("token", "");
				uni.setStorageSync("userid", "");
				uni.navigateTo({
					url: "/pages/login/index"
				});
			}
		},
		complete: function() {
			uni.hideLoading();
			"function" == typeof e.complete && e.complete();
		}
	});
  }
 app.config.globalProperties.$toast = function(e) {
     e && uni.showToast({
         title: e,
         icon: 'none',
         duration: 2000
     })
 }
 app.config.globalProperties.$goBack = function() {
     uni.navigateTo({
         url: window.history.back(),
         fail: function(t) {
             
         }
     })
 }
 app.config.globalProperties.$gopage = function(e, params = {}) {
     e && uni.navigateTo({
         url: e,
         fail: function(t) {
             uni.switchTab({
                 url: e
             })
         }
     })
 }
  //app.mount('#app')
  return {
    app
  }
}
// #endif