<template>
	<view class="deposit-container">
		<!-- 顶部导航栏 -->
		<view class="navbar">
			<view class="back-btn" @tap="$goBack()">
				<image src="/static/images/icons/back-arrow.svg" class="back-icon"></image>
			</view>
			<text class="page-title">{{ $t('充值') }}</text>
			<view class="navbar-right"></view>
		</view>

		<!-- 充值内容 -->
		<view class="deposit-content">
			<!-- 二维码显示区域 -->
			<view class="qrcode-box">
				<image v-if="qrcodeDataUrl" :src="qrcodeDataUrl" class="qrcode-image" mode="aspectFit"></image>
				<view v-else class="qrcode-placeholder">
					<text class="placeholder-text">{{ $t('加载中...') }}</text>
				</view>
			</view>

			<!-- 交易信息 -->
			<view class="info-card">
				<view class="info-item">
					<text class="info-label">{{ $t('时间') }}</text>
					<text class="info-value">{{ currentTime }}</text>
				</view>

				<view class="info-item">
					<text class="info-label">{{ $t('交易类型') }}</text>
					<text class="info-value">USDT (TRC20)</text>
				</view>

				<view class="info-item">
					<text class="info-label">{{ $t('数量') }}</text>
					<input v-model="amount" type="number" class="input-control" :placeholder="$t('请输入数量')" />
				</view>

				<view class="info-item address-item">
					<text class="info-label">{{ $t('地址') }}</text>
					<view class="address-container">
						<text class="address-text">{{ show_address }}</text>
						<button class="copy-btn" @tap="copyText()">{{ $t('复制') }}</button>
					</view>
				</view>
			</view>

			<!-- 上传转账截图 -->
			<view class="upload-section">
				<view class="section-title">
					<text>{{ $t('上传转账截图') }}</text>
				</view>

				<view class="upload-container">
					<view class="preview-image" v-if="tempFilePath">
						<image :src="tempFilePath" mode="aspectFit" class="uploaded-image"></image>
						<view class="delete-icon" @tap="removeImage">
							<text class="close-icon">×</text>
						</view>
					</view>

					<view v-if="!tempFilePath" class="upload-button" @tap="chooseImage">
						<text class="upload-icon">+</text>
						<text class="upload-text">{{ $t('上传转账截图') }}</text>
					</view>

					<view class="upload-progress" v-if="uploading">
						<progress :percent="uploadProgress" stroke-width="4" active-color="#40DFBF" background-color="#E0E0E0" />
						<text class="progress-text">{{uploadProgress}}%</text>
					</view>
				</view>
			</view>

			<!-- 提交按钮 -->
			<button class="submit-btn" @tap="submit()">{{ $t('提交') }}</button>
		</view>
	</view>
</template>

<script>
	import QRCode from 'qrcode';
	export default {
		data: function() {
			return {
				show_address: '',
				amount: '',
				currentTime: '',
				qrcodeDataUrl: '',
				// 上传图片相关变量
				tempFilePath: '',
				uploadedImageUrl: '',
				uploading: false,
				uploadProgress: 0
			}
		},
		onLoad: function() {
			var userid = uni.getStorageSync('userid');
			if (!userid) this.$gopage("/pages/login/index");
			this.loadData();
		},
		mounted() {
			var t = this;
			t.updateTime();
			setInterval(t.updateTime, 1000);
		},
		methods: {
			// 选择图片
			chooseImage() {
				uni.chooseImage({
					count: 1,
					sizeType: ['compressed'],
					sourceType: ['album', 'camera'],
					success: (res) => {
						this.tempFilePath = res.tempFilePaths[0];
					},
					fail: (err) => {
						uni.showToast({
							title: this.$t('选择图片失败'),
							icon: 'none'
						});
					}
				});
			},

			// 删除已选择的图片
			removeImage() {
				this.tempFilePath = '';
				this.uploadedImageUrl = '';
			},

			// 上传图片到服务器
			uploadImage() {
				if (!this.tempFilePath) {
					uni.showToast({
						title: this.$t('请先选择图片'),
						icon: 'none'
					});
					return false;
				}

				this.uploading = true;
				this.uploadProgress = 0;

				const uploadTask = uni.uploadFile({
					url: this.siteBaseUrl + 'recharge/uploadimg',
					filePath: this.tempFilePath,
					name: 'img',
					header: {
						'token': uni.getStorageSync('token')
					},
					success: (res) => {
						let data;
						try {
							data = JSON.parse(res.data);
						} catch (e) {
							data = res.data;
						}
						if (data.status === "ok") {
							this.uploadedImageUrl = data.data || '';
							return true;
						} else {
							uni.showToast({
								title: data.data || this.$t('上传失败'),
								icon: 'none'
							});
							return false;
						}
					},
					fail: (err) => {
						uni.showToast({
							title: this.$t('上传失败，请重试'),
							icon: 'none'
						});
						return false;
					},
					complete: () => {
						this.uploading = false;
						return true;
					}
				});

				// 监听上传进度
				uploadTask.onProgressUpdate((res) => {
					this.uploadProgress = res.progress;
				});

				return uploadTask;
			},

			updateTime() {
				const currentDate = new Date();
				const year = currentDate.getFullYear();
				const month = String(currentDate.getMonth() + 1).padStart(2, '0');
				const day = String(currentDate.getDate()).padStart(2, '0');
				const time = currentDate.toLocaleTimeString();
				this.currentTime = `${year}/${month}/${day} ${time}`;
			},

			submit() {
				var t = this;
				if (!t.amount || t.amount == '') {
					return t.$toast(t.$t('请输入数量'));
				}

				if (!this.tempFilePath) {
					uni.showToast({
						title: this.$t('请上传转账截图'),
						icon: "none"
					});
					return;
				}

				uni.showLoading({
					title: this.$t('上传中...')
				});

				const uploadTask = this.uploadImage();
				if (uploadTask) {
					setTimeout(() => {
						if (this.uploadedImageUrl) {
							this.submitForm();
						} else {
							uni.hideLoading();
							uni.showToast({
								title: this.$t('上传失败'),
								icon: 'none'
							});
						}
					}, 1500);
				}
			},

			// 提交表单
			submitForm() {
				var t = this;
				t.req({
					url: "recharge/depositdetail",
					data: {
						'count': t.amount,
						'imgurl': t.uploadedImageUrl,
						'type': 1,
						'address': t.show_address
					},
					Loading: false,
					success: function(i) {
						if (i.code == 0) {
							return t.$toast(t.$t(i.msg));
						}
						t.$toast(t.$t('成功'));
						t.$gopage("/pages/wallet/index");
					},
					complete: function() {
						uni.hideLoading();
					}
				});
			},

			loadData() {
				var t = this;
				t.req({
					url: "recharge/index",
					method: "POST",
					Loading: true,
					success: function(res) {
						console.log('recharge/index返回数据:', res);
						if (res.code === 1 && res.data) {
							// 后端返回的是 usdt_address
							t.show_address = res.data.usdt_address || '';
							// 生成二维码
							if (t.show_address) {
								t.generateQRCode(t.show_address);
							}
						}
					},
					fail: function(err) {
						console.error('加载充值地址失败:', err);
					}
				})
			},

			// 生成二维码
			generateQRCode(address) {
				var t = this;
				if (!address) {
					t.qrcodeDataUrl = '';
					return;
				}
				QRCode.toDataURL(address, {
					width: 200,
					margin: 2,
					color: {
						dark: '#000000',
						light: '#FFFFFF'
					}
				}).then((url) => {
					t.qrcodeDataUrl = url;
				}).catch((error) => {
					console.error('QRCode生成失败:', error);
					t.qrcodeDataUrl = '';
				});
			},

			copyText() {
				var t = this;
				if (!t.show_address) {
					t.$toast(t.$t('地址为空'));
					return;
				}
				uni.setClipboardData({
					data: t.show_address,
					success: () => {
						t.$toast(t.$t('复制成功'));
					},
					fail: () => {
						// 兼容H5的复制方式
						if (navigator.clipboard) {
							navigator.clipboard.writeText(t.show_address)
								.then(() => {
									t.$toast(t.$t('复制成功'));
								})
								.catch((error) => {
									console.error('复制失败:', error);
								});
						}
					}
				});
			},
		},
	}
</script>

<style>
.deposit-container {
	background-color: #1A1A1A;
	min-height: 100vh;
}

/* 导航栏样式 */
.navbar {
	display: flex;
	align-items: center;
	justify-content: space-between;
	height: 90rpx;
	background-color: #1A1A1A;
	padding: 0 30rpx;
	box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.2);
}

.page-title {
	font-size: 36rpx;
	font-weight: bold;
	color: #FFFFFF;
	text-align: center;
}

.navbar-right {
	width: 60rpx;
}

.back-btn {
	width: 60rpx;
	height: 60rpx;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 40rpx;
}

/* 充值内容样式 */
.deposit-content {
	padding: 30rpx;
}

/* 二维码样式 */
.qrcode-box {
	background-color: #2C2C2C;
	border-radius: 20rpx;
	margin-bottom: 20rpx;
	padding: 40rpx;
	display: flex;
	justify-content: center;
	align-items: center;
	box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.2);
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

/* 信息卡片样式 */
.info-card {
	background-color: #2C2C2C;
	border-radius: 20rpx;
	padding: 20rpx;
	margin-bottom: 20rpx;
	box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.2);
}

.info-item {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 15rpx 0;
	border-bottom: 1px solid #3A3A3A;
}

.info-item:last-child {
	border-bottom: none;
}

.info-label {
	font-size: 28rpx;
	color: #CCCCCC;
}

.info-value {
	font-size: 28rpx;
	color: #FFFFFF;
	font-weight: 500;
}

.input-control {
	background-color: #3A3A3A;
	border: 1px solid #4A4A4A;
	border-radius: 6rpx;
	padding: 10rpx 20rpx;
	font-size: 28rpx;
	color: #FFFFFF;
	text-align: right;
	width: 200rpx;
}

.address-item {
	flex-direction: column;
	align-items: flex-start;
}

.address-container {
	display: flex;
	align-items: center;
	width: 100%;
	margin-top: 10rpx;
}

.address-text {
	font-size: 24rpx;
	color: #FFFFFF;
	word-break: break-all;
	flex: 1;
	background-color: #3A3A3A;
	padding: 10rpx;
	border-radius: 6rpx;
}

.copy-btn {
	background-color: #40DFBF;
	color: #FFFFFF;
	font-size: 24rpx;
	padding: 6rpx 20rpx;
	border-radius: 30rpx;
	margin-left: 10rpx;
	line-height: 50rpx;
	min-width: 100rpx;
}

/* 上传区域样式 */
.upload-section {
	background-color: #2C2C2C;
	border-radius: 20rpx;
	padding: 20rpx;
	margin-bottom: 20rpx;
	box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.2);
}

.section-title {
	font-size: 28rpx;
	font-weight: bold;
	color: #FFFFFF;
	margin-bottom: 20rpx;
}

.upload-container {
	display: flex;
	flex-direction: column;
	align-items: flex-start;
}

.preview-image {
	position: relative;
	width: 200rpx;
	height: 200rpx;
	border-radius: 8rpx;
	overflow: hidden;
	margin-bottom: 20rpx;
}

.uploaded-image {
	width: 100%;
	height: 100%;
	object-fit: cover;
}

.delete-icon {
	position: absolute;
	top: 10rpx;
	right: 10rpx;
	width: 40rpx;
	height: 40rpx;
	border-radius: 20rpx;
	background-color: rgba(0, 0, 0, 0.6);
	display: flex;
	justify-content: center;
	align-items: center;
}

.close-icon {
	color: #FFFFFF;
	font-size: 28rpx;
}

.upload-button {
	width: 200rpx;
	height: 200rpx;
	border-radius: 8rpx;
	background-color: #3A3A3A;
	border: 1px dashed #4A4A4A;
	display: flex;
	flex-direction: column;
	justify-content: center;
	align-items: center;
}

.upload-icon {
	font-size: 60rpx;
	color: #999999;
	margin-bottom: 10rpx;
}

.upload-text {
	font-size: 24rpx;
	color: #999999;
	text-align: center;
}

.upload-progress {
	width: 100%;
	margin-top: 20rpx;
}

.progress-text {
	font-size: 24rpx;
	color: #999999;
	margin-top: 10rpx;
	text-align: center;
}

/* 提交按钮样式 */
.submit-btn {
	background-color: #40DFBF;
	color: #FFFFFF;
	font-size: 32rpx;
	padding: 20rpx 0;
	border-radius: 10rpx;
	text-align: center;
	width: 100%;
	margin-top: 30rpx;
}
</style>
