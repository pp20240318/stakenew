<template>
	<view class="page-container">
		<!-- 顶部导航 -->
		<view class="nav-header">
			<image class="back-icon" src="/static/img/market/detail/backicon.png" @tap="goBack"></image>
			<text class="nav-title">{{ $t('guide.title') }}</text>
			<view class="nav-placeholder"></view>
		</view>

		<!-- 内容区域 -->
		<scroll-view class="content-area" scroll-y>
			<!-- 简介 -->
			<view class="intro-card">
				<text class="intro-title">{{ $t('guide.intro.title') }}</text>
				<text class="intro-text">{{ $t('guide.intro.description') }}</text>
			</view>

			<!-- 交易步骤 -->
			<view class="section">
				<text class="section-title">{{ $t('guide.steps.title') }}</text>

				<view class="steps-list">
					<view class="step-card">
						<view class="step-header">
							<view class="step-num">1</view>
							<text class="step-title">{{ $t('guide.steps.step1.title') }}</text>
						</view>
						<text class="step-desc">{{ $t('guide.steps.step1.description') }}</text>
					</view>

					<view class="step-card">
						<view class="step-header">
							<view class="step-num">2</view>
							<text class="step-title">{{ $t('guide.steps.step2.title') }}</text>
						</view>
						<text class="step-desc">{{ $t('guide.steps.step2.description') }}</text>
					</view>

					<view class="step-card">
						<view class="step-header">
							<view class="step-num">3</view>
							<text class="step-title">{{ $t('guide.steps.step3.title') }}</text>
						</view>
						<text class="step-desc">{{ $t('guide.steps.step3.description') }}</text>
					</view>

					<view class="step-card">
						<view class="step-header">
							<view class="step-num">4</view>
							<text class="step-title">{{ $t('guide.steps.step4.title') }}</text>
						</view>
						<text class="step-desc">{{ $t('guide.steps.step4.description') }}</text>
					</view>
				</view>
			</view>

			<!-- 收益说明 -->
			<view class="section">
				<text class="section-title">{{ $t('guide.profit.title') }}</text>
				<view class="profit-card">
					<view class="profit-item">
						<text class="profit-time">{{ $t('guide.profit.time30s') }}</text>
						<text class="profit-rate">100%</text>
					</view>
					<view class="profit-item">
						<text class="profit-time">{{ $t('guide.profit.time1m') }}</text>
						<text class="profit-rate">100%</text>
					</view>
					<view class="profit-item">
						<text class="profit-time">{{ $t('guide.profit.time2m') }}</text>
						<text class="profit-rate">100%</text>
					</view>
					<view class="profit-item">
						<text class="profit-time">{{ $t('guide.profit.time3m') }}</text>
						<text class="profit-rate">100%</text>
					</view>
				</view>
				<text class="profit-hint">{{ $t('guide.profit.correctPrediction') }}</text>
				<text class="profit-hint">{{ $t('guide.profit.wrongPrediction') }}</text>
			</view>

			<!-- 交易示例 -->
			<view class="section">
				<text class="section-title">{{ $t('guide.example.title') }}</text>
				<view class="example-card">
					<view class="example-row">
						<text class="example-label">{{ $t('guide.example.tradingPair') }}</text>
						<text class="example-value">BTC/USDT</text>
					</view>
					<view class="example-row">
						<text class="example-label">{{ $t('guide.example.amount') }}</text>
						<text class="example-value">100 USDT</text>
					</view>
					<view class="example-row">
						<text class="example-label">{{ $t('guide.example.duration') }}</text>
						<text class="example-value">{{ $t('guide.profit.time1m') }}</text>
					</view>
					<view class="example-row">
						<text class="example-label">{{ $t('guide.example.profitRate') }}</text>
						<text class="example-value">100%</text>
					</view>
					<view class="example-row">
						<text class="example-label">{{ $t('guide.example.direction') }}</text>
						<text class="example-value highlight-up">{{ $t('guide.example.buyUp') }}</text>
					</view>
					<view class="example-divider"></view>
					<view class="example-row">
						<text class="example-label">{{ $t('guide.example.correctProfit') }}</text>
						<text class="example-value highlight-profit">+100 USDT</text>
					</view>
				</view>
			</view>

			<!-- 注意事项 -->
			<view class="section">
				<text class="section-title">{{ $t('guide.notes.title') }}</text>
				<view class="info-card">
					<text class="info-text">{{ $t('guide.notes.note1') }}</text>
					<text class="info-text">{{ $t('guide.notes.note2') }}</text>
					<text class="info-text">{{ $t('guide.notes.note3') }}</text>
					<text class="info-text">{{ $t('guide.notes.note4') }}</text>
					<text class="info-text">{{ $t('guide.notes.note5') }}</text>
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

.intro-card {
	background: linear-gradient(135deg, #4A90E2 0%, #357ABD 100%);
	border-radius: 20rpx;
	padding: 30rpx;
	margin-bottom: 30rpx;
	display: flex;
	flex-direction: column;
	gap: 16rpx;
}

.intro-title {
	font-size: 32rpx;
	font-weight: bold;
	color: #FFFFFF;
}

.intro-text {
	font-size: 26rpx;
	color: rgba(255, 255, 255, 0.9);
	line-height: 1.6;
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

.steps-list {
	display: flex;
	flex-direction: column;
	gap: 16rpx;
}

.step-card {
	background-color: #1A1A1A;
	border-radius: 16rpx;
	padding: 24rpx;
	display: flex;
	flex-direction: column;
	gap: 12rpx;
}

.step-header {
	display: flex;
	align-items: center;
	gap: 16rpx;
}

.step-num {
	width: 40rpx;
	height: 40rpx;
	border-radius: 50%;
	background-color: #F9D54A;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 24rpx;
	font-weight: bold;
	color: #000000;
}

.step-title {
	font-size: 28rpx;
	font-weight: 500;
	color: #FFFFFF;
}

.step-desc {
	font-size: 24rpx;
	color: #92979E;
	line-height: 1.5;
	padding-left: 56rpx;
}

.profit-card {
	background-color: #1A1A1A;
	border-radius: 16rpx;
	padding: 24rpx;
	display: flex;
	justify-content: space-between;
	margin-bottom: 16rpx;
}

.profit-item {
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 8rpx;
}

.profit-time {
	font-size: 24rpx;
	color: #92979E;
}

.profit-rate {
	font-size: 32rpx;
	font-weight: bold;
	color: #33D49D;
}

.profit-hint {
	font-size: 24rpx;
	color: #92979E;
	display: block;
	margin-bottom: 8rpx;
}

.example-card {
	background-color: #1A1A1A;
	border-radius: 16rpx;
	padding: 24rpx;
	display: flex;
	flex-direction: column;
	gap: 16rpx;
}

.example-row {
	display: flex;
	justify-content: space-between;
	align-items: center;
}

.example-label {
	font-size: 26rpx;
	color: #92979E;
}

.example-value {
	font-size: 26rpx;
	color: #FFFFFF;
}

.highlight-up {
	color: #33D49D;
	font-weight: 500;
}

.highlight-profit {
	color: #33D49D;
	font-weight: bold;
	font-size: 32rpx;
}

.example-divider {
	height: 1rpx;
	background-color: #2C2C2C;
	margin: 8rpx 0;
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
