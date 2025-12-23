<template>
	<view class="holdings-container">
		<!-- 顶部导航栏 -->
		<view class="navbar">
			<view  @tap="navigateBack">
				<image src="/static/images/icons/back-arrow.svg" class="back-icon"></image>
			</view>
			<text class="page-title">{{ $t('我的持仓') }}</text>
			<view class="navbar-right"></view>
		</view>
		
		<!-- 总持仓价值 -->
		<view class="total-value-card">
			<view class="total-title">{{ $t('总持仓价值') }} (USDT)</view>
			<view class="total-amount">{{ totalHolding }}</view>
		</view>
		
		<!-- 持仓列表 -->
		<view class="holdings-section">
			<view class="section-header">
				<text class="section-title">{{ $t('持有资产') }}</text>
			</view>
			
			<scroll-view 
				scroll-y="true" 
				class="holdings-list-scroll"
				refresher-enabled="true"
				:refresher-triggered="refreshing"
				@refresherrefresh="onRefresh"
			>
				<view class="holdings-list" v-if="holdings.length > 0">
					<view class="holding-item" v-for="(item, index) in holdings" :key="index" >
						<view class="holding-info">
							<text class="holding-symbol">{{ item.symbol }}</text>
							<text class="holding-amount">{{ item.amount }}</text>
						</view>
						<view class="holding-details">
							<text class="price-label">{{ $t('当前价格') }}</text>
							<text class="price-value">{{ item.price }} USDT</text>
							<text class="value-label">{{ $t('估值') }} (USDT)</text>
							<text class="value-amount">{{ item.value }}</text>
						</view>
					</view>
					
					<!-- 无数据状态 -->
					<view class="no-more" v-if="holdings.length === 0">
						<text class="no-more-text">{{ $t('暂无持仓资产') }}</text>
					</view>
				</view>
				
				<view class="empty-state" v-else>
					<text class="empty-text">{{ $t('暂无持仓数据') }}</text>
				</view>
			</scroll-view>
		</view>
	</view>
</template>

<script>
export default {
	data() {
		return {
			userId: 0,
			refreshing: false,
			totalHolding: '0.00',
			holdings: []
		}
	},
	onLoad() {
		// 获取用户ID
		this.userId = uni.getStorageSync('userid');
		
		// 初始化页面数据
		this.loadHoldingData();
	},
	onShow() {
		// 页面显示时重新获取数据
		this.loadHoldingData();
	},
	methods: {
		// 加载持仓数据
		loadHoldingData() {
			uni.showLoading({
				title: this.$t('加载中...')
			});
			
			this.req({
				url: "bot/holding",
				success: (res) => {
					if (res.code == 1) {
						this.totalHolding = res.data.totalHolding || '0.00';
						this.holdings = res.data.holdings || [];
					} else {
						uni.showToast({
							title: res.msg || this.$t('获取持仓数据失败'),
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
				}
			});
		},
		
		// 下拉刷新
		onRefresh() {
			this.refreshing = true;
			
			// 刷新持仓数据
			this.loadHoldingData();
			
			setTimeout(() => {
				this.refreshing = false;
				
				uni.showToast({
					title: this.$t('刷新成功'),
					icon: 'success'
				});
			}, 1000);
		},
		
		// 返回上一页
		navigateBack() {
			uni.navigateBack();
		},
		
		// 导航到资产详情页
		navigateToDetail(item) {
			uni.navigateTo({
				url: `/pages/market/detail?id=${item.id}`
			});
		}
	}
}
</script>

<style>
.holdings-container {
	background-color: #1A1A1A;
	min-height: 100vh;
}

/* 导航栏样式 */
.navbar {
	height: 90rpx;
	display: flex;
	align-items: center;
	justify-content: space-between;
	background-color: #1A1A1A;
	padding: 0 30rpx;
	box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.2);
}

.back-btn {
	width: 60rpx;
	height: 60rpx;
	display: flex;
	align-items: center;
	justify-content: center;
}

.back-icon {
	width: 40rpx;
	height: 40rpx;
}

.page-title {
	font-size: 36rpx;
	font-weight: bold;
	color: #FFFFFF;
	position: absolute;
	left: 50%;
	transform: translateX(-50%);
}

.navbar-right {
	width: 60rpx;
}

/* 总持仓价值卡片 */
.total-value-card {
	background-color: #007AFF;
	margin: 30rpx;
	padding: 40rpx;
	border-radius: 20rpx;
	color: #FFFFFF;
	box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.3);
}

.total-title {
	font-size: 28rpx;
	opacity: 0.8;
	margin-bottom: 10rpx;
}

.total-amount {
	font-size: 60rpx;
	font-weight: bold;
}

/* 持仓列表样式 */
.holdings-section {
	background-color: #2C2C2C;
	margin: 30rpx;
	padding: 30rpx;
	border-radius: 20rpx;
	height: calc(100vh - 340rpx);
	display: flex;
	flex-direction: column;
	box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.2);
}

.section-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding-bottom: 20rpx;
	border-bottom: 1rpx solid #3A3A3A;
	margin-bottom: 20rpx;
}

.section-title {
	font-size: 32rpx;
	font-weight: bold;
	color: #FFFFFF;
}

.holdings-list-scroll {
	flex: 1;
	height: 100%;
}

.holdings-list {
	display: flex;
	flex-direction: column;
}

.holding-item {
	display: flex;
	padding: 30rpx 0;
	border-bottom: 1rpx solid #3A3A3A;
}

.holding-item:last-child {
	border-bottom: none;
}

.holding-info {
	flex: 1;
	display: flex;
	flex-direction: column;
}

.holding-symbol {
	font-size: 36rpx;
	font-weight: bold;
	color: #FFFFFF;
	margin-bottom: 10rpx;
}

.holding-amount {
	font-size: 28rpx;
	color: #999999;
}

.holding-details {
	flex: 1;
	display: flex;
	flex-direction: column;
	align-items: flex-end;
}

.price-label, .value-label {
	font-size: 24rpx;
	color: #999999;
	margin-bottom: 4rpx;
}

.price-value {
	font-size: 28rpx;
	font-weight: 500;
	color: #FFFFFF;
	margin-bottom: 10rpx;
}

.value-amount {
	font-size: 32rpx;
	font-weight: bold;
	color: #34C759;
}

.empty-state {
	display: flex;
	justify-content: center;
	padding: 60rpx 0;
}

.empty-text, .no-more-text {
	font-size: 28rpx;
	color: #999999;
}

.no-more {
	display: flex;
	justify-content: center;
	padding: 30rpx 0;
}
/* 添加箭头图标样式 */
   .ri-arrow-left-line:before {
       content: "←";
       font-size: 36rpx;
       color: #FFFFFF;
   }
</style> 