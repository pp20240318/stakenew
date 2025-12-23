<template>
	<view class="coin-detail-container">
		<!-- 币种基本信息 -->
		<view class="coin-header">
			<view class="back-btn" @tap="goBack">
				<text class="fa fa-arrow-left"></text>
			</view>
			
			<view class="coin-basic-info">
				<image :src="coin.image" class="coin-logo"></image>
				<view class="coin-name-info">
					<text class="coin-name">{{ coin.name }}</text>
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
				<text class="chart-title">价格走势</text>
			</view>
			
			<!-- TradingView 图表 -->
			<view class="trading-view-wrapper">
				<view v-if="chartLoading" class="loading">
					<text class="loading-text">加载图表中...</text>
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
				<text class="data-label">排名</text>
				<text class="data-value">#{{ coin.market_cap_rank || '-' }}</text>
			</view>
			<view class="data-item">
				<text class="data-label">市值</text>
				<text class="data-value">${{ formatLargeNumber(coin.market_cap) }}</text>
			</view>
			<view class="data-item">
				<text class="data-label">24h交易量</text>
				<text class="data-value">${{ formatLargeNumber(coin.total_volume) }}</text>
			</view>
			<view class="data-item">
				<text class="data-label">流通数量</text>
				<text class="data-value">{{ formatLargeNumber(coin.circulating_supply) }} {{ coin.symbol ? coin.symbol.toUpperCase() : '' }}</text>
			</view>
		</view>
		
		<!-- 高低价 -->
		<view class="price-range">
			<view class="range-header">
				<text class="range-title">24小时价格区间</text>
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
			chartData: []
		}
	},
	onLoad(options) {
		if (options.id) {
			this.coinId = options.id;
			this.loadCoinData();
		} else {
			uni.showToast({
				title: '币种ID不存在',
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
			
			// 使用较长的缓存时间，减少API请求
			api.getCoin(this.coinId, 10) // 缓存10分钟
				.then(data => {
					this.coin = {
						...data,
						current_price: data.market_data?.current_price?.usd || 0,
						price_change_percentage_24h: data.market_data?.price_change_percentage_24h || 0,
						market_cap: data.market_data?.market_cap?.usd || 0,
						total_volume: data.market_data?.total_volume?.usd || 0,
						high_24h: data.market_data?.high_24h?.usd || 0,
						low_24h: data.market_data?.low_24h?.usd || 0,
						circulating_supply: data.market_data?.circulating_supply || 0,
						total_supply: data.market_data?.total_supply || 0
					};
					
					// 更新TradingView URL
					this.updateTradingViewUrl();
					this.chartLoading = false;
				})
				.catch(err => {
					console.error('Failed to load coin data:', err);
					uni.showToast({
						title: '加载币种数据失败',
						icon: 'none'
					});
					this.chartLoading = false;
					
					// 显示备用图表以防主数据加载失败
					this.showFallbackChart = true;
				});
		},
		
		// 更新TradingView URL
		updateTradingViewUrl() {
			// 获取币种符号，TradingView通常使用COINBASE或BINANCE交易所数据
			const symbol = this.coin.symbol ? this.coin.symbol.toUpperCase() : '';
			if (symbol) {
				// 重置备用图表显示状态
				this.showFallbackChart = false;
				
				// 构建增强的TradingView图表URL - 启用更多功能和控件
				this.tradingViewUrl = `https://s.tradingview.com/widgetembed/?symbol=BINANCE:${symbol}USD&interval=D&hidesidetoolbar=0&symboledit=1&saveimage=1&toolbarbg=f1f3f6&studies=[]&theme=light&style=1&timezone=exchange&withdateranges=1&showpopupbutton=1&studies_overrides={}&overrides={}&enabled_features=[]&disabled_features=[]&locale=zh_CN&utm_source=localhost&utm_medium=widget&utm_campaign=chart&utm_term=${symbol}`;
				
				// 加载完成后检查图表是否显示
				setTimeout(() => {
					this.checkChartVisibility();
					this.chartLoading = false;
				}, 5000); // 增加等待时间到5秒，给TradingView更多加载时间
			} else {
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
						console.log('没有网络连接，使用备用图表');
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
					console.log('无法获取网络状态，使用备用图表');
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
			// 使用switchTab直接返回到首页(交易所/市场页面)
			uni.switchTab({
				url: '/pages/market/market'
			});
		},
		
		// 跳转到创建机器人页面
		createBot() {
			uni.navigateTo({
				url: `/pages/strategy/create-strategy?coin=${this.coinId}&symbol=${this.coin.symbol}&price=${this.coin.current_price}`
			});
		},
	}
}
</script>

<style>
.coin-detail-container {
	padding: 30rpx;
	background-color: #F5F5F5;
	min-height: 100vh;
}

/* 币种基本信息 */
.coin-header {
	background-color: #FFFFFF;
	border-radius: 12rpx;
	padding: 30rpx;
	margin-bottom: 20rpx;
	position: relative;
}

.back-btn {
	position: absolute;
	top: 30rpx;
	left: 30rpx;
	width: 60rpx;
	height: 60rpx;
	display: flex;
	align-items: center;
	justify-content: center;
	background-color: rgba(0, 0, 0, 0.05);
	border-radius: 30rpx;
	z-index: 10;
}

.coin-basic-info {
	display: flex;
	align-items: center;
	margin-left: 60rpx;
	margin-bottom: 20rpx;
}

.coin-logo {
	width: 80rpx;
	height: 80rpx;
	border-radius: 40rpx;
	margin-right: 20rpx;
}

.coin-name-info {
	display: flex;
	flex-direction: column;
}

.coin-name {
	font-size: 32rpx;
	font-weight: bold;
	color: #333;
}

.coin-symbol {
	font-size: 24rpx;
	color: #999;
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
	color: #333;
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
	background-color: #FFFFFF;
	border-radius: 12rpx;
	padding: 30rpx;
	margin-bottom: 20rpx;
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
	color: #333;
}

.trading-view-wrapper {
	position: relative;
	width: 100%;
	height: 500px;  /* 增加默认高度 */
	margin: 15rpx 0;
	overflow: hidden;
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
	background-color: rgba(255, 255, 255, 0.7);
}

.loading-text {
	font-size: 24rpx;
	color: #666;
	margin-top: 10rpx;
}

/* 市场数据 */
.market-data {
	background-color: #FFFFFF;
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
	color: #999;
	margin-bottom: 6rpx;
	display: block;
}

.data-value {
	font-size: 28rpx;
	color: #333;
	font-weight: bold;
}

/* 价格区间 */
.price-range {
	background-color: #FFFFFF;
	border-radius: 12rpx;
	padding: 30rpx;
	margin-bottom: 120rpx; /* 增加底部边距，为固定按钮留出空间 */
}

.range-header {
	margin-bottom: 20rpx;
}

.range-title {
	font-size: 28rpx;
	font-weight: bold;
	color: #333;
}

.range-content {
	display: flex;
	align-items: center;
}

.low-price, .high-price {
	font-size: 24rpx;
	color: #333;
	width: 120rpx;
}

.high-price {
	text-align: right;
}

.range-bar {
	flex: 1;
	height: 12rpx;
	background-color: #F0F0F0;
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
	background-color: #FFFFFF;
	border: 2rpx solid #333;
	border-radius: 50%;
	top: 50%;
	transform: translate(-50%, -50%);
}

/* 创建量化机器人按钮 - 改为固定定位 */
.fixed-action-buttons {
	position: fixed;
	bottom: 30rpx;
	left: 0;
	right: 0;
	display: flex;
	justify-content: center;
	z-index: 100;
	padding: 0 30rpx;
}

.create-bot-btn {
	width: 90%;
	height: 80rpx;
	border-radius: 40rpx;
	font-size: 28rpx;
	display: flex;
	align-items: center;
	justify-content: center;
	font-weight: bold;
	background-color: #007AFF;
	color: #FFFFFF;
	/* 添加阴影使按钮更明显 */
	box-shadow: 0 4rpx 12rpx rgba(0, 122, 255, 0.3);
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
	border: 1px solid #f0f0f0;
	border-radius: 8px;
	padding: 20rpx;
	background-color: #fff;
}

.chart-info {
	display: flex;
	flex-direction: column;
	margin-bottom: 20rpx;
}

.chart-currency {
	font-size: 24rpx;
	color: #666;
}

.chart-price {
	font-size: 36rpx;
	font-weight: bold;
	color: #333;
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
</style> 