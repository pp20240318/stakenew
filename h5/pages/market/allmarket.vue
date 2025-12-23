<template>
	<view class="all-market-container">
		<!-- 顶部导航栏 -->
		<view class="navbar">
			<text class="page-title">{{ $t('所有市场') }}</text>
		</view>
		
		<!-- 搜索框 -->
		<view class="search-container">
			<view class="search-input-container">
				<text class="fa fa-search search-icon"></text>
				<input 
					class="search-input" 
					type="text" 
					:placeholder="$t('搜索币种')" 
					v-model="searchQuery"
					confirm-type="search"
					@input="filterCoins"
				/>
				<text class="fa fa-times search-clear" v-if="searchQuery" @tap="clearSearch"></text>
			</view>
		</view>
		
		<!-- 币种列表 -->
		<view class="coins-section">
			<view class="coin-filter">
				<view class="sort-row">
					<view class="sort-item" @tap="sortBy('name')">
						<text class="sort-title">{{ $t('名称') }}</text>
						<text class="sort-arrow" v-if="sortField === 'name'">
							<text class="fa" :class="sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down'"></text>
						</text>
					</view>
					<view class="sort-item" @tap="sortBy('current_price')">
						<text class="sort-title">{{ $t('最新价') }}</text>
						<text class="sort-arrow" v-if="sortField === 'current_price'">
							<text class="fa" :class="sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down'"></text>
						</text>
					</view>
					<view class="sort-item" @tap="sortBy('price_change_percentage_24h')">
						<text class="sort-title">{{ $t('24h涨跌') }}</text>
						<text class="sort-arrow" v-if="sortField === 'price_change_percentage_24h'">
							<text class="fa" :class="sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down'"></text>
						</text>
					</view>
				</view>
				
				<scroll-view 
					class="coin-table" 
					scroll-y="true" 
					@scrolltolower="loadMoreCoins"
					refresher-enabled="true"
					:refresher-triggered="refreshing"
					@refresherrefresh="onPullDownRefresh"
				>
					<view class="coin-row" v-for="(coin, index) in displayedCoins" :key="index" @tap="navigateToCoin(coin)">
						<view class="coin-cell name-cell">
							<image :src="coin.image" class="small-icon"></image>
							<view class="coin-name-group">
								<text class="coin-full-name">{{ coin.name }}</text>
								<text class="coin-abbr">{{ coin.symbol.toUpperCase() }}</text>
							</view>
						</view>
						<view class="coin-cell price-cell">
							<text class="current-price">${{ formatPrice(coin.current_price) }}</text>
						</view>
						<view class="coin-cell change-cell">
							<text :class="['change-value', {
								'increase': coin.price_change_percentage_24h > 0,
								'decrease': coin.price_change_percentage_24h < 0
							}]">
								{{ formatChange(coin.price_change_percentage_24h) }}
							</text>
						</view>
					</view>
					
					<!-- 加载更多提示 -->
					<view class="loading-more" v-if="hasMoreCoins && !loading">
						<text class="loading-text">{{ $t('上拉加载更多') }}</text>
					</view>
					
					<!-- 无更多数据提示 -->
					<view class="no-more-data" v-if="!hasMoreCoins && allCoins.length > 0 && !loading">
						<text class="no-more-text">{{ $t('已显示全部数据') }}</text>
					</view>
				</scroll-view>
			</view>
		</view>
		
		<!-- 加载中提示 -->
		<view class="loading-container" v-if="loading">
			<view class="loading-icon">
				<text class="fa fa-spinner fa-spin"></text>
			</view>
			<text class="loading-text">{{ $t('加载中...') }}</text>
		</view>
		
		<!-- API错误提示 -->
		<view class="error-container" v-if="apiError">
			<view class="error-icon">
				<text class="fa fa-exclamation-circle"></text>
			</view>
			<text class="error-title">{{ $t('获取数据失败') }}</text>
			<text class="error-text">{{ $t('无法从CoinGecko获取市场数据，请确认您的网络连接正常。如果频繁出现此问题，这可能是由于直接API访问受限。') }}</text>
			<button class="retry-button" @tap="refreshData">{{ $t('重试') }}</button>
		</view>
	</view>
</template>

<script>
	import api from '@/utils/api.js'; // 引入API工具

	export default {
		data() {
			return {
				loading: false,
				apiError: false,
				refreshing: false,
				// 排序选项
				sortField: 'market_cap_rank',
				sortDirection: 'asc',
				// 币种数据
				allCoins: [], // 所有币种
				displayedCoins: [], // 当前显示的币种
				page: 1,
				pageSize: 200,
				hasMoreCoins: true,
				// 搜索相关
				searchQuery: '',
				filteredCoins: [],
				// API分页参数
				totalPages: 6, // 假设有6页，每页50个，共300个币种
				currentApiPage: 1,
				allCoinsLoaded: false
			}
		},
		computed: {
			// 排序后的币种列表 (仅用于计算，不直接显示)
			sortedCoins() {
				// 使用搜索过滤后的结果或原始列表
				const coinsToSort = this.searchQuery ? this.filteredCoins : this.allCoins;
				
				// 排除USDT
				const filteredCoins = coinsToSort.filter(coin => 
					coin.symbol && coin.symbol.toLowerCase() !== 'usdt'
				);
				
				const result = [...filteredCoins];
				
				// 按字段排序
				result.sort((a, b) => {
					let aValue = a[this.sortField];
					let bValue = b[this.sortField];
					
					// 处理特殊字段
					if (this.sortField === 'name') {
						aValue = a.name.toLowerCase();
						bValue = b.name.toLowerCase();
						return this.sortDirection === 'asc' ? 
							aValue.localeCompare(bValue) : 
							bValue.localeCompare(aValue);
					}
					
					// 数值比较
					const comparison = (aValue || 0) - (bValue || 0);
					return this.sortDirection === 'asc' ? comparison : -comparison;
				});
				
				return result;
			}
		},
		onLoad() {
			this.refreshData();
			
			// 设置TabBar的暗色主题样式
			uni.setTabBarStyle({
				color: "#999999",
				selectedColor: "#007AFF",
				backgroundColor: "#2C2C2C",
				borderStyle: "black"
			});
		},
		methods: {
			// 获取币种列表数据
			getCoinList(page = 1) {
				this.loading = true;
				
				// 使用API工具获取币种数据，加入缓存时间参数
				return api.getCoinMarkets({
					vs_currency: 'usd',
					order: 'market_cap_desc',
					per_page: 200, // 每页50个
					page: page, // 指定页码
					sparkline: false,
					price_change_percentage: '24h'
				}, 5) // 使用5分钟缓存
				.then(data => {
					if (data && data.length > 0) {
						if (page === 1) {
							// 第一页数据，替换现有数据
							this.allCoins = data;
							this.filteredCoins = [...data];
							this.displayedCoins = data.slice(0, this.pageSize);
							this.hasMoreCoins = data.length >= this.pageSize;
						} else {
							// 追加数据
							this.allCoins = [...this.allCoins, ...data];
							this.filteredCoins = this.searchQuery ? 
								this.filterCoinsArray(this.allCoins) : 
								[...this.allCoins];
							
							this.updateDisplayedCoins();
						}
						
						// 判断是否还有更多数据
						this.hasMoreCoins = data.length > 0;
						
						// 如果数据少于50个，说明已经加载完所有数据
						if (data.length < 50) {
							this.allCoinsLoaded = true;
						}
					} else {
						// 处理空数据情况
						if (page === 1) {
							this.apiError = true;
							uni.showToast({
								title: this.$t('没有获取到市场数据'),
								icon: 'none'
							});
						} else {
							// 如果是加载更多时没有数据，说明已加载全部
							this.hasMoreCoins = false;
							this.allCoinsLoaded = true;
						}
					}
					return data;
				})
				.catch(err => {
					console.error('Failed to get coin list:', err);
					if (page === 1) {
						this.apiError = true;
						uni.showToast({
							title: this.$t('获取市场数据失败，请稍后重试'),
							icon: 'none'
						});
					}
					throw err; // 重新抛出错误以便Promise.all捕获
				})
				.finally(() => {
					this.loading = false;
					this.refreshing = false;
				});
			},
			
			// 加载更多币种
			loadMoreCoins() {
				if (this.loading || !this.hasMoreCoins) return;
				
				if (this.searchQuery) {
					// 如果正在搜索，从过滤结果中加载更多
					this.page++;
					this.updateDisplayedCoins();
				} else if (this.page * this.pageSize >= this.allCoins.length) {
					// 需要从API加载更多数据
					if (!this.allCoinsLoaded) {
						this.currentApiPage++;
						this.getCoinList(this.currentApiPage);
					}
				} else {
					// 从已加载的数据中显示更多
					this.page++;
					this.updateDisplayedCoins();
				}
			},
			
			// 更新显示的币种列表
			updateDisplayedCoins() {
				const coins = this.sortedCoins;
				const startIndex = 0;
				const endIndex = this.page * this.pageSize;
				
				this.displayedCoins = coins.slice(startIndex, endIndex);
				this.hasMoreCoins = endIndex < coins.length || !this.allCoinsLoaded;
			},
			
			// 刷新数据
			refreshData() {
				// 避免频繁刷新
				if (this.loading) return;
				
				// 重置变量
				this.apiError = false;
				this.page = 1;
				this.currentApiPage = 1;
				this.allCoinsLoaded = false;
				
				// 显示刷新提示
				uni.showLoading({
					title: this.$t('正在刷新...')
				});
				
				// 清除API缓存以获取最新数据
				api.clearCache('/coins/markets');
				
				// 获取数据
				this.getCoinList(1)
				.catch(err => {
					console.error('数据刷新失败:', err);
					this.apiError = true;
				})
				.finally(() => {
					setTimeout(() => {
						uni.hideLoading();
					}, 1000);
				});
			},
			
			// 改变排序方式
			sortBy(field) {
				if (this.sortField === field) {
					// 切换排序方向
					this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
				} else {
					// 设置新的排序字段
					this.sortField = field;
					// 默认排序方向
					this.sortDirection = field === 'name' ? 'asc' : 'desc';
				}
				
				// 重新计算显示的币种
				this.page = 1;
				this.updateDisplayedCoins();
			},
			
			// 过滤币种
			filterCoins() {
				// 重置分页
				this.page = 1;
				
				if (!this.searchQuery.trim()) {
					this.filteredCoins = [...this.allCoins];
					this.updateDisplayedCoins();
					return;
				}
				
				this.filteredCoins = this.filterCoinsArray(this.allCoins);
				this.updateDisplayedCoins();
			},
			
			// 过滤币种数组的辅助方法
			filterCoinsArray(coinsArray) {
				const query = this.searchQuery.toLowerCase().trim();
				return coinsArray.filter(coin => 
					coin.name.toLowerCase().includes(query) || 
					coin.symbol.toLowerCase().includes(query)
				);
			},
			
			// 清除搜索
			clearSearch() {
				this.searchQuery = '';
				this.filteredCoins = [...this.allCoins];
				this.page = 1;
				this.updateDisplayedCoins();
			},
			
			// 导航到币种详情
			navigateToCoin(coin) {
				uni.navigateTo({
					url: `/pages/market/detail?id=${coin.id}`
				});
			},
			
			// 格式化价格
			formatPrice(price) {
				if (price === undefined || price === null) return '0.00';
				
				if (Math.abs(price) < 0.01) {
					return parseFloat(price).toFixed(6);
				} else if (Math.abs(price) < 1) {
					return parseFloat(price).toFixed(4);
				} else if (Math.abs(price) < 10) {
					return parseFloat(price).toFixed(3);
				} else {
					return parseFloat(price).toFixed(2);
				}
			},
			
			// 格式化百分比变化
			formatChange(change) {
				if (change === undefined || change === null) return '0.00%';
				
				let symbol = change >= 0 ? '+' : '';
				return symbol + change.toFixed(2) + '%';
			},
			
			// 下拉刷新处理
			onPullDownRefresh() {
				this.refreshing = true;
				this.refreshData();
			}
		}
	}
</script>

<style>
.all-market-container {
	padding-bottom: 30rpx;
	background-color: #1A1A1A;
	min-height: 100vh;
}

/* 导航栏 */
.navbar {
	display: flex;
	align-items: center;
	justify-content: space-between;
	height: 90rpx;
	background-color: #2C2C2C;
	padding: 0 30rpx;
	box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.1);
}

.page-title {
	font-size: 36rpx;
	font-weight: bold;
	color: #FFFFFF;
}

/* 搜索框样式 */
.search-container {
	padding: 15rpx 30rpx;
	background-color: #2C2C2C;
	border-bottom: 1px solid #3A3A3A;
}

.search-input-container {
	position: relative;
	display: flex;
	align-items: center;
	background-color: #3A3A3A;
	border-radius: 8rpx;
	padding: 0 15rpx;
}

.search-icon {
	font-size: 28rpx;
	color: #999999;
	margin-right: 10rpx;
}

.search-input {
	flex: 1;
	height: 70rpx;
	font-size: 28rpx;
	color: #FFFFFF;
	background: transparent;
}

.search-clear {
	font-size: 28rpx;
	color: #999999;
	padding: 10rpx;
}

/* 币种列表 */
.coins-section {
	background-color: #2C2C2C;
	padding: 30rpx;
	margin: 20rpx;
	border-radius: 12rpx;
}

.coin-filter {
	margin-top: 20rpx;
}

.sort-row {
	display: flex;
	border-bottom: 1rpx solid #3A3A3A;
	padding-bottom: 15rpx;
	margin-bottom: 10rpx;
}

.sort-item {
	flex: 1;
	display: flex;
	align-items: center;
}

.sort-item:nth-child(1) {
	flex: 1.5;
}

.sort-title {
	font-size: 26rpx;
	color: #999999;
	margin-right: 6rpx;
}

.sort-arrow {
	font-size: 22rpx;
	color: #999999;
}

.coin-table {
	display: flex;
	flex-direction: column;
	height: calc(100vh - 400rpx); /* 高度自适应，留出顶部和底部的空间 */
}

.coin-row {
	display: flex;
	padding: 15rpx 0;
	border-bottom: 1rpx solid #3A3A3A;
	align-items: center;
}

.coin-row:last-child {
	border-bottom: none;
}

.coin-cell {
	flex: 1;
}

.name-cell {
	flex: 1.5;
	display: flex;
	align-items: center;
}

.small-icon {
	width: 40rpx;
	height: 40rpx;
	border-radius: 20rpx;
	margin-right: 10rpx;
}

.coin-name-group {
	display: flex;
	flex-direction: column;
}

.coin-full-name {
	font-size: 26rpx;
	color: #FFFFFF;
}

.coin-abbr {
	font-size: 22rpx;
	color: #999999;
}

.price-cell {
	text-align: center;
}

.current-price {
	font-size: 26rpx;
	font-weight: bold;
	color: #FFFFFF;
}

.change-cell {
	display: flex;
	justify-content: flex-end;
}

.change-value {
	font-size: 24rpx;
	padding: 4rpx 10rpx;
	border-radius: 10rpx;
}

.increase {
	background-color: rgba(52, 199, 89, 0.1);
	color: #34C759;
}

.decrease {
	background-color: rgba(255, 59, 48, 0.1);
	color: #FF3B30;
}

/* Loading状态 */
.loading-container {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: rgba(0, 0, 0, 0.8);
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	z-index: 9999;
}

.loading-icon {
	font-size: 60rpx;
	color: #007AFF;
	margin-bottom: 20rpx;
}

.loading-text {
	font-size: 28rpx;
	color: #999999;
}

/* FontAwesome图标 */
.fa {
	font-family: 'FontAwesome';
}

/* API错误提示 */
.error-container {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: rgba(0, 0, 0, 0.8);
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	z-index: 9999;
	padding: 20rpx;
}

.error-icon {
	font-size: 60rpx;
	color: #FF3B30;
	margin-bottom: 20rpx;
}

.error-title {
	font-size: 28rpx;
	font-weight: bold;
	color: #FFFFFF;
	margin-bottom: 10rpx;
}

.error-text {
	font-size: 24rpx;
	color: #999999;
	margin-bottom: 20rpx;
	text-align: center;
}

.retry-button {
	background-color: #007AFF;
	color: #FFFFFF;
	padding: 10rpx 20rpx;
	border-radius: 10rpx;
	font-size: 26rpx;
}

/* 加载更多与无数据提示 */
.loading-more, .no-more-data {
	text-align: center;
	padding: 30rpx 0;
}

.loading-text, .no-more-text {
	font-size: 24rpx;
	color: #999999;
}
</style> 