<script>
	export default {
		onLaunch: function() {
			// #ifdef APP-PLUS
			const domModule = uni.requireNativePlugin('dom')
			domModule.addRule('fontFace', {
				'fontFamily': "graceuiiconfont",
				'src': "url('https://at.alicdn.com/t/c/font_823462_whtuj4ktcl.ttf?t=1703075468532')"
			});
			console.log("字体加载成功");
			// #endif

			// #ifdef MP-WEIXIN
			wx.onNeedPrivacyAuthorization(function(e){
				console.log(e);
			})
			// #endif

			// 延迟初始化，确保i18n已经加载
			setTimeout(() => {
				this.initLanguage();
			}, 500);

			// 监听语言变化事件
			uni.$on('languageChanged', () => {
				console.log('收到语言变化事件');
				setTimeout(() => {
					this.initLanguage();
				}, 100);
			});
		},
		onShow: function(){
			// 每次显示时重新加载语言设置并更新tabBar
			console.log('App onShow 触发');
			setTimeout(() => {
				this.initLanguage();
			}, 200);
		},
		onHide: function(){},
		methods: {
			// 检查当前页面是否是 tabBar 页面
			isTabBarPage() {
				const pages = getCurrentPages();
				if (pages.length === 0) return false;

				const currentPage = pages[pages.length - 1];
				const currentRoute = currentPage.route;

				// 定义 tabBar 页面路径（从 pages.json 中获取）
				const tabBarPages = [
					'pages/home/index',
					'pages/market/detail',
					'pages/profile/profile'
				];

				const isTabBar = tabBarPages.includes(currentRoute);
				console.log('当前页面:', currentRoute, '是否为tabBar页面:', isTabBar);
				return isTabBar;
			},

			// 初始化语言并更新tabBar
			initLanguage() { 

				// 从存储中获取保存的语言
				let savedLanguage = uni.getStorageSync('userlanguage'); 

				// 如果没有保存的语言，使用默认值
				if (!savedLanguage) {
					savedLanguage = 'en'; 
				}

				// 设置i18n语言
				if (this.$i18n) {
					try {
						if (this.$i18n.global) {
							// Vue 3 Composition API 
							this.$i18n.global.locale.value = savedLanguage; 
						} else {
							// Vue 2 或 Vue 3 Legacy 
							this.$i18n.locale = savedLanguage; 
						}
					} catch (e) {
						console.error('设置语言失败:', e);
					}
				} else {
					console.error('$i18n 不可用！');
				}

				// 只在 tabBar 页面才更新 tabBar
				if (this.isTabBarPage()) { 
					// 延迟更新tabBar，确保语言切换完成
					setTimeout(() => {
						this.updateTabBarLanguage();
					}, 300);
				} else { 
				}
			},

			// 更新tabBar文字为当前语言
			updateTabBarLanguage() {
				console.log('===== 开始更新 tabBar =====');

				try {
					// 验证 $t 方法是否可用
					if (!this.$t) {
						console.error('$t 方法不可用！');
						return;
					}

					// 获取当前语言
					let currentLocale = 'en';
					if (this.$i18n) {
						if (this.$i18n.global) {
							currentLocale = this.$i18n.global.locale.value;
						} else {
							currentLocale = this.$i18n.locale;
						}
					}

					// 定义 tabBar 项
					const tabBarItems = [
						{ index: 0, key: 'tabBar_home' },
						{ index: 1, key: 'tabBar_trade' },
						{ index: 2, key: 'tabBar_wallet' }
					];

					// 更新每个 tabBar 项
					let successCount = 0;
					tabBarItems.forEach(item => {
						try {
							const text = this.$t(item.key);

							if (!text || text === item.key) {
								console.warn(`警告: ${item.key} 没有找到翻译！`);
							}

							uni.setTabBarItem({
								index: item.index,
								text: text,
								success: () => {
									successCount++;
								},
								fail: (err) => {
									console.error(`✗ tabBar[${item.index}] 更新失败:`, err);
								}
							});
						} catch (e) {
							console.error(`更新 tabBar[${item.index}] 时出错:`, e);
						}
					});



				} catch (e) {
					console.error('updateTabBarLanguage 执行失败:', e);
				}
			},

			// 生成或获取设备唯一ID
			getDeviceId() {
				let deviceId = uni.getStorageSync('device_id');
				if (!deviceId) {
					// 生成UUID
					deviceId = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
						const r = Math.random() * 16 | 0;
						const v = c === 'x' ? r : (r & 0x3 | 0x8);
						return v.toString(16);
					});
					uni.setStorageSync('device_id', deviceId);
					console.log('生成新的设备ID:', deviceId);
				}
				return deviceId;
			}
		}
	}
</script>
<!-- #ifndef APP-NVUE -->
<style lang="scss">
/* 加载框架核心样式 */
@import "./Grace6/css/grace.scss";
/* 加载深色模式适配样式 */
@import "./Grace6/css/graceDark.scss";
/* 加载自定义样式 */
@import "./custom/custom.scss";
page{background:#1a1a1a;}
</style>
<!-- #endif -->
<!-- #ifdef APP-NVUE -->
<style lang="scss">
/* 加载框架核心样式 */
@import "./Grace6/css/grace.scss";
/* 加载自定义样式 */
@import "./custom/custom.scss";
.gui-icons{font-family:graceuiiconfont; font-style:normal;}
</style>
<!-- #endif -->
