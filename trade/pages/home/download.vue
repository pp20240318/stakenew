<template>
	<view class="page-container">
		<!-- 顶部导航 -->
		<view class="nav-header">
			<image class="back-icon" src="/static/img/market/detail/backicon.png" @tap="goBack"></image>
			<text class="nav-title">{{ $t('下载中心') }}</text>
			<view class="nav-placeholder"></view>
		</view>

		<!-- 内容区域 -->
		<scroll-view class="content-area" scroll-y>
			<!-- APP下载 -->
			<view class="section">
				<text class="section-title">{{ $t('APP下载') }}</text>

				<view class="download-card">
					<view class="app-info">
						<image class="app-icon" src="/static/img/logo.png"></image>
						<view class="app-details">
							<text class="app-name">Second Futures</text>
							<text class="app-version">{{ $t('版本') }} 2.1.0</text>
						</view>
					</view>

					<view class="download-buttons">
						<view class="download-btn btn-ios">
							<text class="btn-icon">iOS</text>
							<text class="btn-text">App Store</text>
						</view>
						<view class="download-btn btn-android">
							<text class="btn-icon">Android</text>
							<text class="btn-text">{{ $t('下载APK') }}</text>
						</view>
					</view>

					<view class="qr-section">
						<view class="qr-placeholder">
							<text class="qr-text">{{ $t('扫码下载') }}</text>
						</view>
						<text class="qr-hint">{{ $t('扫描二维码下载APP') }}</text>
					</view>
				</view>
			</view>

			<!-- 版本特性 -->
			<view class="section">
				<text class="section-title">{{ $t('最新版本特性') }}</text>

				<view class="feature-list">
					<view class="feature-item">
						<view class="feature-dot"></view>
						<text class="feature-text">{{ $t('全新UI界面，操作更流畅') }}</text>
					</view>
					<view class="feature-item">
						<view class="feature-dot"></view>
						<text class="feature-text">{{ $t('优化K线图表，数据更精准') }}</text>
					</view>
					<view class="feature-item">
						<view class="feature-dot"></view>
						<text class="feature-text">{{ $t('新增多种交易对支持') }}</text>
					</view>
					<view class="feature-item">
						<view class="feature-dot"></view>
						<text class="feature-text">{{ $t('提升交易速度，降低延迟') }}</text>
					</view>
					<view class="feature-item">
						<view class="feature-dot"></view>
						<text class="feature-text">{{ $t('修复已知问题，提升稳定性') }}</text>
					</view>
				</view>
			</view>

			<!-- 安装说明 -->
			<view class="section">
				<text class="section-title">{{ $t('安装说明') }}</text>
				<view class="info-card">
					<text class="info-title">{{ $t('iOS用户') }}</text>
					<text class="info-text">{{ $t('1. 点击"App Store"按钮跳转到应用商店') }}</text>
					<text class="info-text">{{ $t('2. 点击"获取"按钮下载安装') }}</text>

					<text class="info-title" style="margin-top: 20rpx;">{{ $t('Android用户') }}</text>
					<text class="info-text">{{ $t('1. 点击"下载APK"按钮下载安装包') }}</text>
					<text class="info-text">{{ $t('2. 下载完成后点击安装') }}</text>
					<text class="info-text">{{ $t('3. 如提示"未知来源"，请在设置中允许安装') }}</text>
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

.download-card {
	background-color: #1A1A1A;
	border-radius: 20rpx;
	padding: 30rpx;
}

.app-info {
	display: flex;
	align-items: center;
	gap: 20rpx;
	margin-bottom: 30rpx;
}

.app-icon {
	width: 100rpx;
	height: 100rpx;
	border-radius: 20rpx;
}

.app-details {
	display: flex;
	flex-direction: column;
	gap: 8rpx;
}

.app-name {
	font-size: 32rpx;
	font-weight: 600;
	color: #FFFFFF;
}

.app-version {
	font-size: 24rpx;
	color: #92979E;
}

.download-buttons {
	display: flex;
	gap: 20rpx;
	margin-bottom: 30rpx;
}

.download-btn {
	flex: 1;
	padding: 24rpx;
	border-radius: 12rpx;
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 8rpx;
}

.btn-ios {
	background: linear-gradient(135deg, #333333 0%, #555555 100%);
}

.btn-android {
	background: linear-gradient(135deg, #33D49D 0%, #2AAE82 100%);
}

.btn-icon {
	font-size: 28rpx;
	font-weight: 600;
	color: #FFFFFF;
}

.btn-text {
	font-size: 24rpx;
	color: rgba(255, 255, 255, 0.8);
}

.qr-section {
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 16rpx;
}

.qr-placeholder {
	width: 200rpx;
	height: 200rpx;
	background-color: #FFFFFF;
	border-radius: 12rpx;
	display: flex;
	align-items: center;
	justify-content: center;
}

.qr-text {
	font-size: 24rpx;
	color: #333333;
}

.qr-hint {
	font-size: 24rpx;
	color: #92979E;
}

.feature-list {
	background-color: #1A1A1A;
	border-radius: 16rpx;
	padding: 24rpx;
	display: flex;
	flex-direction: column;
	gap: 20rpx;
}

.feature-item {
	display: flex;
	align-items: center;
	gap: 16rpx;
}

.feature-dot {
	width: 12rpx;
	height: 12rpx;
	border-radius: 50%;
	background-color: #33D49D;
}

.feature-text {
	font-size: 26rpx;
	color: #FFFFFF;
}

.info-card {
	background-color: #1A1A1A;
	border-radius: 16rpx;
	padding: 24rpx;
	display: flex;
	flex-direction: column;
	gap: 12rpx;
}

.info-title {
	font-size: 28rpx;
	font-weight: 500;
	color: #F9D54A;
}

.info-text {
	font-size: 24rpx;
	color: #92979E;
	line-height: 1.6;
}
</style>
