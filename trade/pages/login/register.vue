<template>
	 <div class="rigister dark-theme">
		<div class="head">
		    <image v-if="!ishavecode" class="back-icon" src="/static/img/market/detail/backicon.png" @tap="goBack"></image>
			<span class="text">{{ $t('注册') }}</span>
			<span class="langbutt"> 
				<gui-select-menu 
				:isH5header="false"
				:items="actionSheetItems" 
				@select="select1" 
				:selectIndex="showlanguageindex"
				ref="selectMenu1"></gui-select-menu>
			</span>
		</div>

		<!-- Remove the background image -->
		<div class="app-title">
			<h1>Second Futures</h1>
		</div>
		
		<div class="con2 dark-content">
			<div class="tab">
				<span class="text" @click="selecttype=1" :class="selecttype==1?'active':''"> {{ $t('邮箱') }}</span> 
			</div>
			<div class="box">
				<input v-if="selecttype==1" v-model="emailtxt" type="text" :placeholder="$t('请输入邮箱')" class="text">
				<div v-else class="inputb">
					<span  class="textb">
						<select class="text1" v-model="mobilearea">
							<option  value="+234"> +234(NG) </option>
							<option  value="+81"> +81(JP) </option>
							<option  value="+82"> +82(RKSK) </option>
							<option  value="+91"> +91(Ind) </option>
							<option  value="+65"> +65(SG) </option>
							<option  value="+92"> +92(PK) </option>
							<option  value="+233"> +233(GHA) </option>
							<option  value="+225"> +225(CIV) </option>
							<option  value="+1"> +1(US) </option>
							<option  value="+55"> +55(BR) </option>
							<option  value="+44"> +44(UK) </option>
							<option  value="+254"> +254(KE) </option>
							<option  value="+27"> +27(ZA) </option>
							<option  value="+250"> +250(RW) </option>
							<option  value="+90"> +90(TR) </option>
							<option  value="+971"> +971(AE) </option>
							<option  value="+260"> +260(ZM) </option>
							<option  value="+237"> +237(CM) </option>
							<option  value="+7"> +7(RU) </option>
							<option  value="+256"> +256(UG) </option>
							<option  value="+218"> +218(LBY) </option>
							<option  value="+62"> +62(IDN) </option>
							<option  value="+34"> +34(ES) </option>
							<option  value="+39"> +39(IT) </option>
							<option  value="+226"> +226(BF) </option>
							<option  value="+220"> +220(GM) </option>
							<option  value="+49"> +49(DE) </option>
							<option  value="+966"> +966(SA) </option>
							<option  value="+43"> +43(AT) </option>
							<option  value="+40"> +40(RO) </option>
							<option  value="+229"> +229(BJ) </option>
							<option  value="+351"> +351(pt) </option>
							<option  value="+20"> +20(EG) </option>
							<option  value="+855"> +855(KH) </option>
							<option  value="+61"> +61(AU) </option>
							<option  value="+66"> +66(THA) </option>
						</select><input v-model="mobiletxt" style="float: right;" type="text" :placeholder="$t('请输入手机号')"  class="text2"></span></div>
				<input type="password" v-model="passwordtxt" :placeholder="$t('请输入密码')"  class="text">
				<input type="password" v-model="confirmpassword" :placeholder="$t('请确认密码')"  class="text">
				<input type="password" v-model="passwordtxt1" :placeholder="$t('请输入支付密码')"  class="text">
				<input type="text" v-model="codetxt" :placeholder="$t('邀请码') + ' (' + $t('选填') + ')'"  class="text">
			</div>
			<div class="buttb">
				<div class="butt" @click="register()">{{$t('注册')}}</div>
			</div>
		</div>

	</div><!----><!----><!---->
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
				confirmpassword:'',
				passwordtxt1:'',
				codetxt:'',
				ishavecode:false,
				showlanguagetxt:uni.getStorageSync('userlanguagetxt'),
				showlanguageindex:uni.getStorageSync('userlanguageindex'),
				actionSheetItems: ['English','中文', 'Português','日本語'],
				actionSheetItemsvalue: ['en', 'zhHans', 'ptyy','ry'],
			}
		},
		onReady: function() {

		},
		onLoad: function(t) { 
            if(t.code){
				this.ishavecode=true;
				this.codetxt=t.code;
			}
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
				// 触发语言变化事件，更新tabBar
				uni.$emit('languageChanged');
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
				if(!t.confirmpassword||t.confirmpassword==''){
					return t.$toast(t.$t('请确认密码'));
				}
				if(t.passwordtxt !== t.confirmpassword){
					return t.$toast(t.$t('两次密码不一致'));
				}
				if(!t.passwordtxt1||t.passwordtxt1==''){
					return t.$toast(t.$t('请输入支付密码'));
				}
				// 邀请码为选填，不再强制验证
				// 获取设备ID，用于合并临时用户数据
				const deviceId = uni.getStorageSync('device_id') || '';
				t.req({
					url: "user/register",
					data: {'selecttype':t.selecttype,'emailtxt':t.emailtxt,'mobiletxt':t.mobiletxt,'mobilearea':t.mobilearea,'passwordtxt':t.passwordtxt,'passwordtxt1':t.passwordtxt1,'codetxt':t.codetxt,'device_id':deviceId},
					Loading: !1,
					success: function (i) {
						if(i.code==0){
							return t.$toast(t.$t(i.msg));
						}
					    t.$toast(t.$t('注册成功'));
						uni.setStorageSync('token',i.data.token);
						uni.setStorageSync('userid',i.data.userid);
						uni.setStorageSync('userdata',i.data.userdata);
						// 清除游客状态
						uni.removeStorageSync('is_guest');
						uni.removeStorageSync('guest_userid');
						uni.removeStorageSync('guest_token');
						t.$gopage("/pages/login/index");
					}
				})
			},
		},
	}
</script>

<style>
/* Dark theme styles */
.dark-theme {
	background-color: #1A1A1A;
	color: #FFFFFF;
	min-height: 100vh;
	padding-bottom: 50rpx;
}
.back-icon {
	width: 48rpx;
	height: 48rpx;
}
.head {
	background-color: #2C2C2C;
	padding: 20rpx 30rpx;
	display: flex;
	align-items: center;
	justify-content: space-between;
	box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.head .text {
	font-size: 36rpx;
	font-weight: bold;
	color: #FFFFFF;
}

/* Language button style */
.langbutt {
	display: flex;
	align-items: center;
	color: #40DFBF !important;
}

.langbutt >>> .gui-select-menu,
.langbutt >>> .gui-select-menu-text {
	color: #40DFBF !important;
	background-color:#3a3a3a !important;
}

.img2 {
	width: 50rpx;
	height: 50rpx;
}

/* App title styles */
.app-title {
	padding: 80rpx 0;
	text-align: center;
}

.app-title h1 {
	font-size: 60rpx;
	font-weight: bold;
	color: #40DFBF;
	margin: 0;
	letter-spacing: 2px;
	text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.dark-content {
	background-color: #2C2C2C;
	border-radius: 20rpx;
	padding: 40rpx 30rpx;
	margin: 0 30rpx;
	box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
}

.tab {
	display: flex;
	justify-content: space-around;
	margin-bottom: 40rpx;
	border-bottom: 1px solid #3A3A3A;
	padding-bottom: 15rpx;
}

.tab .text {
	color: #999999;
	font-size: 32rpx;
	padding: 10rpx 30rpx;
	transition: all 0.3s ease;
}

.tab .active {
	color: #40DFBF;
	border-bottom: 4rpx solid #40DFBF;
}

.box {
	margin-bottom: 30rpx;
}

.box .text, .box .text1, .box .text2, .inputb .text, .inputb .text1, .inputb .text2 {
	background-color: #3A3A3A;
	border: 1px solid #4A4A4A;
	color: #FFFFFF;
	border-radius: 12rpx;
	height: 100rpx;
	padding: 0 30rpx;
	margin-bottom: 30rpx;
	width: 100%;
	box-sizing: border-box;
	transition: border-color 0.3s ease;
}

.box .text:focus, .box .text1:focus, .box .text2:focus,
.inputb .text:focus, .inputb .text1:focus, .inputb .text2:focus {
	border-color: #40DFBF;
}

.textb {
	display: flex;
}

.text1 {
	flex: 0.35;
	margin-right: 20rpx;
}

.text2 {
	flex: 0.65;
}

.buttb {
	display: flex;
	justify-content: center;
	margin-top: 50rpx;
}

.butt {
	width: 100%;
	height: 100rpx;
	line-height: 100rpx;
	text-align: center;
	border-radius: 50rpx;
	font-size: 32rpx;
	font-weight: bold;
	background-color: #40DFBF;
	color: #FFFFFF;
	box-shadow: 0 5px 15px rgba(0, 122, 255, 0.3);
	transition: all 0.3s ease;
}

.butt:active {
	background-color: #0065d1;
	transform: translateY(2px);
}
.gui-primary-text{
	 color:#40DFBF !important;
}
.gui-select-menus{
	background-color: black !important;
}
</style>