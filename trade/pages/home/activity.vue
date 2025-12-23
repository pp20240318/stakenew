<template>
	<view class="page-container">
		<!-- 顶部导航 -->
		<view class="nav-header">
			<image class="back-icon" src="/static/img/market/detail/backicon.png" @tap="goBack"></image>
			<text class="nav-title">{{ $t('活动福利') }}</text>
			<view class="nav-placeholder"></view>
		</view>

		<!-- 内容区域 -->
		<scroll-view class="content-area" scroll-y>
			<!-- 活动Banner -->
			<view class="activity-banner">
				<view class="banner-content">
					<text class="banner-title">{{ $t('新用户专享福利') }}</text>
					<text class="banner-desc">{{ $t('注册即送100 USDT体验金') }}</text>
				</view>
			</view>

			<!-- 活动列表 -->
			<view class="section">
				<text class="section-title">{{ $t('热门活动') }}</text>

				<view class="activity-item">
					<view class="activity-icon icon-gift"></view>
					<view class="activity-info">
						<text class="activity-name">{{ $t('新手任务奖励') }}</text>
						<text class="activity-desc">{{ $t('完成新手任务，最高可获得500 USDT奖励') }}</text>
					</view>
					<text class="activity-status status-active">{{ $t('进行中') }}</text>
				</view>

				<view class="activity-item">
					<view class="activity-icon icon-invite"></view>
					<view class="activity-info">
						<text class="activity-name">{{ $t('邀请好友返佣') }}</text>
						<text class="activity-desc">{{ $t('邀请好友交易，享受永久返佣20%') }}</text>
					</view>
					<text class="activity-status status-active">{{ $t('进行中') }}</text>
				</view>

				<view class="activity-item">
					<view class="activity-icon icon-trade"></view>
					<view class="activity-info">
						<text class="activity-name">{{ $t('交易量排行赛') }}</text>
						<text class="activity-desc">{{ $t('周交易量TOP10瓜分10000 USDT') }}</text>
					</view>
					<text class="activity-status status-active">{{ $t('进行中') }}</text>
				</view>

				<view class="activity-item">
					<view class="activity-icon icon-deposit"></view>
					<view class="activity-info">
						<text class="activity-name">{{ $t('首充双倍赠送') }}</text>
						<text class="activity-desc">{{ $t('首次充值即送同等金额体验金') }}</text>
					</view>
					<text class="activity-status status-ended">{{ $t('已结束') }}</text>
				</view>
			</view>

			<!-- 福利说明 -->
			<view class="section">
				<text class="section-title">{{ $t('福利说明') }}</text>
				<view class="info-card">
					<text class="info-text">{{ $t('1. 所有活动奖励将在活动结束后3个工作日内发放') }}</text>
					<text class="info-text">{{ $t('2. 体验金可用于模拟交易，盈利部分可提现') }}</text>
					<text class="info-text">{{ $t('3. 如有任何疑问，请联系在线客服') }}</text>
					<text class="info-text">{{ $t('4. 最终解释权归平台所有') }}</text>
				</view>
			</view>
		</scroll-view>
	</view>
</template>

<script>
export default {
	methods: {
		goBack() {
			uni.navigateBack({
				delta: 1
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

.activity-banner {
	background: linear-gradient(135deg, #F9D54A 0%, #F0A500 100%);
	border-radius: 20rpx;
	padding: 40rpx;
	margin-bottom: 30rpx;
}

.banner-content {
	display: flex;
	flex-direction: column;
	gap: 12rpx;
}

.banner-title {
	font-size: 36rpx;
	font-weight: bold;
	color: #000000;
}

.banner-desc {
	font-size: 28rpx;
	color: #333333;
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

.activity-item {
	background-color: #1A1A1A;
	border-radius: 16rpx;
	padding: 24rpx;
	margin-bottom: 16rpx;
	display: flex;
	align-items: center;
	gap: 20rpx;
}

.activity-icon {
	width: 80rpx;
	height: 80rpx;
	border-radius: 16rpx;
	display: flex;
	align-items: center;
	justify-content: center;
}

.icon-gift {
	background: linear-gradient(135deg, #FF6B6B 0%, #FF8E8E 100%);
}

.icon-invite {
	background: linear-gradient(135deg, #33D49D 0%, #4AE8B0 100%);
}

.icon-trade {
	background: linear-gradient(135deg, #4A90E2 0%, #6BA3EC 100%);
}

.icon-deposit {
	background: linear-gradient(135deg, #F9D54A 0%, #FBE178 100%);
}

.activity-info {
	flex: 1;
	display: flex;
	flex-direction: column;
	gap: 8rpx;
}

.activity-name {
	font-size: 28rpx;
	font-weight: 500;
	color: #FFFFFF;
}

.activity-desc {
	font-size: 24rpx;
	color: #92979E;
}

.activity-status {
	font-size: 24rpx;
	padding: 8rpx 16rpx;
	border-radius: 8rpx;
}

.status-active {
	background-color: rgba(51, 212, 157, 0.2);
	color: #33D49D;
}

.status-ended {
	background-color: rgba(146, 151, 158, 0.2);
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
