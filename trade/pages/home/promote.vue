<template>
	<view class="page-container">
		<!-- 顶部导航 -->
		<view class="nav-header">
			<image class="back-icon" src="/static/img/market/detail/backicon.png" @tap="goBack"></image>
			<text class="nav-title">{{ $t('推广中心') }}</text>
			<view class="nav-placeholder"></view>
		</view>

		<!-- 内容区域 -->
		<scroll-view class="content-area" scroll-y>
			 

			<!-- 邀请链接 -->
			<view class="section">
				<text class="section-title">{{ $t('我的邀请链接') }}</text>
				<view class="invite-card">
					<text class="invite-code">{{currentDomain}}/#/pages/login/register?code={{invitecode}}</text>
					<view class="invite-actions">
						<view class="action-btn btn-copy" @tap="copyInviteLink">
							<text class="action-text">{{ $t('复制链接') }}</text>
						</view>
						<view class="action-btn btn-share" @tap="copyInviteLink">
							<text class="action-text">{{ $t('分享') }}</text>
						</view>
					</view>
				</view>
			</view>
 

			<!-- 推广步骤 -->
			<view class="section">
				<text class="section-title">{{ $t('如何推广') }}</text>
				<view class="steps-card">
					<view class="step-item">
						<view class="step-number">1</view>
						<view class="step-content">
							<text class="step-title">{{ $t('复制邀请链接') }}</text>
							<text class="step-desc">{{ $t('点击上方复制按钮获取您的专属邀请链接') }}</text>
						</view>
					</view>
					<view class="step-item">
						<view class="step-number">2</view>
						<view class="step-content">
							<text class="step-title">{{ $t('分享给好友') }}</text>
							<text class="step-desc">{{ $t('将链接分享到社交媒体或发送给好友') }}</text>
						</view>
					</view>

				</view>
			</view>

		 
		</scroll-view>
	</view>
</template>

<script>
export default {
	data() {
		return {
			invitecode: uni.getStorageSync('userdata')['invite_code'],
			currentDomain: ''
		}
	},
	onLoad() {
		// 获取当前域名
		// #ifdef H5
		this.currentDomain = window.location.origin;
		// #endif
		// #ifndef H5
		this.currentDomain = this.H5BaseUrl || '';
		// #endif
	},
	onShow: function() {
		console.log(uni.getStorageSync('userdata')['invite_code']);
	},
	methods: {
		goBack() {
			uni.navigateBack({
				delta: 1
			});
		},
		copyInviteLink() {
			const inviteLink = `${this.currentDomain}/#/pages/login/register?code=${this.invitecode}`;
			uni.setClipboardData({
				data: inviteLink,
				success: () => {
					uni.showToast({
						title: this.$t('复制成功'),
						icon: 'success',
						duration: 2000
					});
				},
				fail: () => {
					uni.showToast({
						title: this.$t('复制失败'),
						icon: 'none',
						duration: 2000
					});
				}
			});
		}
	}
}
</script>

<style>
/* 全局盒模型重置 */
view, text, image {
	box-sizing: border-box;
}

.page-container {
	background-color: #0E0F0F;
	min-height: 100vh;
	display: flex;
	flex-direction: column;
	width: 100%;
	box-sizing: border-box;
	overflow-x: hidden;
}

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

.content-area {
	flex: 1;
	padding: 20rpx;
	box-sizing: border-box;
	width: 100%;
}

.content-area > view {
	box-sizing: border-box;
}

.stats-card {
	background: linear-gradient(135deg, #F9D54A 0%, #F0A500 100%);
	border-radius: 20rpx;
	padding: 40rpx;
	display: flex;
	align-items: center;
	margin-bottom: 30rpx;
}

.stat-item {
	flex: 1;
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 12rpx;
}

.stat-value {
	font-size: 48rpx;
	font-weight: bold;
	color: #000000;
}

.stat-label {
	font-size: 24rpx;
	color: #333333;
}

.stat-divider {
	width: 2rpx;
	height: 60rpx;
	background-color: rgba(0, 0, 0, 0.2);
}

.section {
	margin-bottom: 30rpx;
}

.section-title {
	font-size: 32rpx;
	font-weight: 600;
	color: #FFFFFF;
	margin-bottom: 20rpx;
	display: block;
}

.invite-card {
	background-color: #1A1A1A;
	border-radius: 16rpx;
	padding: 24rpx;
}

.invite-code {
	font-size: 24rpx;
	color: #92979E;
	word-break: break-all;
	margin-bottom: 20rpx;
	display: block;
}

.invite-actions {
	display: flex;
	gap: 20rpx;
}

.action-btn {
	flex: 1;
	padding: 20rpx;
	border-radius: 12rpx;
	display: flex;
	align-items: center;
	justify-content: center;
}

.btn-copy {
	background-color: #2C2C2C;
}

.btn-share {
	background-color: #F9D54A;
}

.btn-copy .action-text {
	color: #FFFFFF;
}

.btn-share .action-text {
	color: #000000;
}

.action-text {
	font-size: 28rpx;
	font-weight: 500;
}

.rule-card {
	background-color: #1A1A1A;
	border-radius: 16rpx;
	padding: 24rpx;
	display: flex;
	flex-direction: column;
	gap: 20rpx;
}

.rule-item {
	display: flex;
	align-items: center;
	gap: 16rpx;
}

.rule-level {
	width: 60rpx;
	height: 60rpx;
	border-radius: 12rpx;
	display: flex;
	align-items: center;
	justify-content: center;
}

.level-1 {
	background: linear-gradient(135deg, #F9D54A 0%, #F0A500 100%);
}

.level-2 {
	background: linear-gradient(135deg, #C0C0C0 0%, #A0A0A0 100%);
}

.level-3 {
	background: linear-gradient(135deg, #CD7F32 0%, #A0522D 100%);
}

.level-text {
	font-size: 20rpx;
	font-weight: 600;
	color: #000000;
}

.rule-info {
	flex: 1;
	display: flex;
	flex-direction: column;
	gap: 4rpx;
}

.rule-title {
	font-size: 28rpx;
	font-weight: 500;
	color: #FFFFFF;
}

.rule-desc {
	font-size: 22rpx;
	color: #92979E;
}

.rule-rate {
	font-size: 32rpx;
	font-weight: bold;
	color: #33D49D;
}

.steps-card {
	background-color: #1A1A1A;
	border-radius: 16rpx;
	padding: 24rpx;
	display: flex;
	flex-direction: column;
	gap: 24rpx;
}

.step-item {
	display: flex;
	align-items: flex-start;
	gap: 16rpx;
}

.step-number {
	width: 48rpx;
	height: 48rpx;
	border-radius: 50%;
	background-color: #F9D54A;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 24rpx;
	font-weight: bold;
	color: #000000;
	flex-shrink: 0;
}

.step-content {
	flex: 1;
	display: flex;
	flex-direction: column;
	gap: 8rpx;
}

.step-title {
	font-size: 28rpx;
	font-weight: 500;
	color: #FFFFFF;
}

.step-desc {
	font-size: 24rpx;
	color: #92979E;
}

.info-card {
	background-color: #1A1A1A;
	border-radius: 16rpx;
	padding: 24rpx;
	display: flex;
	flex-direction: column;
	gap: 16rpx;
}

.info-text {
	font-size: 24rpx;
	color: #92979E;
	line-height: 1.6;
}
</style>
