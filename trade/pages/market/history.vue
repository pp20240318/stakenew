<template>
	<view class="history-container">
		<!-- 固定顶部区域 -->
		<view class="fixed-header">
			<!-- 顶部导航 -->
			<view class="nav-header">
				<image class="back-icon" src="/static/img/market/detail/backicon.png" @tap="goBack"></image>
				<text class="nav-title">{{ $t('订单记录') }}</text>
				<view class="nav-placeholder"></view>
			</view>

			<!-- Tab选项 -->
			<view class="tab-container">
				<view
					:class="['tab-item', activeTab === 'pending' ? 'tab-active' : '']"
					@tap="switchTab('pending')"
				>
					<text class="tab-text">{{ $t('活跃订单') }}</text>
				</view>
				<view
					:class="['tab-item', activeTab === 'history' ? 'tab-active' : '']"
					@tap="switchTab('history')"
				>
					<text class="tab-text">{{ $t('交易历史') }}</text>
				</view>
			</view>
		</view>

		<!-- 订单列表 -->
		<scroll-view
			class="order-list"
			scroll-y
			@scrolltolower="loadMore"
			:refresher-enabled="true"
			@refresherrefresh="onRefresh"
			:refresher-triggered="isRefreshing"
		>
			<view v-if="orders.length === 0" class="empty-state">
				<text class="empty-text">{{ $t('暂无订单') }}</text>
			</view>

			<view v-for="(order, index) in orders" :key="index" class="order-item">
				<!-- 订单头部：币种和状态 -->
				<view class="order-header">
					<view class="order-coin">
						<text class="coin-symbol">{{ order.coin_symbol }}/USDT</text>
						<text :class="['trade-type', order.trade_type === 'up' ? 'type-up' : 'type-down']">
							{{ order.trade_type === 'up' ? $t('买涨') : $t('买跌') }}
						</text>
						<text :class="['money-type-tag', order.money_type == 1 ? 'money-real' : 'money-demo']">
							{{ order.money_type == 1 ? $t('真实') : $t('模拟') }}
						</text>
					</view>
					<text :class="['order-status', getStatusClass(order)]">
						{{ getStatusText(order) }}
					</text>
				</view>

				<!-- 订单详情 -->
				<view class="order-details">
					<view class="detail-row">
						<text class="detail-label">{{ $t('时间') }}</text>
						<text class="detail-value">{{ formatTime(order.created_at) }}</text>
					</view>
					<view class="detail-row">
						<text class="detail-label">{{ $t('交易时间') }}</text>
						<text class="detail-value">{{ order.trade_time }}</text>
					</view>
					<view class="detail-row">
						<text class="detail-label">{{ $t('金额') }}</text>
						<text class="detail-value">${{ parseFloat(order.amount).toFixed(2) }}</text>
					</view>
					<view class="detail-row">
						<text class="detail-label">{{ $t('下单价格') }}</text>
						<text class="detail-value">${{ parseFloat(order.entry_price).toFixed(2) }}</text>
					</view>
					<view class="detail-row">
						<text class="detail-label">{{ $t('结束价格') }}</text>
						<text class="detail-value">{{ order.exit_price ? '$' + parseFloat(order.exit_price).toFixed(2) : '-' }}</text>
					</view>
					<view class="detail-row" v-if="order.profit_loss !== undefined && order.profit_loss !== null">
						<text class="detail-label">{{ $t('盈亏') }}</text>
						<text :class="['detail-value', parseFloat(order.profit_loss) > 0 ? 'profit-win' : (parseFloat(order.profit_loss) < 0 ? 'profit-lose' : '')]">
							{{ parseFloat(order.profit_loss) > 0 ? '+' : '' }}${{ parseFloat(order.profit_loss).toFixed(2) }}
						</text>
					</view>
				</view>

				<!-- 活跃订单显示剩余时间 -->
				<view v-if="activeTab === 'pending' && order.remaining_seconds > 0" class="order-countdown">
					<text class="countdown-label">{{ $t('剩余时间') }}</text>
					<text class="countdown-value">{{ formatCountdown(order.remaining_seconds) }}</text>
				</view>
			</view>

			<!-- 加载更多 -->
			<view v-if="activeTab === 'history' && hasMore" class="load-more">
				<text class="load-more-text">{{ isLoading ? $t('加载中...') : $t('上拉加载更多') }}</text>
			</view>
		</scroll-view>
	</view>
</template>

<script>
export default {
	data() {
		return {
			activeTab: 'pending', // pending: 活跃订单, history: 交易历史
			orders: [],
			isLoading: false,
			isRefreshing: false,
			page: 1,
			limit: 20,
			total: 0,
			hasMore: true,
			userId: '',
			countdownTimer: null,
			isRealTrade: true, // 是否真实交易
			deviceId: '' // 游客设备ID
		}
	},
	onLoad(options) {
		this.userId = uni.getStorageSync('userid');
		this.deviceId = uni.getStorageSync('device_id') || '';

		// 如果没有 device_id，生成一个
		if (!this.deviceId) {
			this.deviceId = 'guest_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
			uni.setStorageSync('device_id', this.deviceId);
		}

		// 从页面参数或缓存获取交易模式
		if (options && options.money_type) {
			this.isRealTrade = options.money_type == 1;
		} else {
			this.isRealTrade = uni.getStorageSync('trade_mode') !== 'demo';
		}
		this.loadOrders();
	},
	onUnload() {
		this.stopCountdown();
	},
	methods: {
		// 切换Tab
		switchTab(tab) {
			if (this.activeTab === tab) return;
			this.activeTab = tab;
			this.orders = [];
			this.page = 1;
			this.hasMore = true;
			this.loadOrders();
		},

		// 加载订单
		loadOrders() {
			if (this.isLoading) return;
			this.isLoading = true;

			const t = this;

			// 判断是否为游客模式（未登录且有 deviceId）
			const isGuestMode = !t.userId && t.deviceId;

			if (isGuestMode) {
				// 游客模式使用游客接口（只能看模拟交易数据）
				const guestUrl = t.activeTab === 'pending'
					? 'tradeOrder/pendingOrdersGuest'
					: 'tradeOrder/historyOrdersGuest';
				const guestData = t.activeTab === 'pending'
					? { device_id: t.deviceId }
					: { device_id: t.deviceId, page: t.page, limit: t.limit };

				uni.request({
					url: t.siteBaseUrl + guestUrl,
					method: 'POST',
					data: guestData,
					header: {
						'Content-Type': 'application/x-www-form-urlencoded'
					},
					success: function(res) {
						t.isLoading = false;
						t.isRefreshing = false;

						const result = res.data;
						if (result.code == 1 && result.data) {
							const newOrders = result.data.orders || [];

							if (t.activeTab === 'pending') {
								t.orders = newOrders;
								t.startCountdown();
							} else {
								if (t.page === 1) {
									t.orders = newOrders;
								} else {
									t.orders = [...t.orders, ...newOrders];
								}
								t.total = result.data.total || 0;
								t.hasMore = t.orders.length < t.total;
							}
						} else {
							// 游客没有订单也是正常情况，不需要报错
							t.orders = [];
							t.total = 0;
							t.hasMore = false;
						}
					},
					fail: function(err) {
						t.isLoading = false;
						t.isRefreshing = false;
						t.orders = [];
						console.error('获取游客订单失败:', err);
					}
				});
				return;
			}

			// 已登录用户使用正常接口，不传money_type获取所有订单
			const url = t.activeTab === 'pending' ? 'tradeOrder/pendingOrders' : 'tradeOrder/historyOrders';
			const data = t.activeTab === 'pending'
				? {}
				: { page: t.page, limit: t.limit };

			t.req({
				url: url,
				data: data,
				success: function(res) {
					t.isLoading = false;
					t.isRefreshing = false;

					if (res.code == 1 && res.data) {
						const newOrders = res.data.orders || [];

						if (t.activeTab === 'pending') {
							t.orders = newOrders;
							// 启动倒计时
							t.startCountdown();
						} else {
							if (t.page === 1) {
								t.orders = newOrders;
							} else {
								t.orders = [...t.orders, ...newOrders];
							}
							t.total = res.data.total || 0;
							t.hasMore = t.orders.length < t.total;
						}
					}
				},
				fail: function(err) {
					t.isLoading = false;
					t.isRefreshing = false;
					console.error('获取订单失败:', err);
				}
			});
		},

		// 下拉刷新
		onRefresh() {
			this.isRefreshing = true;
			this.page = 1;
			this.hasMore = true;
			this.loadOrders();
		},

		// 加载更多
		loadMore() {
			if (this.activeTab !== 'history' || !this.hasMore || this.isLoading) return;
			this.page++;
			this.loadOrders();
		},

		// 启动倒计时
		startCountdown() {
			this.stopCountdown();
			const t = this;
			t.countdownTimer = setInterval(() => {
				let hasActiveOrders = false;
				t.orders = t.orders.map(order => {
					if (order.remaining_seconds > 0) {
						order.remaining_seconds--;
						hasActiveOrders = true;
					}
					return order;
				});

				if (!hasActiveOrders) {
					t.stopCountdown();
				}
			}, 1000);
		},

		// 停止倒计时
		stopCountdown() {
			if (this.countdownTimer) {
				clearInterval(this.countdownTimer);
				this.countdownTimer = null;
			}
		},

		// 获取状态样式类
		getStatusClass(order) {
			if (this.activeTab === 'pending') {
				return 'status-pending';
			}
			switch (order.result) {
				case 'win':
					return 'status-win';
				case 'lose':
					return 'status-lose';
				case 'draw':
					return 'status-draw';
				default:
					return '';
			}
		},

		// 获取状态文本
		getStatusText(order) {
			if (this.activeTab === 'pending') {
				return this.$t('进行中');
			}
			if (order.status === 3) {
				return this.$t('已取消');
			}
			switch (order.result) {
				case 'win':
					return this.$t('WIN');
				case 'lose':
					return this.$t('LOSE');
				case 'draw':
					return this.$t('DRAW');
				default:
					return '-';
			}
		},

		// 格式化时间
		formatTime(timeStr) {
			if (!timeStr) return '-';
			const date = new Date(timeStr);
			const month = String(date.getMonth() + 1).padStart(2, '0');
			const day = String(date.getDate()).padStart(2, '0');
			const hours = String(date.getHours()).padStart(2, '0');
			const minutes = String(date.getMinutes()).padStart(2, '0');
			const seconds = String(date.getSeconds()).padStart(2, '0');
			return `${month}/${day} ${hours}:${minutes}:${seconds}`;
		},

		// 格式化价格
		formatPrice(price) {
			if (!price) return '0.00';
			const num = parseFloat(price);
			if (num < 0.01) {
				return num.toFixed(6);
			} else if (num < 1) {
				return num.toFixed(4);
			} else if (num < 10000) {
				return num.toFixed(2);
			} else {
				return Math.round(num).toLocaleString();
			}
		},

		// 格式化倒计时
		formatCountdown(seconds) {
			const mins = Math.floor(seconds / 60);
			const secs = seconds % 60;
			return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
		},

		// 返回上一页
		goBack() {
			uni.navigateBack({
				delta: 1,
				fail: () => {
					uni.switchTab({
						url: '/pages/market/market'
					});
				}
			});
		}
	}
}
</script>

<style>
.history-container {
	background-color: #0E0F0F;
	min-height: 100vh;
	display: flex;
	flex-direction: column;
}

/* 固定顶部区域 */
.fixed-header {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	z-index: 100;
	background-color: #1A1A1A;
}

/* 顶部导航 */
.nav-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 20rpx 30rpx;
	background-color: #1A1A1A;
}

.back-icon {
	width: 48rpx;
	height: 48rpx;
}

.nav-title {
	font-size: 36rpx;
	font-weight: bold;
	color: #FFFFFF;
}

.nav-placeholder {
	width: 48rpx;
}

/* Tab选项 */
.tab-container {
	display: flex;
	background-color: #1A1A1A;
	padding: 20rpx 30rpx;
	gap: 20rpx;
}

.tab-item {
	flex: 1;
	padding: 20rpx;
	text-align: center;
	border-radius: 12rpx;
	background-color: #2C2C2C;
	transition: all 0.3s;
}

.tab-active {
	background-color: #F9D54A;
}

.tab-text {
	font-size: 28rpx;
	color: #FFFFFF;
	font-weight: 500;
}

.tab-active .tab-text {
	color: #000000;
}

/* 订单列表 */
.order-list {
	flex: 1;
	padding: 20rpx;
	padding-top: 200rpx; /* 为固定头部留出空间 */
	height: 100vh;
	box-sizing: border-box;
}

.empty-state {
	display: flex;
	justify-content: center;
	align-items: center;
	padding: 100rpx 0;
}

.empty-text {
	font-size: 28rpx;
	color: #666;
}

/* 订单项 */
.order-item {
	background-color: #1A1A1A;
	border-radius: 16rpx;
	padding: 24rpx;
	margin-bottom: 20rpx;
	border: 1px solid #2C2C2C;
	box-sizing: border-box;
}

.order-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 20rpx;
	padding-bottom: 16rpx;
	border-bottom: 1px solid #2C2C2C;
}

.order-coin {
	display: flex;
	align-items: center;
	gap: 12rpx;
}

.coin-symbol {
	font-size: 30rpx;
	font-weight: 600;
	color: #FFFFFF;
}

.trade-type {
	font-size: 22rpx;
	padding: 6rpx 12rpx;
	border-radius: 6rpx;
}

.type-up {
	background-color: rgba(51, 212, 157, 0.2);
	color: #33D49D;
}

.type-down {
	background-color: rgba(255, 100, 100, 0.2);
	color: #FF6464;
}

.money-type-tag {
	font-size: 20rpx;
	padding: 4rpx 10rpx;
	border-radius: 6rpx;
}

.money-real {
	background-color: rgba(249, 213, 74, 0.2);
	color: #F9D54A;
}

.money-demo {
	background-color: rgba(146, 151, 158, 0.2);
	color: #92979E;
}

.order-status {
	font-size: 28rpx;
	font-weight: 600;
}

.status-pending {
	color: #F9D54A;
}

.status-win {
	color: #FF6464;
}

.status-lose {
	color: #33D49D;
}

.status-draw {
	color: #FFFFFF;
}

/* 订单详情 */
.order-details {
	display: flex;
	flex-direction: column;
	gap: 12rpx;
}

.detail-row {
	display: flex;
	justify-content: space-between;
	align-items: center;
}

.detail-label {
	font-size: 24rpx;
	color: #92979E;
}

.detail-value {
	font-size: 24rpx;
	color: #FFFFFF;
}

.profit-win {
	color: #FF6464;
}

.profit-lose {
	color: #33D49D;
}

/* 倒计时 */
.order-countdown {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-top: 16rpx;
	padding-top: 16rpx;
	border-top: 1px solid #2C2C2C;
}

.countdown-label {
	font-size: 24rpx;
	color: #92979E;
}

.countdown-value {
	font-size: 28rpx;
	font-weight: 600;
	color: #F9D54A;
}

/* 加载更多 */
.load-more {
	text-align: center;
	padding: 30rpx;
}

.load-more-text {
	font-size: 24rpx;
	color: #666;
}
</style>
