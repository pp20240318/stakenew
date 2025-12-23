<template>
	<view class="truename-container">
		<!-- 顶部导航栏 -->
		<view class="navbar">
			<view   @tap="$goBack()">
				<image src="/static/images/icons/back-arrow.svg" class="back-icon"></image>
			</view>
			 
			<text class="page-title">{{ $t('实名认证') }}</text>
			<view class="navbar-right"></view>
		</view>
		
		<!-- 认证表单卡片 -->
		<view class="form-card">
			<view class="form-section">
				<text class="section-title">{{ $t('真实姓名') }}</text>
				<view class="input-wrapper">
					<input 
						v-if="!isReadOnly" 
						v-model="realname" 
						type="text" 
						:placeholder="$t('请输入真实姓名')" 
						class="input-field"
					/>
					<view v-else class="readonly-field">{{ realname }}</view>
				</view>
			</view>
			
			<view class="form-section">
				<text class="section-title">{{ $t('证件号') }}</text>
				<view class="input-wrapper">
					<input 
						v-if="!isReadOnly" 
						v-model="certificate" 
						type="text" 
						:placeholder="$t('请输入证件号')" 
						class="input-field"
					/>
					<view v-else class="readonly-field">{{ certificate }}</view>
				</view>
			</view>
			
			<!-- 添加护照图片上传功能 -->
			<view class="form-section">
				<text class="section-title">{{ $t('证件照片') }}</text>
				<view class="upload-container" v-if="!isReadOnly">
					<view class="preview-image" v-if="passportImage">
						<image :src="passportImage" mode="aspectFit" class="uploaded-image"></image>
						<view class="delete-icon" @tap="removeImage">
							<text class="close-icon">×</text>
						</view>
					</view>
					
					<view v-if="!passportImage" class="upload-button" @tap="chooseImage">
						<text class="upload-icon">+</text>
						<text class="upload-text">{{ $t('上传证件照片') }}</text>
					</view>
					
					<view class="upload-progress" v-if="uploading">
						<progress :percent="uploadProgress" stroke-width="4" active-color="#007AFF" background-color="#E0E0E0" />
						<text class="progress-text">{{uploadProgress}}%</text>
					</view>
				</view>
				<view class="image-preview" v-else-if="passportImage">
					<image :src="passportImage" mode="aspectFit" class="preview-readonly-image"></image>
				</view>
			</view>
			
			<view class="status-section" v-if="showtxt">
				<view class="status-badge" :class="{
					'status-pending': showtxt === '开始验证',
					'status-success': showtxt === '已验证',
					'status-failed': showtxt === '验证失败'
				}">
					{{ $t(showtxt) }}
				</view>
			</view>
			
			<button 
				v-if="!isReadOnly" 
				class="submit-button" 
				@tap="submit()"
			>
				{{ $t('提交') }}
			</button>
		</view>
		
		<!-- 实名认证说明 -->
		<view class="info-card">
			<view class="info-header">
				<text class="info-icon ri-information-line"></text>
				<text class="info-title">{{ $t('实名认证说明') }}</text>
			</view>
			<view class="info-content">
				<text class="info-item">· {{ $t('请确保填写的姓名与证件一致') }}</text> 
				<text class="info-item">· {{ $t('认证信息仅用于身份验证，保障账户安全') }}</text> 
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		data: function() {
			return {
				realname:'',
				certificate:'' ,
				isReadOnly: false,
				showtxt: '',
				passportImage: '',
				uploading: false,
				uploadProgress: 0,
				uploadedImageUrl: ''
			}
		},
		onReady: function() {
	       
		},
		onLoad: function() {
		  var userid=uni.getStorageSync('userid');
		  if(!userid) this.$gopage("/pages/login/index");   
	      this.loadData();
		},
		onShow: function() {
	       
		},
		mounted() {  
		},
		methods: { 
			// 选择图片
			chooseImage() {
				uni.chooseImage({
					count: 1, // 最多可以选择的图片张数
					sizeType: ['compressed'], // 可以指定是原图还是压缩图，默认二者都有
					sourceType: ['album', 'camera'], // 从相册选择或使用相机拍照
					success: (res) => {
						this.passportImage = res.tempFilePaths[0];
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
				this.passportImage = '';
				this.uploadedImageUrl = '';
			},
			
			// 上传图片到服务器
			uploadImage() {
				if (!this.passportImage) {
					uni.showToast({
						title: this.$t('请先选择证件照片'),
						icon: 'none'
					});
					return false;
				}
				
				this.uploading = true;
				this.uploadProgress = 0;
				
				const uploadTask = uni.uploadFile({
					url: this.siteBaseUrl + 'user/uploadimg',
					filePath: this.passportImage,
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
					}
				});
				
				// 监听上传进度
				uploadTask.onProgressUpdate((res) => {
					this.uploadProgress = res.progress;
				});
				
				return uploadTask;
			},
			
			submit(){
				var t=this;//是否提交审核
				if(!t.realname || t.realname==''){
					return t.$toast(t.$t('请输入真实姓名'));
				}
				if(!t.certificate || t.certificate==''){
					return t.$toast(t.$t('请输入证件号'));
				}
				if(!t.passportImage) {
					return t.$toast(t.$t('请上传证件照片'));
				}
				
				uni.showModal({
					title: t.$t('温馨提示'),
					content: t.$t('是否提交审核'),
					cancelText: t.$t('取消'),
					confirmText: t.$t('确认'),
					confirmColor: '#007AFF',
					success: function(res) {
						if (res.confirm) {
							// 先上传图片
							uni.showLoading({
								title: t.$t('上传中...')
							});
							
							const uploadTask = t.uploadImage();
							if (uploadTask) {
								uploadTask.then && uploadTask.then ? 
								uploadTask.then((res) => {
									// 上传成功，提交表单
									t.submitForm();
								}) : 
								// 兼容旧的上传方式
								setTimeout(() => {
									if (t.uploadedImageUrl) {
										// 图片上传成功后提交表单
										t.submitForm();
									} else {
										uni.hideLoading();
										uni.showToast({
											title: t.$t('上传失败'),
											icon: 'none'
										});
									}
								}, 1000);
							}
						} else if (res.cancel) {  
							// 用户取消
						}
					}
				});
			},
			
			// 提交表单
			submitForm() {
				var t = this;
				var userid=uni.getStorageSync('userid');
				t.req({
					url: "user/savetruename",
					data: {
						'userid': userid,
						'name': t.realname,
						'certificate': t.certificate,
						'passport_image': t.uploadedImageUrl
					},
					Loading: !1,
					success: function (i) { 
						if(i.code==0){
							return t.$toast(t.$t(i.msg));
						} 
						t.$toast(t.$t('成功'));
						t.showtxt="开始验证";
						t.isReadOnly=true;  
					},
					complete: function() {
						uni.hideLoading();
					}
				});
			},
	        loadData(){
	        	var t = this; 
				var userid=uni.getStorageSync('userid');
	        	t.req({
	        		url: "user/truename", 
	        		Loading: !1,
					data:{'userid':userid},
	        		success: function (i) {  
	        			 t.certificate=i.data.certificate;
						 t.realname=i.data.name;
						 t.passportImage=i.data.passport_image || '';
						 t.isReadOnly=true;
						 if(i.data.status=='3')  t.showtxt="开始验证";
						 else if(i.data.status=='1')  t.showtxt="已验证"; 
						 else if(i.data.status=='2') {t.showtxt="验证失败"; t.isReadOnly=false;} 
						 else {t.isReadOnly=false;t.showtxt="";}
	        		}
	        	})
	        } 
		},
	}
</script>

<style>
.truename-container {
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

.back-btn {
	width: 60rpx;
	height: 60rpx;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 40rpx;
	color: #FFFFFF;
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

.form-section {
	margin-bottom: 30rpx;
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
	font-size: 30rpx;
	box-sizing: border-box;
	color: #FFFFFF;
	border: 1px solid #444444;
}

.readonly-field {
	width: 100%;
	height: 100rpx;
	background-color: #3A3A3A;
	border-radius: 12rpx;
	padding: 0 30rpx;
	font-size: 30rpx;
	box-sizing: border-box;
	color: #FFFFFF;
	border: 1px solid #444444;
	line-height: 100rpx;
}

.status-section {
	margin-bottom: 30rpx;
	display: flex;
	justify-content: center;
}

.status-badge {
	display: inline-block;
	padding: 10rpx 30rpx;
	border-radius: 30rpx;
	font-size: 26rpx;
	color: #FFFFFF;
	background-color: #909399;
}

.status-pending {
	background-color: #E6A23C;
}

.status-success {
	background-color: #67C23A;
}

.status-failed {
	background-color: #F56C6C;
}

.submit-button {
	height: 100rpx;
	background-color: #007AFF;
	border-radius: 12rpx;
	color: #FFFFFF;
	font-size: 32rpx;
	font-weight: bold;
	display: flex;
	justify-content: center;
	align-items: center;
	margin-top: 30rpx;
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
	color: #007AFF;
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

/* 上传功能样式 */
.upload-container {
	display: flex;
	align-items: center;
	justify-content: center;
	margin-bottom: 30rpx;
}

.preview-image {
	position: relative;
	margin-right: 20rpx;
}

.uploaded-image {
	width: 100rpx;
	height: 100rpx;
	border-radius: 12rpx;
}

.delete-icon {
	position: absolute;
	top: 0;
	right: 0;
	width: 20rpx;
	height: 20rpx;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 20rpx;
	color: #FFFFFF;
}

.upload-button {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 160rpx;
	height: 100rpx;
	background-color: #3A3A3A;
	border-radius: 12rpx;
	font-size: 32rpx;
	color: #FFFFFF;
}

.upload-progress {
	width: 160rpx;
	height: 10rpx;
	background-color: #3A3A3A;
	border-radius: 5rpx;
	margin-top: 10rpx;
}

.progress-text {
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	text-align: center;
	font-size: 26rpx;
	color: #FFFFFF;
}

.image-preview {
	display: flex;
	align-items: center;
	justify-content: center;
	margin-bottom: 30rpx;
}

.preview-readonly-image {
	width: 100rpx;
	height: 100rpx;
	border-radius: 12rpx;
}
</style>