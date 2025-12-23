<template>
	<div class="register-container">
		<!-- 顶部导航栏 -->
		<div class="navbar">
			<div class="back-btn" @click="goBack()">
				<text class="ri-arrow-left-line"></text>
			</div>
			<text class="page-title">{{ $t('注册') }}</text>
			<div class="navbar-right"></div>
		</div>
		
		<!-- Logo and background -->
		<div class="logo-container">
			<div class="header-text">TRADE BOT</div> 
		</div>
		
		<!-- Registration Form Card -->
		<div class="register-card">
			<!-- Tab Selection -->
			<div class="tab-container">
				<div @click="selecttype=1" :class="selecttype==1 ? 'tab-item active' : 'tab-item'">
					<text class="tab-text">{{ $t('邮箱') }}</text>
				</div> 
			</div>
			
			<!-- Form Fields -->
			<div class="form-container">
				<!-- Email Input -->
				<input v-if="selecttype==1" v-model="emailtxt" type="text" :placeholder="$t('请输入邮箱')" class="input-field">
				
				<!-- Phone Input -->
				<div v-else>
					<div class="phone-input-container">
						<select class="country-select" v-model="mobilearea">
							<option value="+234">+234(NG)</option>
							<option value="+81">+81(JP)</option>
							<option value="+82">+82(RKSK)</option>
							<option value="+91">+91(Ind)</option>
							<option value="+65">+65(SG)</option>
							<option value="+92">+92(PK)</option>
							<option value="+233">+233(GHA)</option>
							<option value="+225">+225(CIV)</option>
							<option value="+1">+1(US)</option>
							<option value="+55">+55(BR)</option>
							<option value="+44">+44(UK)</option>
							<option value="+254">+254(KE)</option>
							<option value="+27">+27(ZA)</option>
							<option value="+250">+250(RW)</option>
							<option value="+90">+90(TR)</option>
							<option value="+971">+971(AE)</option>
							<option value="+260">+260(ZM)</option>
							<option value="+237">+237(CM)</option>
							<option value="+7">+7(RU)</option>
							<option value="+256">+256(UG)</option>
							<option value="+218">+218(LBY)</option>
							<option value="+62">+62(IDN)</option>
							<option value="+34">+34(ES)</option>
							<option value="+39">+39(IT)</option>
							<option value="+226">+226(BF)</option>
							<option value="+220">+220(GM)</option>
							<option value="+49">+49(DE)</option>
							<option value="+966">+966(SA)</option>
							<option value="+43">+43(AT)</option>
							<option value="+40">+40(RO)</option>
							<option value="+229">+229(BJ)</option>
							<option value="+351">+351(pt)</option>
							<option value="+20">+20(EG)</option>
							<option value="+855">+855(KH)</option>
							<option value="+61">+61(AU)</option>
							<option value="+66">+66(THA)</option>
						</select>
						<input v-model="mobiletxt" type="number" :placeholder="$t('请输入手机号')" class="phone-input">
					</div>
				</div>
				
				<!-- Password and Invite Code -->
				<input type="password" v-model="passwordtxt" :placeholder="$t('请输入密码')" class="input-field">
				<input type="password" v-model="passwordtxt1" :placeholder="$t('请输入支付密码')" class="input-field">
				<input type="text" v-model="codetxt" :placeholder="$t('请输入邀请码')" class="input-field">
				
				<!-- Register Button -->
				<div class="action-button" @click="register()">
					<text class="button-text">{{$t('注册')}}</text>
				</div>
			</div>
		</div>
	</div>
</template>

<script> 
	export default {
		data: function() {
			return {
				selectforgettype: 1,
				selecttype: 1,  
				emailtxt:'',
				mobiletxt:'',
				mobilearea:'+234',
				passwordtxt:'',
				passwordtxt1:'',
				codetxt:''
			}
		},
		onReady: function() {

		},
		onLoad: function(t) { 
            if(t.code){
				this.codetxt=t.code;
			}
		},
		onShow: function() {

		},
		mounted() {

		},
		methods: {
			goBack: function() {
				window.history.back();
			},
            register: function() {
				var t = this;
				if(t.selecttype==1){
					const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
					if(!emailRegex.test(t.emailtxt)){ 
						return t.$toast(t.$t('请输入邮箱'));
					}
				}else{
					if(isNaN(t.mobiletxt))
					  return t.$toast(t.$t('请输入手机号'));
				}
				
				if(!t.passwordtxt||t.passwordtxt==''){
					return t.$toast(t.$t('请输入密码'));
				} 
				if(!t.passwordtxt1||t.passwordtxt1==''){
					return t.$toast(t.$t('请输入支付密码'));
				} 
				if(!t.codetxt||t.codetxt==''){
					return t.$toast(t.$t('请输入邀请码'));
				} 
				t.req({
					url: "user/register",
					data: {'selecttype':t.selecttype,'emailtxt':t.emailtxt,'mobiletxt':t.mobiletxt,'mobilearea':t.mobilearea,'passwordtxt':t.passwordtxt,'passwordtxt1':t.passwordtxt1,'codetxt':t.codetxt},
					Loading: !1,
					success: function (i) { 
						if(i.code==0){
							return t.$toast(t.$t(i.msg));
						}					    
						t.$toast(t.$t('注册成功'));
						uni.setStorageSync('token',i.data.token);
						uni.setStorageSync('userid',i.data.userid);
						uni.setStorageSync('userdata',i.data.userdata);    
						t.$gopage("/pages/login/index");   
					}
				})
			},
		},
	}
</script>

<style>
.register-container {
	background-color: #F5F5F5;
	min-height: 100vh;
}

/* Navbar Styling */
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
}

.back-btn img {
	width: 40rpx;
	height: 40rpx;
}

.page-title {
	font-size: 36rpx;
	font-weight: bold;
	color: #333333;
}

.navbar-right {
	width: 60rpx;
}

/* Logo and Background */
.logo-container {
	position: relative;
	display: flex;
	flex-direction: column;
	justify-content: center;
	align-items: center;
	height: 220rpx;
	margin-bottom: 20rpx;
	background-color: #007AFF;
}

.header-text {
	font-size: 40rpx;
	font-weight: bold;
	color: #FFFFFF;
	margin-bottom: 20rpx;
}

.logo {
	width: 120rpx;
	height: 120rpx;
	z-index: 2;
}

/* Register Card */
.register-card {
	background-color: #FFFFFF;
	margin: 30rpx;
	border-radius: 20rpx;
	padding: 30rpx;
	box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.05);
}

/* Tab Navigation */
.tab-container {
	display: flex;
	border-bottom: 1rpx solid #F0F0F0;
	margin-bottom: 30rpx;
}

.tab-item {
	flex: 1;
	text-align: center;
	padding: 20rpx 0;
	position: relative;
}

.tab-text {
	font-size: 32rpx;
	color: #666666;
}

.tab-item.active .tab-text {
	color: #007AFF;
	font-weight: bold;
}

.tab-item.active:after {
	content: "";
	position: absolute;
	bottom: -2rpx;
	left: 50%;
	transform: translateX(-50%);
	width: 60rpx;
	height: 4rpx;
	background-color: #007AFF;
	border-radius: 4rpx;
}

/* Form Styling */
.form-container {
	padding: 10rpx 0;
}

.input-field {
	width: 100%;
	height: 100rpx;
	background-color: #F8F8F8;
	border-radius: 12rpx;
	margin-bottom: 20rpx;
	padding: 0 30rpx;
	font-size: 30rpx;
	box-sizing: border-box;
	color: #333333;
	border: 1px solid #EEEEEE;
}

.phone-input-container {
	display: flex;
	margin-bottom: 20rpx;
	height: 100rpx;
	width: 100%;
}

.country-select {
	width: 40%;
	background-color: #F8F8F8;
	border-radius: 12rpx 0 0 12rpx;
	padding: 0 20rpx;
	font-size: 30rpx;
	color: #333333;
	border: 1px solid #EEEEEE;
	border-right: none;
	height: 100rpx;
}

.phone-input {
	width: 60%;
	height: 100rpx;
	background-color: #F8F8F8;
	border-radius: 0 12rpx 12rpx 0;
	padding: 0 30rpx;
	font-size: 30rpx;
	color: #333333;
	border: 1px solid #EEEEEE;
	border-left: none;
	box-sizing: border-box;
}

/* Action Button */
.action-button {
	height: 100rpx;
	background-color: #007AFF;
	border-radius: 12rpx;
	display: flex;
	justify-content: center;
	align-items: center;
	margin-top: 30rpx;
}

.button-text {
	color: #FFFFFF;
	font-size: 32rpx;
	font-weight: bold;
}
</style>