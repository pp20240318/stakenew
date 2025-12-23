<template>
	<div class="login-container">
		<!-- 顶部导航栏 -->
		<div class="navbar">
			 
			<text class="page-title">TRADE BOT</text>
			<span class="langbutt">
				 
				<gui-select-menu 
				:isH5header="false"
				:items="actionSheetItems" 
				@select="select1" 
				:selectIndex="showlanguageindex"
				ref="selectMenu1"></gui-select-menu>
			</span>
		</div>
		
		<!-- Header -->
	 
		<!-- Login Form Card -->
		<div class="login-card">
			<!-- Tab Selection -->
			<div class="tab-container">
				<div @click="selecttype=1;emailtxt='';password=''" :class="selecttype==1 ? 'tab-item active' : 'tab-item'">
					<text class="tab-text">{{ $t('邮箱') }}</text>
				</div> 
			</div>
			
			<!-- Form Fields -->
			<div class="form-container">
				<!-- Email Input -->
				<div v-if="selecttype==1">
					<input v-model="emailtxt" type="text" :placeholder="$t('请输入邮箱')" class="input-field">
					<input v-model="password" type="password" :placeholder="$t('密码')" class="input-field">
				</div>
				
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
					<input v-model="password1" type="password" :placeholder="$t('密码')" class="input-field">
				</div>
				
				<!-- Action Buttons -->
				<div class="button-container">
					<div class="action-button login-btn" @click="login()">{{ $t('登录') }}</div>
					<div class="action-button register-btn" @click="$gopage('/pages/login/register')">{{ $t('注册') }}</div>
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
				showlanguagetxt:uni.getStorageSync('userlanguagetxt'),
				showlanguageindex:uni.getStorageSync('userlanguageindex'), 
				actionSheetItems: ['English','中文', 'Português','แบบไทย'],
				actionSheetItemsvalue: ['en', 'zhHans', 'ptyy','ty'],
				emailtxt:'',
				mobilearea:'+234',
				mobiletxt:'',
				password:'',
				password1:''
			}
		},
		onReady: function() {

		},
		onLoad: function() {

		},
		onShow: function() {
          if(this.showlanguagetxt==''||!this.showlanguagetxt){
			this.showlanguagetxt=this.actionSheetItemsvalue[0];
		  }
		  if(this.showlanguageindex==''){
		  	this.showlanguageindex=0;
		  }
		},
		mounted() {

		},
		methods: {
			goBack: function() {
				window.history.back();
			},
            select1: function (index, val) {
				this.$i18n.locale  = this.actionSheetItemsvalue[index]; // 切换中英文
				this.showlanguageindex=index;
				uni.setStorageSync('userlanguagetxt', val)
				uni.setStorageSync('userlanguage', this.actionSheetItemsvalue[index]) 
            	uni.setStorageSync('userlanguageindex', index)
            },
			login:function(){
				var t=this;
				if(t.selecttype=='1'){
					if(!t.emailtxt||t.emailtxt==''){
						return t.$toast(t.$t('请输入邮箱'));
					}else{
						const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
						if(!emailRegex.test(t.emailtxt)){ 
							return t.$toast(t.$t('请输入邮箱'));
						}
					}
					if(!t.password||t.password==''){
						return t.$toast(t.$t('请输入密码'));
					} 
				}else{
					if(!t.mobiletxt||t.mobiletxt==''){
						return t.$toast(t.$t('请输入手机号'));
					}
					if(!t.password1||t.password1==''){
						return t.$toast(t.$t('请输入密码'));
					} 
				}
				t.req({
					url: "user/login",
					data: {'selecttype':t.selecttype,'emailtxt':t.emailtxt,'mobiletxt':t.mobiletxt,'mobilearea':t.mobilearea,'password':t.password,'password1':t.password1},
					Loading: !1,
					success: function (i) { 
						if(i.code==0){
							return t.$toast(t.$t(i.msg));
						}
						
						uni.setStorageSync('token',i.data.token);
						uni.setStorageSync('userid',i.data.userid);
						uni.setStorageSync('userdata',i.data.userdata);   
						t.$gopage("/pages/bot/bot");   
					}
				})
			}
		},
	}
</script>

<style>
.login-container {
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

.lang-icon {
	width: 40rpx;
	height: 40rpx;
}

.langbutt {
	display: flex;
	align-items: center;
}

.page-title {
	font-size: 36rpx;
	font-weight: bold;
	color: #333333;
}

/* Header with Text */
.header-container {
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

/* Login Card */
.login-card {
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

/* Button Container */
.button-container {
	display: flex;
	justify-content: space-between;
	margin-top: 30rpx;
}

/* Action Buttons */
.action-button {
	height: 100rpx;
	border-radius: 12rpx;
	display: flex;
	justify-content: center;
	align-items: center;
	font-size: 32rpx;
	font-weight: bold;
}

.login-btn {
	background-color: #007AFF;
	color: #FFFFFF;
	flex: 1;
	margin-right: 15rpx;
}

.register-btn {
	background-color: #F8F8F8;
	color: #007AFF;
	border: 1px solid #007AFF;
	flex: 1;
	margin-left: 15rpx;
}
</style>