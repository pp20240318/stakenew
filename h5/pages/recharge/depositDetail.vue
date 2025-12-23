<template>
	<view class="deposit-container">
		<!-- 顶部导航栏 -->
		<view class="navbar">
			<view class="back-btn" @tap="$goBack()">
				<image src="/static/images/icons/back-arrow.svg" class="back-icon"></image>
			</view>
			<text class="page-title">{{ $t('充值明细') }}</text>
			 
			<view class="navbar-right"></view>
		</view>
		
		<!-- 充值内容 -->
		<view class="deposit-content">
			<!-- 二维码显示区域 -->
			<view class="qrcode-box">
				<view id="qrcode" class="qrcode"></view>
			</view>
			
			<!-- 交易信息 -->
			<view class="info-card">
				<view class="info-item">
					<text class="info-label">{{ $t('时间') }}</text>
					<text class="info-value">{{ currentTime }}</text>
				</view>
				
				<view class="info-item">
					<text class="info-label">{{ $t('交易类型') }}</text>
					<view class="type-select">
						<select v-model="selecttype" @change="changetype()" class="select-control">
							<option value="1" class="option-item">USDT</option> 
							<option value="5" class="option-item">MYR</option> 
							<!--
							<option value="2" class="option-item">BTC</option>
							<option value="4" class="option-item">ETH</option>-->
						</select>
					</view>
				</view>
				
				
				
				<view class="info-item">
					<text class="info-label">{{ $t('数量') }}</text>
					<input v-model="erc_count" type="number" class="input-control"  />
				</view>
				
				<view class="info-item address-item">
					<text class="info-label">{{ $t('地址') }}</text>
					<view class="address-container">
						<text class="address-text">{{show_address}}</text>
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
						<progress :percent="uploadProgress" stroke-width="4" active-color="#007AFF" background-color="#E0E0E0" />
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
				show_address:'',
				erc_address:'',
				btc_address:'',
				ETH_address:'',
				usdc_address:'',
				erc_addressur:'',
				erc_count:'',
				currentTime:'',
				selecttype:1,
				show_percent:'1',
				show_name:'USDT',
				usdt_percent:'',
				btc_percent:'',
				eth_percent:'',
				usdc_percent:'',
				// 上传图片相关变量
				tempFilePath: '', // 临时文件路径
				uploadedImageUrl: '', // 上传后的图片URL
				uploading: false, // 是否正在上传
				uploadProgress: 0 // 上传进度
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
		   var t = this; 
	       t.updateTime();
	       setInterval(t.updateTime, 1000); // 每秒更新一次时间
		},
		methods: {
			// 选择图片
			chooseImage() {
				uni.chooseImage({
					count: 1, // 最多可以选择的图片张数
					sizeType: ['compressed'], // 可以指定是原图还是压缩图，默认二者都有
					sourceType: ['album', 'camera'], // 从相册选择或使用相机拍照
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
				if (!t.erc_count || t.erc_count == '') {
					return t.$toast(t.$t('请输入数量'));
				}
				
				// 判断图片选择
				if (!this.tempFilePath) {
					uni.showToast({
						title: this.$t('请上传转账截图'), 
						icon: "none"
					}); 
					return;
				}
				
				// 先上传图片
				uni.showLoading({
					title: this.$t('上传中...')
				});
				
				const uploadTask = this.uploadImage();
				if (uploadTask) {
					uploadTask.then && uploadTask.then ? 
					uploadTask.then((res) => {
						// 上传成功，提交表单
						this.submitForm();
					}) : 
					// 兼容旧的上传方式
					setTimeout(() => {
						if (this.uploadedImageUrl) {
							// 图片上传成功后提交表单
							this.submitForm();
						} else {
							uni.hideLoading();
							uni.showToast({
								title: this.$t('上传失败'),
								icon: 'none'
							});
						}
					}, 1000);
				}
			},
			
			// 提交表单
			submitForm() {
				var t = this;
				t.req({
					url: "recharge/depositdetail", 
					data: { 
						'count': t.erc_count,
						'imgurl': this.uploadedImageUrl,
						'type': t.selecttype,
						'address': t.show_address
					},
					Loading: !1,
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
	        loadData(){
	        	var t = this; 
	        	t.req({
	        		url: "recharge/index",  
					method: "POST", 
					Loading: !1,
	        		success: function (i) {  
	        			 t.erc_address=i.data.erc_address;
						 t.ETH_address=i.data.ETH_address;
						 t.btc_address=i.data.btc_address;
						 t.usdc_address=i.data.usdc_address;
						 t.show_address=i.data.erc_address;
						 t.usdt_percent=i.data.usdt_percent;
						 t.btc_percent=i.data.btc_percent;
						 t.eth_percent=i.data.eth_percent;
						 t.usdc_percent=i.data.usdc_percent;
						 var qrCodeCanvas =QRCode.toCanvas(t.erc_address)
						 .then((canvas) => {
							 document.getElementById('qrcode').innerHTML = '';
						           document.getElementById('qrcode').append(canvas); 
						         })
						         .catch((error) => {
						           console.error(error);
						         });;
	        		}
	        	})
	        } ,
			changetype(){
				var t = this; 
				var thisaddress="";
				var thispercent="",thisname=""; 
				if(t.selecttype==1){
					thisaddress=t.erc_address;
					thispercent=t.usdt_percent;
					thisname="USDT";
				}else if(t.selecttype==2){
					thisaddress=t.btc_address;
					thispercent=t.btc_percent;
					thisname="BTC";
				}else if(t.selecttype==5){
					thisaddress=t.usdc_address;
					thispercent=t.usdc_percent;
					thisname="USDC";
				}
				else{
					thisaddress=t.ETH_address;
					thispercent=t.eth_percent;
					thisname="ETH";
				}
				t.show_name=thisname;
				t.show_percent=thispercent;
				t.show_address=thisaddress;
				var qrCodeCanvas =QRCode.toCanvas(thisaddress)
				.then((canvas) => {
					document.getElementById('qrcode').innerHTML = '';
				          document.getElementById('qrcode').append(canvas); 
				        })
				        .catch((error) => {
				          console.error(error);
				        });;
			},
			copyText(){
				var t = this;
				var thisaddress="";
				if(t.selecttype==1){
					thisaddress=t.erc_address;
				}else if(t.selecttype==2){
					thisaddress=t.btc_address;
				}else if(t.selecttype==5){
					thisaddress=t.usdc_address;
				}else{
					thisaddress=t.ETH_address;
				}
				 navigator.clipboard.writeText(thisaddress)
				        .then(() => {
				          t.$toast(t.$t('复制成功')); 
				        })
				        .catch((error) => { 
				        });
			} ,
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
 
.ri-arrow-left-line:before {
	content: "←";
}

.page-title {
	font-size: 36rpx;
	font-weight: bold;
	color: #FFFFFF;
	text-align:center;
}

.navbar-right {
	font-size: 28rpx;
	color: #007AFF;
}

.records-btn {
	font-size: 28rpx;
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

.qrcode {
	margin: 0 auto;
	text-align: center;
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

.type-select {
	max-width: 50%;
}

.select-control {
	background-color: #3A3A3A;
	border: 1px solid #4A4A4A;
	border-radius: 6rpx;
	padding: 10rpx 20rpx;
	font-size: 28rpx;
	color: #FFFFFF;
	width: 200rpx;
}

.option-item {
	font-size: 28rpx;
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
	background-color: #007AFF;
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
	background-color: #007AFF;
	color: #FFFFFF;
	font-size: 32rpx;
	padding: 20rpx 0;
	border-radius: 10rpx;
	text-align: center;
	width: 100%;
	margin-top: 30rpx;
}

.back-btn {
	width: 60rpx;
	height: 60rpx;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 40rpx;
}

</style>