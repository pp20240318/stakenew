<template>
	<view class="bot-container">
		<!-- 顶部导航 -->
 
		<!-- 账户统计 -->
		<view class="account-stats" style="position:fixed; top:var(--window-top);background-color: #fff;z-index:100;width:100%;">
			<text class="navbar-title">{{ $t('量化机器人') }}</text>
			<view class="navbar-actions">
				<text class="fa fa-bell" @tap="showNotifications"></text>
			</view>
			<view class="stats-row">
				<view class="stat-card">
					<text class="stat-value">{{ balanceTotal }}</text>
					<text class="stat-label">{{ $t('账户总资产 (USDT)') }}</text>
				</view>
				<view class="stat-card">
					<text class="stat-value" :class="{ 'profit': profitTotal > 0, 'loss': profitTotal < 0 }">
						{{ profitTotal > 0 ? '+' : '' }}{{ profitTotal }}
					</text>
					<text class="stat-label">{{ $t('今日盈亏 (USDT)') }}</text>
				</view>
			</view>
		 
		</view>
		
		<!-- 交易记录列表 -->
		<view class="transaction-container">
			<view class="list-header">
				<text class="list-title">{{ $t('交易记录') }}</text>
				<view class="bot-status" :class="isRunning ? 'status-running' : 'status-stopped'">
					{{ isRunning ? $t('运行中') : $t('已停止') }}
				</view>
			</view>
			
			<!-- 交易记录表格  refresher-enabled="false"
				:refresher-triggered="refreshing"
				@refresherrefresh="onRefresh" -->
			<scroll-view 
				scroll-y="true" 
				class="transaction-list"
				
			>
				<view class="transaction-table">
					<view class="table-header">
						<view class="th date-col">{{ $t('日期') }}</view>
						<view class="th time-col">{{ $t('时间') }}</view>
						<view class="th operation-col">{{ $t('操作') }}</view>
						<view class="th amount-col">{{ $t('金额') }}</view>
						<view class="th profit-col">{{ $t('盈利率') }}</view>
					</view>
					
					<view v-if="transactions.length === 0" class="empty-container">
						<text class="fa fa-calculator empty-icon"></text>
						<text class="empty-text">{{ $t('暂无交易记录') }}</text>
						<text class="empty-tips">{{ $t("点击底部'启动'按钮开始交易") }}</text>
					</view>
					
					<view v-else class="table-body">
						<view 
							v-for="(transaction, index) in transactions" 
							:key="index"
							class="table-row"
						>
							<view class="td date-col">{{ transaction.date }}</view>
							<view class="td time-col">{{ transaction.time }}</view>
							<view class="td operation-col" :class="transaction.isBuy ? 'buy-operation' : 'sell-operation'">
								{{ transaction.operation }}
							</view>
							<view class="td amount-col">{{ transaction.amount }}</view>
							<view v-if="!transaction.isBuy" class="td profit-col profit">{{ transaction.profit_rate }}%</view>
							<view v-else class="td profit-col">-</view>
						</view>
					</view>
				</view>
			</scroll-view>
		</view>
		
		<!-- 底部操作按钮 -->
		<view class="bottom-actions">
			<view 
				class="action-button start-button" style="background-color: black;"
				@tap="gotrade"
			>
				<text class="fa fa-play action-icon"></text>
				<text class="action-text">{{ $t('授权交易所') }}</text>
			</view>
			<view 
				v-if="!isRunning" 
				class="action-button start-button"
				@tap="startBot"
			>
				<text class="fa fa-play action-icon"></text>
				<text class="action-text">{{ $t('启动') }}</text>
			</view>
			<view 
				v-if="isRunning" 
				class="action-button stop-button" 
			>
				<text class="fa fa-stop action-icon"></text>
				<text class="action-text">{{ $t('停止') }}</text>
			</view>
		</view>
	
	</view>
</template>

<script>
	export default {
		data() {
			return {
				isRunning: false,
				refreshing: false,
				balanceTotal: '0.00',
				profitTotal: '0.00',
				transactionTimer: null,
				lastOperationBuy: false,
				currentAmount: 30,
				userId: 0,
				
				// 交易记录
				transactions: [],
				
				// 机器人相关数据
				runningBots: 0,
				totalBots: 0
			}
		},
		onLoad() {
			// 获取用户ID
			this.userId = uni.getStorageSync('userid');
			
			// 初始化页面
			this.initPage();
		    this.loadInviteCode();
		},
		onShow() {
			// 页面显示时重新获取数据
			this.initPage();
		},
		onUnload() {
			// 清除定时器
			this.clearTransactionTimer();
		},
		methods: {
			// 初始化页面
			initPage() {
				// 获取机器人状态
				this.loadBotStatus();
				
				// 获取统计数据
				this.loadStats();
				
				// 获取交易记录
				this.loadTransactions();
				
			},
			loadInviteCode() {
				this.req({
					url: "user/invitecode",  
					success: (res) => {
						if (res.code == 1) { 
							this.referralCode = res.data.invite_code || '';  
						} else {
							uni.showToast({
								title: res.msg || this.$t('获取邀请码失败'),
								icon: 'none'
							});
						}
					},
					fail: (err) => {
						uni.showToast({
							title: this.$t('网络请求失败，请重试'),
							icon: 'none'
						});
					}
				});
			},
			// 加载机器人状态
			loadBotStatus() {
				this.req({
					url: "bot/status",
					success: (res) => {
						if (res.code == 1) {
							this.isRunning = res.data.isRunning;
							this.lastOperationBuy = res.data.lastOperationBuy;
							this.currentAmount = res.data.currentAmount;
						 
						}
					}
				});
			},
			
			// 加载统计数据
			loadStats() {
				this.req({
					url: "bot/index",
					success: (res) => {
						if (res.code == 1) {
							this.balanceTotal = res.data.balanceTotal;
							this.profitTotal = res.data.profitTotal;
							this.runningBots = res.data.runningBots;
							this.totalBots = res.data.totalBots;
						}
					}
				});
			},
			
			// 加载交易记录
			loadTransactions() {
				this.req({
					url: "bot/transactions",
					success: (res) => {
						if (res.code == 1) {
							this.transactions = res.data.transactions || [];
						}
					}
				});
			},
			gotrade(){ 
				var t= this;
				uni.showLoading({
					title: this.$t('正在创建交易所账号')
				});
				uni.showModal({
					title: this.$t('恭喜您'),
					content: this .$t('自动创建交易所账号与机器人账号密码一致。'), 
					confirmText: this.$t('确认'),
					cancelText: this.$t('取消'),
					success: (res) => {
						// plus.runtime.openURL("https://colorfulspark.com/#/pages/login/index");
						if (typeof window !== 'undefined' && window.navigator && window.navigator.userAgent) {
							// 网页模式
							window.open("https://colorfulspark2.com/#/pages/login/index");
						} else {
							// App模式或其他环境
							plus.runtime.openURL("https://colorfulspark2.com/#/pages/login/index");
						}
						// 如果用户取消，则不执行任何操作，机器人继续运行
					}
				}); 
			},
			// 开始机器人
			startBot() {
				if (this.isRunning) return;
				
				// 显示加载提示
				uni.showLoading({
					title: this.$t('正在启动...')
				});
				
				this.req({
					url: "bot/start",
					method: "POST", 
					success: (res) => {
						console.log("code",res.code)
						if (res.code == 1) {
							this.isRunning = true;
							this.lastOperationBuy = true;
							this.currentAmount = res.data.initialAmount || 30;
							
							// 刷新数据
							this.loadStats();
							this.loadTransactions();
							 
							uni.showToast({
								title: this.$t('机器人已启动'),
								icon: 'success'
							});
						} else {
							uni.showToast({
								title: res.msg || this.$t('启动失败'),
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
			  
			// 停止机器人
			stopBot() {
				return;
				if (!this.isRunning) return;
				
				// 如果有未平仓的买入订单，先卖出
				if (this.lastOperationBuy) {
					uni.showModal({
						title: this.$t('确认停止'),
						content: this.$t('当前有未平仓的买入订单，停止机器人将自动卖出。是否继续？'), 
						confirmText: this.$t('确认'),
						cancelText: this.$t('取消'),
						success: (res) => {
							if (res.confirm) {
								this.doStopBot(true);
							}
							// 如果用户取消，则不执行任何操作，机器人继续运行
						}
					});
				} else {
					// 没有未平仓订单，直接确认停止
					uni.showModal({
						title: this.$t('确认停止'),
						content: this.$t('确定要停止机器人吗？'),
						confirmText: this.$t('确认'),
						cancelText: this.$t('取消'),
						success: (res) => {
							if (res.confirm) {
								this.doStopBot(false);
							}
							// 如果用户取消，则不执行任何操作
						}
					});
				}
			},
			
			// 执行停止机器人
			doStopBot(sellBeforeStop) {
				this.req({
					url: "bot/stop",
					data: { 
						'sell_before_stop': sellBeforeStop
					},
					success: (res) => {
						if (res.code == 1) {
							this.isRunning = false;
							this.clearTransactionTimer();
							
							// 刷新数据
							this.loadStats();
							this.loadTransactions();
							
							uni.showToast({
								title: sellBeforeStop ? this.$t('已卖出并停止') : this.$t('机器人已停止'),
								icon: 'success'
							});
						} else {
							uni.showToast({
								title: res.msg || this.$t('停止失败'),
								icon: 'none'
							});
						}
					}
				});
			},
			
			// 清除定时器
			clearTransactionTimer() {
				if (this.transactionTimer) {
					clearInterval(this.transactionTimer);
					this.transactionTimer = null;
				}
			},
			
			// 显示通知
			showNotifications() {
				uni.showToast({
					title: this.$t('暂无新通知'),
					icon: 'none'
				});
			},
			
			// 下拉刷新
			onRefresh() {
				this.refreshing = true;
				
				// 刷新所有数据
				Promise.all([
					new Promise(resolve => {
						this.loadBotStatus();
						resolve();
					}),
					new Promise(resolve => {
						this.loadStats();
						resolve();
					}),
					new Promise(resolve => {
						this.loadTransactions();
						resolve();
					})
				]).then(() => {
					setTimeout(() => {
						this.refreshing = false;
						
						uni.showToast({
							title: this.$t('刷新成功'),
							icon: 'success'
						});
					}, 500);
				});
			}
		}
	}
</script>

<style>
	.bot-container {
		background-color: #f5f5f5;
		min-height: 100vh;
		position: relative;
		padding-bottom: 160rpx; /* 为底部按钮留出空间 */
	}
	
	/* 导航栏 */
	.navbar {
		display: flex;
		justify-content: space-between;
		align-items: center;
		height: 44px;
		background-color: #fff;
		padding: 0 15px;
		position: relative;
	}
	
	.navbar-title {
		font-size: 18px;
		font-weight: 500;
	}
	
	.navbar-actions {
		font-size: 22px;
		color: #333;
	}
	
	/* 账户统计 */
	.account-stats {
		background-color: #fff;
		padding: 15px;
		margin-bottom: 10px;
	}
	
	.stats-row {
		display: flex;
	}
	
	.stat-card {
		flex: 1;
		padding: 10px;
		background-color: #f9f9f9;
		border-radius: 8px;
		margin-right: 10px;
	}
	
	.stat-card:last-child {
		margin-right: 0;
	}
	
	.stat-value {
		display: block;
		font-size: 18px;
		font-weight: 600;
		color: #333;
		margin-bottom: 5px;
	}
	
	.stat-label {
		display: block;
		font-size: 12px;
		color: #666;
	}
	
	.profit {
		color: #4cd964;
	}
	
	.loss {
		color: #ff3b30;
	}
	
	/* 交易记录容器 */
	.transaction-container {
		margin-top:100px;
		background-color: #fff;
		border-radius: 10px 10px 0 0;
		padding: 15px 0 0;
		flex: 1;
		display: flex;
		flex-direction: column;
	}
	
	.list-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 0 15px 15px;
		border-bottom: 1px solid #eee;
	}
	
	.list-title {
		font-size: 16px;
		font-weight: 600;
		color: #333;
	}
	
	.bot-status {
		padding: 4px 10px;
		border-radius: 4px;
		font-size: 12px;
	}
	
	.status-running {
		background-color: #e8f5e9;
		color: #4cd964;
	}
	
	.status-stopped {
		background-color: #f5f5f5;
		color: #999;
	}
	
	/* 交易表格 */
	.transaction-list {
		flex: 1;
		height: calc(100vh - 180px);
	}
	
	.transaction-table {
		width: 100%;
	}
	
	.table-header {
		display: flex;
		background-color: #f2f2f2;
		padding: 10px 0;
		border-bottom: 1px solid #eee;
	}
	
	.th {
		font-size: 13px;
		color: #666;
		font-weight: 500;
		text-align: center;
	}
	
	.table-body {
		padding-bottom: 20px;
	}
	
	.table-row {
		display: flex;
		border-bottom: 1px solid #f5f5f5;
		padding: 12px 0;
	}
	
	.td {
		font-size: 13px;
		color: #333;
		text-align: center;
	}
	
	.date-col {
		flex: 1.2;
	}
	
	.time-col {
		flex: 0.8;
	}
	
	.operation-col {
		flex: 1.5;
	}
	
	.amount-col {
		flex: 1.2;
	}
	
	.profit-col {
		flex: 0.8;
	}
	
	.buy-operation {
		color: #007aff;
	}
	
	.sell-operation {
		color: #ff9500;
	}
	
	/* 空状态 */
	.empty-container {  
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		padding: 50px 0;
	}
	
	.empty-icon {
		font-size: 60px;
		color: #ddd;
		margin-bottom: 20px;
	}
	
	.empty-text {
		font-size: 16px;
		color: #999;
		margin-bottom: 10px;
	}
	
	.empty-tips {
		font-size: 14px;
		color: #b3b3b3;
	}
	
	/* 底部操作按钮 */
	.bottom-actions {
		position: fixed;
		bottom: var(--window-bottom); /* 增加底部间距，避免被tabbar遮挡 */
		left: 0;
		right: 0;
		height: 80px;
		background-color: #fff;
		box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
		display: flex;
		justify-content: center;
		align-items: center;
		padding: 10px 20px;
		z-index: 100;
	}
	
	.action-button {
		flex: 1;
		height: 50px;
		border-radius: 25px;
		display: flex;
		justify-content: center;
		align-items: center;
		font-size: 16px;
		font-weight: 500;
		margin: 0 5px;
		width:50%;
	}
	
	.action-icon {
		margin-right: 8px;
		font-size: 18px;
	}
	
	.start-button {
		background-color: #4cd964;
		color: #fff;
	}
	
	.stop-button {
		background-color: #ff3b30;
		color: #fff;
	}
	
	.sell-button {
		background-color: #007aff;
		color: #fff;
	}
	
	.disabled {
		background-color: #cccccc;
		opacity: 0.6;
	}
</style> 