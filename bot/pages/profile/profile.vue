<template>
	<view class="profile-container">
		<!-- 用户信息卡片 -->
		<view class="user-card">
			<view class="user-info">
				<image :src="userInfo.avatar || '/static/images/avatar/default-avatar.png'" class="user-avatar"></image>
				<view class="user-details">
					<text class="user-name">{{ userInfo.nickname }}</text>
					<view class="user-level">
						<text class="level-text">{{ userInfo.level }}</text>
					</view>
				</view>
			</view>
			
			<view class="user-stats">
				<view class="stat-item">
					<text class="stat-value asset-amount">{{ userInfo.totalAssets }}</text>
					<view class="user-level" style="margin-left:10px;margin-bottom:20rpx">
						<text class="refresh-icon ri-refresh-line" @tap.stop="fetchUserData">{{ $t('刷新') }}</text>
						</view>
					<text class="stat-label">{{ $t('总资产') }}(USDT)</text>
				</view>
				<!-- <view class="stat-item"> 
					<text class="stat-value" :class="{'positive': userInfo.totalProfit > 0, 'negative': userInfo.totalProfit < 0}">
						{{ userInfo.totalProfit > 0 ? '+' : '' }}{{ userInfo.totalProfit }}%
					</text>
					<text class="stat-label">{{ $t('总收益') }}</text>
				</view> -->
			</view>
		</view>
		
		<!-- 功能列表 -->
		<view class="feature-list">
			<view class="feature-group">
				<view class="feature-item" @tap="navigateTo('/pages/invite/invite')">
					<view class="feature-icon-container">
						<text class="feature-icon ri-user-add-line"></text>
					</view>
					<text class="feature-title">{{ $t('邀请好友') }}</text>
					<text class="feature-desc">{{ $t('获得更高机器人权限') }}</text>
				</view>
				<view class="feature-item" @tap="navigateTo('/pages/team/index')">
					<view class="feature-icon-container">
						<text class="feature-icon ri-team-line"></text>
					</view>
					<text class="feature-title">{{ $t('我的团队') }}</text>
					<text class="feature-desc">{{ $t('查看团队收益') }}</text>
				</view>
				
			</view>
		 
		</view>
		
		<!-- 代理等级信息 -->
		<view class="section-card agent-level-card">
			<view class="section-header">
				<view class="section-title-container">
					<text class="ri-trophy-line section-icon"></text>
					<text class="section-title">{{ $t('代理等级') }}</text>
				</view>
				 
			</view>
			
			<view class="agent-level-info">
				<view class="agent-level-header">
					<view class="current-level">
						<text class="level-label">{{ $t('当前等级') }}</text>
						<text class="level-value">{{ "Level" +userInfo.level }}</text>
					</view>
					<view class="level-limit">
						<text class="limit-label">{{ $t('交易额度上限') }}</text>
						<text class="limit-value">{{ userInfo.tasklimit }} USDT</text>
					</view>
				</view>
				 
				<view class="commission-info" style="display:none">
					<text class="commission-label">{{ $t('佣金比例') }}</text>
					<text class="commission-value">{{ agentInfo.commissionRate }}</text>
				</view>
			 
				<view class="level-progress" v-if="userInfo.level<5" >
					<view class="progress-header">
						<text class="progress-title">{{ $t('距离升级还需邀请') }}</text>
						<text class="progress-value">{{ userInfo.upgrade_num - userInfo.reference_num}} </text>
					</view>
					<view class="progress-bar-container">
						<view class="progress-bar" :style="{ width: (100*userInfo.reference_num/ userInfo.upgrade_num)+'%' }"></view>
					</view>
					<view class="progress-labels">
						<text class="progress-current">{{ $t('已邀请') }}: {{ userInfo.reference_num }}</text>
						<text class="progress-target">{{ $t('目标') }}: {{ userInfo.upgrade_num }}</text>
					</view>
				</view>
				
				<view class="level-max-notice" v-else>
					<text class="max-level-text">{{ $t('恭喜您已达到最高代理等级！') }}</text>
				</view>
			</view>
		</view>
		
		 
		<!-- 退出登录 -->
		<button class="logout-btn" @tap="logout"><text class="logout-icon ri-logout-box-r-line"></text> {{ $t('退出登录') }}</button>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				userInfo: {
					nickname: '',
					avatar: '',
					level: '',
					totalAssets: '0.00',
					totalProfit: 0,
					runningBots: 0
				},
				
				assets: [
					{
						name: 'Bitcoin',
						symbol: 'BTC',
						icon: '/static/images/coins/btc.png',
						balance: '0.0235',
						value: '1,013.45'
					},
					{
						name: 'Ethereum',
						symbol: 'ETH',
						icon: '/static/images/coins/eth.png',
						balance: '0.54',
						value: '845.12'
					},
					{
						name: 'Tether',
						symbol: 'USDT',
						icon: '/static/images/coins/usdt.png',
						balance: '560.45',
						value: '560.45'
					}
				],
				
				notices: [
					{
						id: 1,
						title: '系统升级通知：将于5月15日进行系统维护',
						time: '2小时前',
						read: false
					},
					{
						id: 2,
						title: '恭喜您完成新手任务，获得5USDT奖励！',
						time: '昨天',
						read: true
					},
					{
						id: 3,
						title: '比特币价格突破新高，快来关注市场动态',
						time: '3天前',
						read: true
					}
				],
				
				agentInfo: {
					level: 'Level1',
					tradeLimit: '200',
					commissionRate: 'commissionRate',
					invitesNeeded: 3,
					currentInvites: 1,
					targetInvites: 3,
					progressPercentage: 33
				}
			}
		},
		onShow() {
			// 页面加载时获取用户数据
			this.fetchUserData();
		},
		methods: {
			navigateTo(url) {
				uni.navigateTo({
					url: url
				});
			},
			
			readNotice(noticeId) {
				// 标记通知为已读
				const noticeIndex = this.notices.findIndex(notice => notice.id === noticeId);
				if (noticeIndex !== -1) {
					this.notices[noticeIndex].read = true;
				}
				
				// 跳转到通知详情页
				uni.navigateTo({
					url: `/pages/notifications/detail?id=${noticeId}`
				});
			},
			
			contactSupport() {
				// 打开客服会话
				uni.showToast({
					title: '正在连接客服...',
					icon: 'none'
				});
				
				// 这里可以实现真实的客服系统连接
				setTimeout(() => {
					uni.navigateTo({
						url: '/pages/support/chat'
					});
				}, 1000);
			},
			
			logout() {
				// 确认退出
				uni.showModal({
					title: this.$t('提示'),
					content: this.$t('确定要退出登录吗？'), 
					confirmText:  this.$t('确认'),
					cancelText:  this.$t('取消'),
					success: (res) => {
						if (res.confirm) {
							// 清除登录状态
							// uni.removeStorageSync('token');
							// uni.removeStorageSync('userInfo'    );
							
							// 返回登录页
							uni.reLaunch({
								url: '/pages/login/index'
							});
						}
					}
				});
			},
			
			async fetchUserData() {
				try {
					// 获取用户信息和余额
					this.req({
						url: "user/index", 
						success: (res) => {
							if (res.code === 1) {
								const userData = res.data;
								
								// 设置用户基本信息
								if (userData.userdata) {
									this.userInfo.nickname = userData.userdata.nickname || userData.userdata.username;
									this.userInfo.avatar = userData.userdata.avatar || '/static/images/avatar/default-avatar.png';
									this.userInfo.level = userData.userdata.level;
									this.userInfo.upgrade_num = userData.upgradenum; 
									this.userInfo.tasklimit = userData.tasklimit;
									this.userInfo.reference_num = userData.userdata.reference_num; 
									
									// 设置余额
									this.userInfo.totalAssets = userData.userdata.money || '0.00';
								}
								
								// 设置收益率
								if (userData.profit_data) {
									this.userInfo.totalProfit = userData.profit_data.profit_rate || '0.00';
								}
							}
						},
						fail: (err) => {
							console.error('获取用户数据失败:', err);
							uni.showToast({
								title: this.$t('获取用户数据失败'),
								icon: 'none'
							});
						}
					});
				} catch (error) {
					console.error('获取用户数据失败:', error);
					uni.showToast({
						title: this.$t('获取用户数据失败'),
						icon: 'none'
					});
				}
			}
		}
	}
</script>

<style>
	.profile-container {
		padding: 30rpx;
		padding-bottom: 100rpx;
		background-color: #F5F5F5;
	}
	.uni-page-body{
		background-color:#F5F5F5 !import;
	}
	/* 用户信息卡片样式 */
	.user-card {
		background-color: #007AFF;
		border-radius: 16rpx;
		padding: 40rpx 30rpx;
		color: #FFFFFF;
		margin-bottom: 30rpx;
		box-shadow: 0 8rpx 20rpx rgba(0, 122, 255, 0.2);
	}
	
	.user-info {
		display: flex;
		align-items: center;
		margin-bottom: 30rpx;
	}
	
	.user-avatar {
		width: 120rpx;
		height: 120rpx;
		border-radius: 60rpx;
		border: 4rpx solid rgba(255, 255, 255, 0.3);
	}
	
	.user-details {
		margin-left: 20rpx;
	}
	
	.user-name {
		font-size: 36rpx;
		font-weight: bold;
		margin-bottom: 10rpx;
		display: block;
	}
	
	.user-level {
		display: inline-block;
		background-color: rgba(255, 255, 255, 0.2);
		padding: 4rpx 16rpx;
		border-radius: 20rpx;
	}
	
	.level-text {
		font-size: 24rpx;
	}
	
	.user-stats {
		display: flex;
		justify-content: space-between;
	}
	
	.stat-item {
		display: flex;
		flex-direction: column;
		align-items: center;
	}
	
	.stat-value {
		font-size: 32rpx;
		font-weight: bold;
		margin-bottom: 8rpx;
		color: #FFFFFF;
		text-shadow: 0 1rpx 3rpx rgba(0, 0, 0, 0.2);
	}
	
	/* Custom color for total assets amount */
	.asset-amount {
		color: #FFE082 !important; /* Bright gold color that stands out on blue */
		font-size: 34rpx !important;
		text-shadow: 0 2rpx 4rpx rgba(0, 0, 0, 0.3) !important;
	}
	
	/* Custom color for bot count */
	.bot-count {
		color: #81D4FA !important; /* Bright light blue that stands out */
		font-size: 34rpx !important;
		text-shadow: 0 2rpx 4rpx rgba(0, 0, 0, 0.3) !important;
	}
	
	.stat-label {
		font-size: 24rpx;
		opacity: 1;
		color: rgba(255, 255, 255, 0.9);
	}
	
	.positive {
		color: #4FD964;
	}
	
	.negative {
		color: #FF3B30;
	}
	
	/* 通用卡片样式 */
	.section-card {
		background-color: #FFFFFF;
		border-radius: 12rpx;
		padding: 30rpx;
		margin-bottom: 30rpx;
		box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
	}
	
	.section-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 20rpx;
	}
	
	.section-title-container {
		display: flex;
		align-items: center;
	}
	
	.section-icon {
		font-size: 32rpx;
		color: #007AFF;
		margin-right: 10rpx;
	}
	
	.section-title {
		font-size: 32rpx;
		font-weight: bold;
		color: #333333;
	}
	
	.section-action {
		font-size: 26rpx;
		color: #007AFF;
		display: flex;
		align-items: center;
	}
	
	.section-action .ri-arrow-right-s-line {
		font-size: 24rpx;
		margin-left: 2rpx;
	}
	
	/* 资产预览样式 */
	.asset-preview {
		display: flex;
		flex-direction: column;
	}
	
	.asset-item {
		display: flex;
		align-items: center;
		padding: 20rpx 0;
		border-bottom: 1rpx solid #F2F2F2;
	}
	
	.asset-item:last-child {
		border-bottom: none;
	}
	
	.asset-icon {
		width: 60rpx;
		height: 60rpx;
		margin-right: 20rpx;
	}
	
	.asset-details {
		flex: 1;
	}
	
	.asset-name {
		font-size: 28rpx;
		color: #333333;
		margin-bottom: 6rpx;
		display: block;
	}
	
	.asset-balance {
		font-size: 24rpx;
		color: #666666;
		font-weight: 500;
	}
	
	.asset-value {
		font-size: 30rpx;
		color: #333333;
		font-weight: 500;
	}
	
	/* 功能列表样式 */
	.feature-list {
		margin-bottom: 30rpx;
	}
	
	.feature-group {
		display: flex;
		justify-content: space-around;
		margin-bottom: 20rpx;
	}
	
	.feature-item {
		width: 50%;
		background-color: #FFFFFF;
		border-radius: 12rpx;
		padding: 30rpx 10rpx; 
		display: flex;
		flex-direction: column;
		align-items: center;
		box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
	}
	
	.feature-icon-container {
		width: 80rpx;
		height: 80rpx;
		border-radius: 50%;
		background-color: rgba(0, 122, 255, 0.1);
		display: flex;
		align-items: center;
		justify-content: center;
		margin-bottom: 15rpx;
	}
	
	.feature-icon {
		font-size: 40rpx;
		color: #007AFF;
	}
	
	.feature-title {
		font-size: 28rpx;
		font-weight: bold;
		color: #333333;
		margin-bottom: 8rpx;
		text-align: center;
	}
	
	.feature-desc {
		font-size: 22rpx;
		color: #666666;
		text-align: center;
	}
	
	/* 通知公告样式 */
	.notice-list {
		display: flex;
		flex-direction: column;
	}
	
	.notice-item {
		display: flex;
		align-items: center;
		padding: 20rpx 0;
		border-bottom: 1rpx solid #F2F2F2;
		position: relative;
	}
	
	.notice-item:last-child {
		border-bottom: none;
	}
	
	.notice-badge {
		width: 16rpx;
		height: 16rpx;
		border-radius: 8rpx;
		background-color: #FF3B30;
		position: absolute;
		left: -10rpx;
		top: 30rpx;
	}
	
	.notice-title {
		flex: 1;
		font-size: 28rpx;
		color: #333333;
		padding-right: 20rpx;
	}
	
	.notice-time {
		font-size: 24rpx;
		color: #666666;
		white-space: nowrap;
	}
	
	/* 客服支持样式 */
	.support-card {
		background-color: #FFFFFF;
		border-radius: 12rpx;
		padding: 30rpx;
		margin-bottom: 30rpx;
		display: flex;
		justify-content: space-between;
		align-items: center;
		box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
	}
	
	.support-content {
		display: flex;
		align-items: center;
	}
	
	.support-icon-container {
		width: 70rpx;
		height: 70rpx;
		border-radius: 50%;
		background-color: rgba(0, 122, 255, 0.1);
		display: flex;
		align-items: center;
		justify-content: center;
		margin-right: 20rpx;
	}
	
	.support-icon {
		font-size: 36rpx;
		color: #007AFF;
	}
	
	.support-text {
		display: flex;
		flex-direction: column;
	}
	
	.support-title {
		font-size: 30rpx;
		font-weight: bold;
		color: #333333;
		margin-bottom: 6rpx;
	}
	
	.support-desc {
		font-size: 24rpx;
		color: #666666;
	}
	
	.support-btn {
		padding: 15rpx 30rpx;
		background-color: #007AFF;
		color: #FFFFFF;
		font-size: 26rpx;
		border-radius: 30rpx;
		display: flex;
		align-items: center;
		justify-content: center;
	}
	
	.support-btn-text {
		color: #FFFFFF;
	}
	
	/* 退出登录按钮 */
	.logout-btn {
		width: 100%;
		height: 90rpx;
		line-height: 90rpx;
		text-align: center;
		background-color: #FFFFFF;
		color: #FF3B30;
		font-size: 32rpx;
		border-radius: 45rpx;
		margin-bottom: 50rpx;
		display: flex;
		align-items: center;
		justify-content: center;
	}
	
	.logout-icon {
		margin-right: 10rpx;
		font-size: 30rpx;
	}
	
	/* 空状态提示 */
	.empty-tips {
		padding: 40rpx 0;
		display: flex;
		justify-content: center;
		align-items: center;
	}
	
	.empty-text {
		font-size: 28rpx;
		color: #666666;
	}
	
	/* 代理等级信息样式 */
	.agent-level-card {
		background-color: #FFFFFF;
		border-radius: 12rpx;
		padding: 30rpx;
		margin-bottom: 30rpx;
		box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
	}
	
	.agent-level-info {
		display: flex;
		flex-direction: column;
	}
	
	.agent-level-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 20rpx;
	}
	
	.current-level {
		display: flex;
		flex-direction: column;
	}
	
	.level-label {
		font-size: 24rpx;
		color: #666666;
	}
	
	.level-value {
		font-size: 32rpx;
		font-weight: bold;
		color: #333333;
	}
	
	.level-limit {
		display: flex;
		flex-direction: column;
	}
	
	.limit-label {
		font-size: 24rpx;
		color: #666666;
	}
	
	.limit-value {
		font-size: 32rpx;
		font-weight: bold;
		color: #333333;
	}
	
	.commission-info {
		margin-top: 20rpx;
		margin-bottom: 20rpx;
	}
	
	.commission-label {
		font-size: 24rpx;
		color: #666666;
	}
	
	.commission-value {
		font-size: 32rpx;
		font-weight: bold;
		color: #333333;
	}
	
	.level-progress {
		margin-top: 20rpx;
		margin-bottom: 20rpx;
	}
	
	.progress-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 10rpx;
	}
	
	.progress-title {
		font-size: 28rpx;
		color: #666666;
	}
	
	.progress-value {
		font-size: 28rpx;
		font-weight: bold;
		color: #333333;
	}
	
	.progress-bar-container {
		height: 20rpx;
		background-color: #F2F2F2;
		border-radius: 10rpx;
		margin-bottom: 10rpx;
	}
	
	.progress-bar {
		height: 100%;
		background-color: #007AFF;
		border-radius: 10rpx;
	}
	
	.progress-labels {
		display: flex;
		justify-content: space-between;
		font-size: 24rpx;
		color: #666666;
	}
	
	.progress-current {
		font-weight: bold;
	}
	
	.progress-target {
		font-weight: bold;
	}
	
	.level-max-notice {
		text-align: center;
		font-size: 28rpx;
		color: #333333;
	}
</style> 