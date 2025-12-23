<template>
	<view class="google-auth-container">
		<!-- 顶部导航栏 -->
		<view class="navbar">
			<view @tap="$goBack()">
				<image src="/static/images/icons/back-arrow.svg" class="back-icon"></image>
			</view>
			<text class="page-title">{{ $t('谷歌验证器') }}</text>
			<view class="navbar-right"></view>
		</view>

		<!-- 已绑定状态 -->
		<view class="form-card" v-if="isBound">
			<view class="bound-status">
				<view class="status-icon-wrapper">
					<text class="status-icon">✓</text>
				</view>
				<text class="bound-text">{{ $t('已绑定谷歌验证器') }}</text>
			</view>

			<view class="form-section">
				<text class="section-title">{{ $t('验证码') }}</text>
				<view class="input-wrapper">
					<input
						v-model="verifyCode"
						type="number"
						maxlength="6"
						:placeholder="$t('请输入6位验证码')"
						class="input-field"
					/>
				</view>
			</view>

			<button class="submit-button unbind-button" @tap="unbindGoogle()">
				{{ $t('解绑谷歌验证器') }}
			</button>
		</view>

		<!-- 未绑定状态 -->
		<view class="form-card" v-else>
			<!-- 步骤1: 下载APP -->
			<view class="step-section">
				<view class="step-header">
					<view class="step-number">1</view>
					<text class="step-title">{{ $t('下载谷歌验证器') }}</text>
				</view>
				<view class="download-buttons">
					<view class="download-btn" @tap="downloadApp('ios')">
						<image src="/static/img/app-store.png" mode="aspectFit" class="store-icon"></image>
						<text class="download-text">App Store</text>
					</view>
					<view class="download-btn" @tap="downloadApp('android')">
						<image src="/static/img/google-play.png" mode="aspectFit" class="store-icon"></image>
						<text class="download-text">Google Play</text>
					</view>
				</view>
			</view>

			<!-- 步骤2: 扫描二维码 -->
			<view class="step-section">
				<view class="step-header">
					<view class="step-number">2</view>
					<text class="step-title">{{ $t('扫描二维码') }}</text>
				</view>
				<view class="qrcode-wrapper">
					<image v-if="qrcodeUrl" :src="qrcodeUrl" mode="aspectFit" class="qrcode-image"></image>
					<view v-else class="qrcode-placeholder">
						<text class="placeholder-text">{{ $t('加载中...') }}</text>
					</view>
				</view>
				<view class="secret-key-section">
					<text class="secret-label">{{ $t('密钥') }}:</text>
					<text class="secret-key" selectable>{{ secretKey }}</text>
					<view class="copy-btn" @tap="copySecret()">
						<text class="copy-text">{{ $t('复制') }}</text>
					</view>
				</view>
			</view>

			<!-- 步骤3: 输入验证码 -->
			<view class="step-section">
				<view class="step-header">
					<view class="step-number">3</view>
					<text class="step-title">{{ $t('输入验证码') }}</text>
				</view>
				<view class="form-section">
					<view class="input-wrapper">
						<input
							v-model="verifyCode"
							type="number"
							maxlength="6"
							:placeholder="$t('请输入6位验证码')"
							class="input-field"
						/>
					</view>
				</view>
			</view>

			<button class="submit-button" @tap="bindGoogle()">
				{{ $t('绑定') }}
			</button>
		</view>

		<!-- 说明卡片 -->
		<view class="info-card">
			<view class="info-header">
				<text class="info-icon">ℹ</text>
				<text class="info-title">{{ $t('谷歌验证器说明') }}</text>
			</view>
			<view class="info-content">
				<text class="info-item">· {{ $t('谷歌验证器是一款动态口令工具，每30秒生成一个新的验证码') }}</text>
				<text class="info-item">· {{ $t('绑定后，提现等敏感操作需要输入验证码') }}</text>
				<text class="info-item">· {{ $t('请妥善保管密钥，更换手机时需要使用密钥恢复') }}</text>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		data: function() {
			return {
				isBound: false,
				qrcodeUrl: '',
				secretKey: '',
				verifyCode: ''
			}
		},
		onLoad: function() {
			var userid = uni.getStorageSync('userid');
			if (!userid) this.$gopage("/pages/login/index");
			this.loadGoogleAuthStatus();
		},
		methods: {
			// 加载谷歌验证器状态
			loadGoogleAuthStatus() {
				var t = this;
				t.req({
					url: "user/googleAuthStatus",
					Loading: true,
					success: function(res) {
						if (res.code === 1) {
							t.isBound = res.data.is_bound || false;
							if (!t.isBound) {
								t.qrcodeUrl = res.data.qrcode_url || '';
								t.secretKey = res.data.secret_key || '';
							}
						}
					}
				});
			},

			// 下载APP
			downloadApp(platform) {
				let url = '';
				if (platform === 'ios') {
					url = 'https://apps.apple.com/app/google-authenticator/id388497605';
				} else {
					url = 'https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2';
				}
				// #ifdef H5
				window.open(url, '_blank');
				// #endif
				// #ifdef APP-PLUS
				plus.runtime.openURL(url);
				// #endif
				// #ifdef MP
				uni.setClipboardData({
					data: url,
					success: () => {
						uni.showToast({
							title: this.$t('链接已复制'),
							icon: 'success'
						});
					}
				});
				// #endif
			},

			// 复制密钥
			copySecret() {
				if (!this.secretKey) return;
				uni.setClipboardData({
					data: this.secretKey,
					success: () => {
						uni.showToast({
							title: this.$t('复制成功'),
							icon: 'success'
						});
					}
				});
			},

			// 绑定谷歌验证器
			bindGoogle() {
				var t = this;
				if (!t.verifyCode || t.verifyCode.length !== 6) {
					return t.$toast(t.$t('请输入6位验证码'));
				}

				t.req({
					url: "user/bindGoogleAuth",
					data: {
						code: t.verifyCode,
						secret: t.secretKey
					},
					Loading: true,
					success: function(res) {
						if (res.code === 1) {
							uni.showToast({
								title: t.$t('绑定成功'),
								icon: 'success'
							});
							t.isBound = true;
							t.verifyCode = '';
						} else {
							t.$toast(res.msg || t.$t('绑定失败'));
						}
					}
				});
			},

			// 解绑谷歌验证器
			unbindGoogle() {
				var t = this;
				if (!t.verifyCode || t.verifyCode.length !== 6) {
					return t.$toast(t.$t('请输入6位验证码'));
				}

				uni.showModal({
					title: t.$t('温馨提示'),
					content: t.$t('确定要解绑谷歌验证器吗？'),
					cancelText: t.$t('取消'),
					confirmText: t.$t('确认'),
					confirmColor: '#F56C6C',
					success: function(res) {
						if (res.confirm) {
							t.req({
								url: "user/unbindGoogleAuth",
								data: {
									code: t.verifyCode
								},
								Loading: true,
								success: function(res) {
									if (res.code === 1) {
										uni.showToast({
											title: t.$t('解绑成功'),
											icon: 'success'
										});
										t.isBound = false;
										t.verifyCode = '';
										// 重新获取新的密钥和二维码
										t.loadGoogleAuthStatus();
									} else {
										t.$toast(res.msg || t.$t('解绑失败'));
									}
								}
							});
						}
					}
				});
			}
		}
	}
</script>

<style>
.google-auth-container {
	background-color: #1A1A1A;
	min-height: 100vh;
}

/* 导航栏样式 */
.navbar {
	display: flex;
	align-items: center;
	justify-content: space-between;
	height: 90rpx;
	background-color: #2C2C2C;
	padding: 0 30rpx;
	box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.2);
}

.back-icon {
	width: 40rpx;
	height: 40rpx;
}

.page-title {
	font-size: 36rpx;
	font-weight: bold;
	color: #FFFFFF;
}

.navbar-right {
	width: 60rpx;
}

/* 表单卡片样式 */
.form-card {
	background-color: #2C2C2C;
	margin: 30rpx;
	border-radius: 20rpx;
	padding: 30rpx;
	box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.2);
}

/* 已绑定状态 */
.bound-status {
	display: flex;
	flex-direction: column;
	align-items: center;
	padding: 40rpx 0;
}

.status-icon-wrapper {
	width: 120rpx;
	height: 120rpx;
	border-radius: 60rpx;
	background-color: #67C23A;
	display: flex;
	align-items: center;
	justify-content: center;
	margin-bottom: 20rpx;
}

.status-icon {
	font-size: 60rpx;
	color: #FFFFFF;
}

.bound-text {
	font-size: 32rpx;
	color: #67C23A;
	font-weight: bold;
}

/* 步骤区域 */
.step-section {
	margin-bottom: 40rpx;
	padding-bottom: 30rpx;
	border-bottom: 1px solid #3A3A3A;
}

.step-section:last-of-type {
	border-bottom: none;
	margin-bottom: 20rpx;
}

.step-header {
	display: flex;
	align-items: center;
	margin-bottom: 20rpx;
}

.step-number {
	width: 50rpx;
	height: 50rpx;
	border-radius: 25rpx;
	background-color: #40DFBF;
	color: #FFFFFF;
	font-size: 28rpx;
	font-weight: bold;
	display: flex;
	align-items: center;
	justify-content: center;
	margin-right: 20rpx;
}

.step-title {
	font-size: 30rpx;
	color: #FFFFFF;
	font-weight: bold;
}

/* 下载按钮 */
.download-buttons {
	display: flex;
	justify-content: space-around;
	margin-top: 20rpx;
}

.download-btn {
	display: flex;
	flex-direction: column;
	align-items: center;
	padding: 20rpx 30rpx;
	background-color: #3A3A3A;
	border-radius: 12rpx;
	min-width: 200rpx;
}

.store-icon {
	width: 220rpx;
	height: 60rpx;
	margin-bottom: 10rpx;
}

.download-text {
	font-size: 24rpx;
	color: #CCCCCC;
}

/* 二维码区域 */
.qrcode-wrapper {
	display: flex;
	justify-content: center;
	margin: 30rpx 0;
}

.qrcode-image {
	width: 300rpx;
	height: 300rpx;
	background-color: #FFFFFF;
	border-radius: 12rpx;
}

.qrcode-placeholder {
	width: 300rpx;
	height: 300rpx;
	background-color: #3A3A3A;
	border-radius: 12rpx;
	display: flex;
	align-items: center;
	justify-content: center;
}

.placeholder-text {
	font-size: 28rpx;
	color: #999999;
}

/* 密钥区域 */
.secret-key-section {
	display: flex;
	align-items: center;
	justify-content: center;
	flex-wrap: wrap;
	padding: 20rpx;
	background-color: #3A3A3A;
	border-radius: 12rpx;
}

.secret-label {
	font-size: 26rpx;
	color: #CCCCCC;
	margin-right: 10rpx;
}

.secret-key {
	font-size: 26rpx;
	color: #40DFBF;
	font-family: monospace;
	word-break: break-all;
	margin-right: 20rpx;
}

.copy-btn {
	padding: 10rpx 20rpx;
	background-color: #40DFBF;
	border-radius: 8rpx;
}

.copy-text {
	font-size: 24rpx;
	color: #FFFFFF;
}

/* 表单区域 */
.form-section {
	margin-bottom: 20rpx;
}

.section-title {
	font-size: 28rpx;
	color: #CCCCCC;
	margin-bottom: 15rpx;
	display: block;
}

.input-wrapper {
	position: relative;
}

.input-field {
	width: 100%;
	height: 100rpx;
	background-color: #3A3A3A;
	border-radius: 12rpx;
	padding: 0 30rpx;
	font-size: 36rpx;
	box-sizing: border-box;
	color: #FFFFFF;
	border: 1px solid #444444;
	text-align: center;
	letter-spacing: 20rpx;
}

/* 提交按钮 */
.submit-button {
	height: 100rpx;
	background-color: #40DFBF;
	border-radius: 12rpx;
	color: #FFFFFF;
	font-size: 32rpx;
	font-weight: bold;
	display: flex;
	justify-content: center;
	align-items: center;
	margin-top: 30rpx;
}

.unbind-button {
	background-color: #F56C6C;
}

/* 信息卡片样式 */
.info-card {
	background-color: #2C2C2C;
	margin: 30rpx;
	border-radius: 20rpx;
	padding: 30rpx;
	box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.2);
}

.info-header {
	display: flex;
	align-items: center;
	margin-bottom: 20rpx;
}

.info-icon {
	font-size: 32rpx;
	color: #40DFBF;
	margin-right: 10rpx;
}

.info-title {
	font-size: 30rpx;
	font-weight: bold;
	color: #FFFFFF;
}

.info-content {
	display: flex;
	flex-direction: column;
}

.info-item {
	font-size: 26rpx;
	color: #CCCCCC;
	margin-bottom: 10rpx;
	line-height: 1.5;
}
</style>
