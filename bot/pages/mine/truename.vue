<template>
	<view class="truename-container">
		<!-- 顶部导航栏 -->
		<view class="navbar">
			<view class="back-btn" @tap="$goBack()">
				<text class="ri-arrow-left-line"></text>
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
			
			<view class="status-section" v-if="showtxt">
				<view class="status-badge" :class="{
					'status-pending': showtxt === 'verifing',
					'status-success': showtxt === 'verified',
					'status-failed': showtxt === 'fail'
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
				showtxt:''
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
			submit(){
				var t=this;//是否提交审核
				if(!t.realname||t.realname==''){
					return t.$toast(t.$t('请输入真实姓名'));
				}
				if(!t.certificate||t.certificate==''){
					return t.$toast(t.$t('请输入证件号'));
				}
				uni.showModal({
					title: t.$t('温馨提示'),
					content: t.$t('是否提交审核'),
					cancelText: t.$t('取消'),
					confirmText: t.$t('确认'),
					confirmColor: '#007AFF',
					success: function(res) {
						if (res.confirm) {
							var userid=uni.getStorageSync('userid');
							t.req({
								url: "user/savetruename",
								data: {'userid':userid,name:t.realname,'certificate':t.certificate},
								Loading: !1,
								success: function (i) { 
									if(i.code==0){
										return t.$toast(t.$t(i.msg));
									} 
									t.$toast(t.$t('成功'));
									t.showtxt="verifing";
									t.isReadOnly=true;  
								}
							}) 
						
						} else if (res.cancel) {  
						}
					}
				});
				
			} ,
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
						 t.isReadOnly=true;
						 if(i.data.status=='3')  t.showtxt="verifing";
						 else if(i.data.status=='1')  t.showtxt="verified"; 
						 else if(i.data.status=='2') {t.showtxt="fail"; t.isReadOnly=false;} 
						 else {t.isReadOnly=false;t.showtxt="";}
	        		}
	        	})
	        } 
		},
	}
</script>

<style>
.truename-container {
	background-color: #F5F5F5;
	min-height: 100vh;
}

/* 导航栏样式 */
.navbar {
	display: flex;
	align-items: center;
	justify-content: space-between;
	height: 90rpx;
	background-color: #FFFFFF;
	padding: 0 30rpx;
	box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
}

.back-btn {
	width: 60rpx;
	height: 60rpx;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 40rpx;
}

.page-title {
	font-size: 36rpx;
	font-weight: bold;
	color: #333333;
}

.navbar-right {
	width: 60rpx;
}

/* 表单卡片样式 */
.form-card {
	background-color: #FFFFFF;
	margin: 30rpx;
	border-radius: 20rpx;
	padding: 30rpx;
	box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.05);
}

.form-section {
	margin-bottom: 30rpx;
}

.section-title {
	font-size: 28rpx;
	color: #666666;
	margin-bottom: 15rpx;
	display: block;
}

.input-wrapper {
	position: relative;
}

.input-field {
	width: 100%;
	height: 100rpx;
	background-color: #F8F8F8;
	border-radius: 12rpx;
	padding: 0 30rpx;
	font-size: 30rpx;
	box-sizing: border-box;
	color: #333333;
	border: 1px solid #EEEEEE;
}

.readonly-field {
	width: 100%;
	height: 100rpx;
	background-color: #F8F8F8;
	border-radius: 12rpx;
	padding: 0 30rpx;
	font-size: 30rpx;
	box-sizing: border-box;
	color: #333333;
	border: 1px solid #EEEEEE;
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
	background-color: #FFFFFF;
	margin: 30rpx;
	border-radius: 20rpx;
	padding: 30rpx;
	box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.05);
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
	color: #333333;
}

.info-content {
	display: flex;
	flex-direction: column;
}

.info-item {
	font-size: 26rpx;
	color: #666666;
	margin-bottom: 10rpx;
	line-height: 1.5;
}
</style>