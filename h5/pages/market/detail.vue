<template>
	<view class="coin-detail-container">
		<!-- 币种基本信息 -->
		<view class="coin-header">
			<view class="navbar">
				<view class="back-btn" @tap="$goBack()">
					<image src="/static/images/icons/back-arrow.svg" class="back-icon"></image>
				</view> 
			</view>
		
			
			<view class="coin-basic-info">
				<view class="coin-logo-container">
					<image 
						:src="getCoinImageUrl()" 
						class="coin-logo"
						@error="handleImageError"
					></image>
				</view>
				<view class="coin-name-info">
					<text class="coin-name">{{ coin.name || $t('未知币种') }}</text>
					<text class="coin-symbol">{{ coin.symbol ? coin.symbol.toUpperCase() : '' }}</text>
				</view>
			</view>
			
			<view class="coin-price-info">
				<text class="coin-price">${{ formatPrice(coin.current_price) }}</text>
				<text :class="['coin-change', coin.price_change_percentage_24h >= 0 ? 'increase' : 'decrease']">
					{{ coin.price_change_percentage_24h >= 0 ? '+' : '' }}{{ coin.price_change_percentage_24h ? coin.price_change_percentage_24h.toFixed(2) : '0.00' }}%
				</text>
			</view>
		</view>
		
		<!-- 图表区域 -->
		<view class="chart-container">
			<view class="chart-header">
				<text class="chart-title">{{ $t('价格走势') }}</text>
			</view>
			
			<!-- TradingView 图表 -->
			<view class="trading-view-wrapper">
				<view v-if="chartLoading" class="loading">
					<text class="loading-text">{{ $t('加载图表中...') }}</text>
				</view>
				<view v-else class="chart-content-wrapper">
					<!-- 尝试使用 iframe -->
					<view v-if="!showFallbackChart" class="iframe-container">
						<iframe 
							:src="tradingViewUrl" 
							class="trading-view-chart" 
							frameborder="0"
							allowtransparency="true"
							scrolling="no"
						></iframe>
					</view>
					
					<!-- 备用：静态图表显示 -->
					<view v-else class="fallback-chart">
						<view class="chart-info">
							<text class="chart-currency">{{ coin.symbol ? coin.symbol.toUpperCase() : '' }}/USD</text>
							<text class="chart-price">${{ formatPrice(coin.current_price) }}</text>
							<text :class="['chart-change', coin.price_change_percentage_24h >= 0 ? 'chart-positive' : 'chart-negative']">
								{{ coin.price_change_percentage_24h >= 0 ? '+' : '' }}{{ coin.price_change_percentage_24h ? coin.price_change_percentage_24h.toFixed(2) : '0.00' }}%
							</text>
						</view>
						
						<view class="chart-image">
							<image 
								:src="`https://www.coingecko.com/coins/${coinId}/sparkline`" 
								mode="aspectFit" 
								class="sparkline-image"
							/>
						</view>
					</view>
				</view>
			</view>
		</view>
		
		<!-- 市场数据 -->
		<view class="market-data">
			<view class="data-item">
				<text class="data-label">{{ $t('排名') }}</text>
				<text class="data-value">#{{ coin.market_cap_rank || '-' }}</text>
			</view>
			<view class="data-item">
				<text class="data-label">{{ $t('市值') }}</text>
				<text class="data-value">${{ formatLargeNumber(coin.market_cap) }}</text>
			</view>
			<view class="data-item">
				<text class="data-label">{{ $t('24h交易量') }}</text>
				<text class="data-value">${{ formatLargeNumber(coin.total_volume) }}</text>
			</view>
			<view class="data-item">
				<text class="data-label">{{ $t('流通数量') }}</text>
				<text class="data-value">{{ formatLargeNumber(coin.circulating_supply) }} {{ coin.symbol ? coin.symbol.toUpperCase() : '' }}</text>
			</view>
		</view>
		
		<!-- 高低价 -->
		<view class="price-range">
			<view class="range-header">
				<text class="range-title">{{ $t('24小时价格区间') }}</text>
			</view>
			<view class="range-content">
				<text class="low-price">${{ formatPrice(coin.low_24h) }}</text>
				<view class="range-bar">
					<view class="range-progress" :style="{
						width: calculateRangePercentage() + '%',
						backgroundColor: coin.price_change_percentage_24h >= 0 ? '#34C759' : '#FF3B30'
					}"></view>
					<view class="current-marker" :style="{
						left: calculateRangePercentage() + '%'
					}"></view>
				</view>
				<text class="high-price">${{ formatPrice(coin.high_24h) }}</text>
			</view>
		</view>
		
		<!-- 交易操作区域 -->
		<view class="trading-actions">
			<view class="action-header">
				<text class="action-title">{{ $t('买入/卖出') }}</text>
				<view class="holding-info"> 
					<text class="holding-label">{{ $t('持仓：') }}</text>
					<view class="holding-value-container">
						<text class="holding-value">{{formatPrice( holdingUsdtAmount) }} USDT</text>
							<view class="user-level" style="margin-left:10px;margin-bottom:10rpx">
	 
								<text class="refresh-icon ri-refresh-line" @tap.stop="loadHoldingAmount">{{ $t('Refresh') }}</text>
							</view>
					</view>
				</view>
			</view>
			
			<view class="amount-input-container">
				<text class="amount-label">{{ $t('交易金额 (USDT)') }}</text>
				<input 
					type="digit" 
					class="amount-input" 
					v-model="tradeAmount" 
					:placeholder="$t('输入交易金额')"
				/>
			</view>
			
			<view class="action-buttons">
				<button 
					class="buy-button" 
					@tap="executeBuy" 
					:disabled="isProcessing"
				>
					<text class="fa fa-arrow-circle-down button-icon"></text>
					{{ $t('买入') }}
				</button>
				<button 
					class="sell-button" 
					@tap="executeSell" 
					:disabled="isProcessing || holdingAmount <= 0"
				>
					<text class="fa fa-arrow-circle-up button-icon"></text>
					{{ $t('卖出') }}
				</button>
			</view>
		</view>
	</view>
</template>

<script>
// 引入API工具
import api from '@/utils/api.js';

export default {
	data() {
		return {
			coinId: '',
			coin: {},
			chartLoading: true,
			// 默认显示周期 - 仅用于备用图表和URL参数
			currentPeriod: '1D',
			tradingViewUrl: '',
			showFallbackChart: false, // 始终使用TradingView图表
			// 备用：添加图表数据
			chartData: [],
			
			// 交易相关
			tradeAmount: '', // 用户输入的交易金额
			holdingAmount: 0, // 持有的币种数量
			holdingUsdtAmount: 0, // 持有的USDT价值
			isProcessing: false, // 是否正在处理交易
			userId: 0 // 用户ID
		}
	},
	onLoad(options) {
		// 获取用户ID
		this.userId = uni.getStorageSync('userid');
		// 设置状态栏为暗色主题
		uni.setNavigationBarColor({
			frontColor: '#ffffff',
			backgroundColor: '#1A1A1A'
		});
		
		if (options.id) {
			this.coinId = options.id;
			this.loadCoinData();
			// 加载持仓数据
			this.loadHoldingAmount();
		} else {
			uni.showToast({
				title: this.$t('币种ID不存在'),
				icon: 'none'
			});
			setTimeout(() => {
				uni.navigateBack();
			}, 1500);
		} 
	},
	methods: {
		// 加载币种数据
		loadCoinData() {
			this.chartLoading = true;
			
			// 初始化默认值，防止undefined错误
			this.coin = {
				name: '',
				symbol: '',
				image: null,
				current_price: 0,
				price_change_percentage_24h: 0,
				market_cap: 0,
				total_volume: 0,
				high_24h: 0,
				low_24h: 0,
				circulating_supply: 0,
				total_supply: 0,
				market_cap_rank: null
			};
			
			// 使用较长的缓存时间，减少API请求
			api.getCoin(this.coinId, 10) // 缓存10分钟
				.then(data => {
					if (data) {
						console.log("API返回的币种数据:", data);
						
						// 正确提取图像 URL
						let imageUrl = null;
						if (data.image) {
							// API 可能返回对象形式的图像
							if (typeof data.image === 'object') {
								imageUrl = data.image.large || data.image.small || data.image.thumb;
							} else if (typeof data.image === 'string') {
								imageUrl = data.image;
							}
						}
						
						// 如果没有图像，使用 CoinGecko 默认图像
						if (!imageUrl && data.symbol) {
							imageUrl = `https://assets.coingecko.com/coins/images/1/large/${data.symbol.toLowerCase()}.png`;
						}
						
						this.coin = {
							...this.coin, // 保留默认值
							...data,
							image: imageUrl,
							current_price: data.market_data?.current_price?.usd || 0,
							price_change_percentage_24h: data.market_data?.price_change_percentage_24h || 0,
							market_cap: data.market_data?.market_cap?.usd || 0,
							total_volume: data.market_data?.total_volume?.usd || 0,
							high_24h: data.market_data?.high_24h?.usd || 0,
							low_24h: data.market_data?.low_24h?.usd || 0,
							circulating_supply: data.market_data?.circulating_supply || 0,
							total_supply: data.market_data?.total_supply || 0
						};
					}
					
					// 更新TradingView URL
					this.updateTradingViewUrl();
					this.chartLoading = false;
				})
				.catch(err => {
					console.error('Failed to load coin data:', err);
					uni.showToast({
						title: this.$t('加载币种数据失败'),
						icon: 'none'
					});
					this.chartLoading = false;
					
					// 显示备用图表以防主数据加载失败
					this.showFallbackChart = true;
				});
		},
		
		// 检查图像 URL 是否有效
		isValidImageUrl(url) {
			if (!url) return false;
			return (
				url.startsWith('http://') || 
				url.startsWith('https://') || 
				url.startsWith('/static/')
			);
		},
		
		// 加载持仓数量
		loadHoldingAmount() {
			if (!this.userId) return;
			
			this.req({
				url: "bot/holding",
				data: {
					coin_id: this.coinId,
					userid: this.userId
				},
				success: (res) => {
					if (res.code == 1 && res.data.holdings) {
						// 在数组中查找匹配当前币种symbol的持仓数据
						const currentHolding = res.data.holdings.find(item => 
							item.symbol.toUpperCase() === (this.coin.symbol ? this.coin.symbol.toUpperCase() : '')
						);
						
						// 如果找到匹配的持仓数据，更新持仓数量
						if (currentHolding) {
							this.holdingAmount = parseFloat(currentHolding.amount) || 0;
						} else {
							this.holdingAmount = 0;
						}
						
						// 计算USDT价值 
						this.holdingUsdtAmount = this.holdingAmount * this.coin.current_price;
					}
				}
			});
		},
		
		// 更新TradingView URL
		updateTradingViewUrl() {
			// 获取币种符号，TradingView通常使用COINBASE或BINANCE交易所数据
			const symbol = this.coin && this.coin.symbol ? this.coin.symbol.toUpperCase() : '';
			
			// 如果没有有效的币种符号，显示备用图表
			if (!symbol) {
				console.log(this.$t('没有有效的币种符号，使用备用图表'));
				this.chartLoading = false;
				this.showFallbackChart = true;
				return;
			}
			
			// 重置备用图表显示状态
			this.showFallbackChart = false;
			
			try {
				// 构建增强的TradingView图表URL - 使用暗色主题并改进兼容性
				// 对JSON参数进行正确编码
				const overrides = encodeURIComponent(JSON.stringify({
					"mainSeriesProperties.candleStyle.upColor": "#34C759",
					"mainSeriesProperties.candleStyle.downColor": "#FF3B30",
					"mainSeriesProperties.candleStyle.borderUpColor": "#34C759",
					"mainSeriesProperties.candleStyle.borderDownColor": "#FF3B30",
					"mainSeriesProperties.candleStyle.wickUpColor": "#34C759",
					"mainSeriesProperties.candleStyle.wickDownColor": "#FF3B30"
				}));
				
				const studiesOverrides = encodeURIComponent(JSON.stringify({}));
				
				this.tradingViewUrl = `https://s.tradingview.com/widgetembed/?symbol=${symbol}USD&interval=D&hidesidetoolbar=0&symboledit=1&saveimage=1&toolbarbg=2C2C2C&studies=[]&theme=dark&style=1&timezone=exchange&withdateranges=1&showpopupbutton=1&studies_overrides=${studiesOverrides}&overrides=${overrides}&enabled_features=[]&disabled_features=[]&locale=en&utm_source=localhost&utm_medium=widget&utm_campaign=chart&utm_term=${symbol}`;
				
				// 加载完成后检查图表是否显示
				setTimeout(() => {
					this.checkChartVisibility();
					this.chartLoading = false;
				}, 5000); // 增加等待时间到5秒，给TradingView更多加载时间
			} catch (error) {
				console.error('构建TradingView URL时出错:', error);
				this.chartLoading = false;
				this.showFallbackChart = true;
			}
		},
		
		// 检查图表是否可见
		checkChartVisibility() {
			// uni-app环境下不能直接访问iframe内容，采用超时和手动检测方法
			// 首先检查网络连接
			uni.getNetworkType({
				success: (res) => {
					if (res.networkType === 'none') {
						console.log(this.$t('没有网络连接，使用备用图表'));
						this.showFallbackChart = true;
						return;
					}
					
					// 默认显示TradingView图表
					this.showFallbackChart = false;
					
					// 添加错误处理，以防TradingView加载失败
					uni.onError((err) => {
						if (err.message && (
							err.message.includes('Trading') || 
							err.message.includes('iframe') ||
							err.message.includes('access denied')
						)) {
							console.error('TradingView加载错误:', err);
							this.showFallbackChart = true;
						}
					});
				},
				fail: () => {
					console.log(this.$t('无法获取网络状态，使用备用图表'));
					this.showFallbackChart = true;
				}
			});
		},
		
		// 备用：获取图表数据
		getChartData() {
			// 使用较长的缓存时间，减少API请求
			api.getCoinMarketChart(this.coinId, { days: '7' }, 30) // 缓存30分钟
				.then(data => {
					if (data && data.prices) {
						// 处理数据，最多保留100个点
						let prices = data.prices;
						if (prices.length > 100) {
							const step = Math.ceil(prices.length / 100);
							prices = prices.filter((_, index) => index % step === 0);
						}
						this.chartData = prices;
					}
				})
				.catch(err => {
					console.error('Failed to load chart data:', err);
				});
		},
		
		// 执行买入操作
		executeBuy() {
			if (!this.userId) {
				uni.showToast({
					title: this.$t('请先登录'),
					icon: 'none'
				});
				return;
			}
			
			// 验证交易金额
			const amount = parseFloat(this.tradeAmount);
			if (isNaN(amount) || amount <= 0) {
				uni.showToast({
					title: this.$t('请输入有效的交易金额'),
					icon: 'none'
				});
				return;
			}
			
			// 防止重复点击
			if (this.isProcessing) return;
			this.isProcessing = true;
			
			uni.showLoading({
				title: this.$t('处理中...')
			});
			
			this.req({
				url: "bot/buy",
				method: "POST",
				data: {
					coin_id: this.coinId,
					coin_symbol: this.coin.symbol,
					amount: amount,
					price: this.coin.current_price,
					userid: this.userId
				},
				success: (res) => {
					if (res.code == 1) {
						uni.showToast({
							title: this.$t('买入成功'),
							icon: 'success'
						});
						
						// 重新加载持仓数据
						this.loadHoldingAmount();
						
						// 清空输入框
						this.tradeAmount = '';
					} else {
						uni.showToast({
							title: res.msg || this.$t('买入失败'),
							icon: 'none'
						});
					}
				},
				fail: (err) => {
					uni.showToast({
						title: this.$t('网络请求失败，请重试'),
						icon: 'none'
					});
				},
				complete: () => {
					uni.hideLoading();
					this.isProcessing = false;
				}
			});
		},
		
		// 执行卖出操作
		executeSell() {
			if (!this.userId) {
				uni.showToast({
					title: this.$t('请先登录'),
					icon: 'none'
				});
				return;
			}
			
			// 检查是否有持仓
			if (this.holdingAmount <= 0) {
				uni.showToast({
					title: this.$t('没有可卖出的持仓'),
					icon: 'none'
				});
				return;
			}
			
			// 验证交易金额
			const amount = parseFloat(this.tradeAmount);
			if (isNaN(amount) || amount <= 0) {
				uni.showToast({
					title: this.$t('请输入有效的交易金额'),
					icon: 'none'
				});
				return;
			}
			
			// 检查是否超过持仓USDT量
			if (amount > this.holdingUsdtAmount) {
				uni.showToast({
					title: this.$t('不能超过持仓量'),
					icon: 'none'
				});
				return;
			}
			
			// 防止重复点击
			if (this.isProcessing) return;
			this.isProcessing = true;
			
			uni.showModal({
				title: this.$t('确认卖出'),
				content: `${this.$t('确定要卖出')} ${amount} USDT 吗？`,
				success: (res) => {
					if (res.confirm) {
						uni.showLoading({
							title: this.$t('处理中...')
						});
						
						this.req({
							url: "bot/sell",
							method: "POST",
							data: {
								coin_id: this.coinId,
								coin_symbol: this.coin.symbol,
								amount: amount,
								price: this.coin.current_price,
								userid: this.userId
							},
							success: (res) => {
								if (res.code == 1) {
									uni.showToast({
										title: this.$t('卖出成功'),
										icon: 'success'
									});
									
									// 重新加载持仓数据
									this.loadHoldingAmount();
									
									// 清空输入框
									this.tradeAmount = '';
								} else {
									uni.showToast({
										title: res.msg || this.$t('卖出失败'),
										icon: 'none'
									});
								}
							},
							fail: (err) => {
								uni.showToast({
									title: this.$t('网络请求失败，请重试'),
									icon: 'none'
								});
							},
							complete: () => {
								uni.hideLoading();
								this.isProcessing = false;
							}
						});
					} else {
						this.isProcessing = false;
					}
				}
			});
		},
		
		// 格式化价格
		formatPrice(price) {
			if (!price) return '0.00';
			
			// 针对不同价格范围使用不同的小数位数
			if (price < 0.01) {
				return price.toFixed(6);
			} else if (price < 1) {
				return price.toFixed(4);
			} else if (price < 10000) {
				return price.toFixed(2);
			} else {
				return Math.round(price).toLocaleString();
			}
		},
		
		// 格式化大数字（例如市值）
		formatLargeNumber(num) {
			if (!num) return '0';
			
			if (num >= 1000000000) {
				return (num / 1000000000).toFixed(2) + 'B';
			} else if (num >= 1000000) {
				return (num / 1000000).toFixed(2) + 'M';
			} else if (num >= 1000) {
				return (num / 1000).toFixed(2) + 'K';
			} else {
				return num.toFixed(2);
			}
		},
		
		// 计算价格区间百分比
		calculateRangePercentage() {
			if (!this.coin.low_24h || !this.coin.high_24h || !this.coin.current_price) return 50;
			
			const range = this.coin.high_24h - this.coin.low_24h;
			if (range === 0) return 50;
			
			const percentage = ((this.coin.current_price - this.coin.low_24h) / range) * 100;
			return Math.min(Math.max(percentage, 0), 100);
		},
		
		// 返回上一页
		goBack() {
			uni.navigateBack({
				delta: 1,
				fail: () => {
					// 如果返回失败，则尝试跳转到市场页
					uni.switchTab({
						url: '/pages/market/market'
					});
				}
			});
		},
		
		// 处理图像加载错误
		handleImageError() {
			console.error(this.$t('图像加载错误，尝试使用备用图标'));
			// 设置为一个通用的加密货币图标
			this.coin.image = 'https://cryptologos.cc/logos/question-mark.png';
		},
		
		// 获取币种图像URL
		getCoinImageUrl() {
			if (this.coin && this.coin.image) {
				// 检查 API 返回的图像 URL
				if (this.isValidImageUrl(this.coin.image)) {
					return this.coin.image;
				}
			}
			
			// 尝试使用 CoinIcons API
			if (this.coin && this.coin.symbol) {
				const symbol = this.coin.symbol.toLowerCase();
				// 返回 Cryptocompare 图标 API (常用且可靠)
				return `https://cryptocompare.com/media/37746238/${symbol}.png`;
			}
			
			// 尝试使用 CryptoIcons
			if (this.coin && this.coin.symbol) {
				const symbol = this.coin.symbol.toLowerCase();
				return `https://cryptoicons.org/api/icon/${symbol}/200`;
			}
			
			// 默认图标
			return 'https://cryptologos.cc/logos/question-mark.png';
		}
	}
}
</script>

<style>
.coin-detail-container {
	background-color: #1A1A1A;
	min-height: 100vh;
	padding: 0;
}

/* 币种基本信息 */
.coin-header {
	background-color: #2C2C2C;
	border-radius: 0 0 12rpx 12rpx;
	padding: 30rpx;
	margin-bottom: 20rpx;
	position: relative;
}
 

/* 添加箭头图标样式 */
.ri-arrow-left-line:before {
	content: "←";
	font-size: 36rpx;
	color: #FFFFFF;
}

.coin-basic-info {
	display: flex;
	align-items: center;
	margin-left: 60rpx;
	margin-bottom: 20rpx;
}

.coin-logo-container {
	width: 80rpx;
	height: 80rpx;
	border-radius: 40rpx;
	margin-right: 20rpx;
	overflow: hidden;
	background-color: #FFFFFF;
	display: flex;
	align-items: center;
	justify-content: center;
}

.coin-logo {
	width: 100%;
	height: 100%;
	object-fit: contain;
}

.coin-name-info {
	display: flex;
	flex-direction: column;
}

.coin-name {
	font-size: 32rpx;
	font-weight: bold;
	color: #FFFFFF;
}

.coin-symbol {
	font-size: 24rpx;
	color: #999999;
}

.coin-price-info {
	display: flex;
	flex-direction: column;
	align-items: flex-start;
	margin-left: 100rpx;
}

.coin-price {
	font-size: 40rpx;
	font-weight: bold;
	color: #FFFFFF;
	margin-bottom: 8rpx;
}

.coin-change {
	font-size: 24rpx;
	padding: 4rpx 12rpx;
	border-radius: 20rpx;
}

.increase {
	background-color: rgba(52, 199, 89, 0.1);
	color: #34C759;
}

.decrease {
	background-color: rgba(255, 59, 48, 0.1);
	color: #FF3B30;
}

/* 图表 */
.chart-container {
	background-color: #2C2C2C;
	border-radius: 12rpx;
	padding: 30rpx;
	margin: 0 0 20rpx 0; /* 移除左右边距 */
	display: flex;
	flex-direction: column;
}

.chart-header {
	margin-bottom: 15rpx;
	display: flex;
	justify-content: space-between;
	align-items: center;
}

.chart-title {
	font-size: 28rpx;
	font-weight: bold;
	color: #FFFFFF;
}

.trading-view-wrapper {
	position: relative;
	width: 100%;
	height: 500px;
	margin: 15rpx 0;
	overflow: hidden;
	background-color: #1A1A1A;
	border-radius: 8rpx;
	border: 1px solid #3A3A3A;
}

.chart-content-wrapper {
	width: 100%;
	height: 100%;
	position: relative;
}

.iframe-container {
	width: 100%;
	height: 100%;
	position: relative;
	overflow: hidden;
}

.trading-view-chart {
	width: 100%;
	height: 100%;
	border: none;
	margin: 0;
	padding: 0;
	display: block;
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
}

.loading {
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	background-color: rgba(0, 0, 0, 0.7);
}

.loading-text {
	font-size: 24rpx;
	color: #999999;
	margin-top: 10rpx;
}

/* 市场数据 */
.market-data {
	background-color: #2C2C2C;
	border-radius: 12rpx;
	padding: 30rpx;
	margin-bottom: 20rpx;
	display: flex;
	flex-wrap: wrap;
}

.data-item {
	width: 50%;
	margin-bottom: 20rpx;
}

.data-label {
	font-size: 24rpx;
	color: #999999;
	margin-bottom: 6rpx;
	display: block;
}

.data-value {
	font-size: 28rpx;
	color: #FFFFFF;
	font-weight: bold;
}

/* 价格区间 */
.price-range {
	background-color: #2C2C2C;
	border-radius: 12rpx;
	padding: 30rpx;
	margin-bottom: 20rpx;
}

.range-header {
	margin-bottom: 20rpx;
}

.range-title {
	font-size: 28rpx;
	font-weight: bold;
	color: #FFFFFF;
}

.range-content {
	display: flex;
	align-items: center;
}

.low-price, .high-price {
	font-size: 24rpx;
	color: #FFFFFF;
	width: 120rpx;
}

.high-price {
	text-align: right;
}

.range-bar {
	flex: 1;
	height: 12rpx;
	background-color: #3A3A3A;
	border-radius: 6rpx;
	position: relative;
	margin: 0 20rpx;
}

.range-progress {
	position: absolute;
	height: 100%;
	left: 0;
	top: 0;
	border-radius: 6rpx;
}

.current-marker {
	position: absolute;
	width: 20rpx;
	height: 20rpx;
	background-color: #2C2C2C;
	border: 2rpx solid #FFFFFF;
	border-radius: 50%;
	top: 50%;
	transform: translate(-50%, -50%);
}

/* 交易操作区域 */
.trading-actions {
	background-color: #2C2C2C;
	border-radius: 12rpx;
	padding: 30rpx;
	margin-bottom: 30rpx;
}

.action-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 20rpx;
}

.action-title {
	font-size: 28rpx;
	font-weight: bold;
	color: #FFFFFF;
}

.holding-info {
	display: flex;
	align-items: center;
}

.holding-label {
	font-size: 24rpx;
	color: #999999;
}

.holding-value-container {
	display: flex;
	align-items: center;
}

.holding-value {
	font-size: 24rpx;
	color: #34C759;
	font-weight: bold;
}

.refresh-icon {
	margin-left: 10rpx;
	font-size: 24rpx;
	color: #999999;
	padding: 6rpx;
}

.refresh-icon:active {
	opacity: 0.7;
}

.amount-input-container {
	margin-bottom: 20rpx;
}

.amount-label {
	font-size: 24rpx;
	color: #999999;
	margin-bottom: 10rpx;
	display: block;
}

.amount-input {
	background-color: #3A3A3A;
	border-radius: 8rpx;
	padding: 15rpx;
	color: #FFFFFF;
	font-size: 28rpx;
}

.action-buttons {
	display: flex;
	justify-content: space-between;
}

.buy-button, .sell-button {
	flex: 1;
	height: 80rpx;
	border-radius: 8rpx;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 28rpx;
	font-weight: bold;
	margin: 0 10rpx;
	border: none;
}

.buy-button {
	background-color: #34C759;
	color: #FFFFFF;
}

.buy-button:active {
	background-color: #2FB350;
}

.buy-button:disabled {
	background-color: #2A9D47;
	opacity: 0.6;
}

.sell-button {
	background-color: #FF3B30;
	color: #FFFFFF;
}

.sell-button:active {
	background-color: #E0352C;
}

.sell-button:disabled {
	background-color: #CC2F26;
	opacity: 0.6;
}

.button-icon {
	margin-right: 10rpx;
}

/* 通用图标样式 */
.fa {
	font-family: 'FontAwesome';
}

.fallback-chart {
	width: 100%;
	height: 100%;
	display: flex;
	flex-direction: column;
	border: 1px solid #3A3A3A;
	border-radius: 8px;
	padding: 20rpx;
	background-color: #2C2C2C;
}

.chart-info {
	display: flex;
	flex-direction: column;
	margin-bottom: 20rpx;
}

.chart-currency {
	font-size: 24rpx;
	color: #999999;
}

.chart-price {
	font-size: 36rpx;
	font-weight: bold;
	color: #FFFFFF;
	margin: 10rpx 0;
}

.chart-change {
	font-size: 24rpx;
	padding: 4rpx 12rpx;
	border-radius: 20rpx;
	width: fit-content;
}

.chart-positive {
	background-color: rgba(52, 199, 89, 0.1);
	color: #34C759;
}

.chart-negative {
	background-color: rgba(255, 59, 48, 0.1);
	color: #FF3B30;
}

.chart-image {
	flex: 1;
	width: 100%;
	display: flex;
	align-items: center;
	justify-content: center;
}

.sparkline-image {
	width: 100%;
	height: 300rpx;
}
.user-level {
		display: inline-block;
		background-color: rgba(255, 255, 255, 0.2);
		padding: 4rpx 16rpx;
		border-radius: 20rpx;
	}
</style> 