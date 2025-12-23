<template>
	<div class="login dark-theme">
		<div class="head">
	    	<image   class="back-icon" src="/static/img/market/detail/backicon.png" @tap="goBack"></image>
			<span class="text"></span>
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
		
		<div class="conb dark-content" > 
			<div class="tab">
				<span @click="selecttype=1;emailtxt='';password=''" :class="selecttype==1?'active':''"
					class="text">{{ $t('邮箱') }}</span>
				 
			</div>
			<div class="inputb" v-if="selecttype==1">
				<input v-model="emailtxt" type="text" :placeholder=" $t('请输入邮箱') " class="text">
				<input v-model="password" type="password" :placeholder=" $t('密码') " class="text"></div><!---->
			<div class="inputb" v-else>
				<span class="textb">
					<select class="text1" v-model="mobilearea">
						<option value="+234"> +234(NG)</option>
						<option value="+81"> +81(JP)</option>
						<option value="+82"> +82(RKSK)</option>
						<option value="+91"> +91(Ind)</option>
						<option value="+65"> +65(SG)</option>
						<option value="+92"> +92(PK)</option>
						<option value="+233"> +233(GHA)</option>
						<option value="+225"> +225(CIV)</option>
						<option value="+1"> +1(US)</option>
						<option value="+55"> +55(BR)</option>
						<option value="+44"> +44(UK)</option>
						<option value="+254"> +254(KE)</option>
						<option value="+27"> +27(ZA)</option>
						<option value="+250"> +250(RW)</option>
						<option value="+90"> +90(TR)</option>
						<option value="+971"> +971(AE)</option>
						<option value="+260"> +260(ZM)</option>
						<option value="+237"> +237(CM)</option>
						<option value="+7"> +7(RU)</option>
						<option value="+256"> +256(UG)</option>
						<option value="+218"> +218(LBY)</option>
						<option value="+62"> +62(IDN)</option>
						<option value="+34"> +34(ES)</option>
						<option value="+39"> +39(IT)</option>
						<option value="+226"> +226(BF)</option>
						<option value="+220"> +220(GM)</option>
						<option value="+49"> +49(DE)</option>
						<option value="+966"> +966(SA)</option>
						<option value="+43"> +43(AT)</option>
						<option value="+40"> +40(RO)</option>
						<option value="+229"> +229(BJ)</option>
						<option value="+351"> +351(pt)</option>
						<option value="+20"> +20(EG)</option>
						<option value="+855"> +855(KH)</option>
						<option value="+61"> +61(AU)</option>
						<option value="+66"> +66(THA)</option>
					</select>
					<input v-model="mobiletxt" type="text" style="float: right;" :placeholder="$t('请输入手机号')" class="text2">
				</span>
				<input v-model="password1" type="password" :placeholder="$t('密码')" class="text">
			</div>
			<div class="buttb">
				<div class="butt butt1" @click="login()">{{ $t('登录') }}</div>
				<div class="butt butt2" @click="$gopage('/pages/login/register')">{{ $t('注册') }}</div>
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
				actionSheetItems: ['English','中文', 'Português','日本語'],
				actionSheetItemsvalue: ['en', 'zhHans', 'ptyy','ry'],
				emailtxt:'',
				mobilearea:'+234',
				mobiletxt:'',
				password:'',
				password1:'', 
				 
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
				// 触发语言变化事件，更新tabBar
				uni.$emit('languageChanged');
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
				// 生成或获取设备ID
				let deviceId = uni.getStorageSync('device_id');
				if (!deviceId) {
					deviceId = 'device_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
					uni.setStorageSync('device_id', deviceId);
				}

				t.req({
					url: "user/login",
					data: {
						'selecttype': t.selecttype,
						'emailtxt': t.emailtxt,
						'mobiletxt': t.mobiletxt,
						'mobilearea': t.mobilearea,
						'password': t.password,
						'password1': t.password1,
						'device_id': deviceId
					},
					Loading: !1,
					success: function (i) {
						if(i.code==0){
							return t.$toast(t.$t(i.msg));
						}
					    // t.$toast(t.$t('登录成功'));
						uni.setStorageSync('token',i.data.token);
						uni.setStorageSync('userid',i.data.userid);
						uni.setStorageSync('userdata',i.data.userdata);
						// 清除游客相关缓存
						uni.removeStorageSync('guest_userid');
						uni.removeStorageSync('guest_token');
						uni.removeStorageSync('guest_virtualmoney');
						uni.removeStorageSync('is_guest');
						t.$gopage("/pages/home/index");
					}
				})
			}
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

/* 导航栏样式 */
.head {
	display: flex;
	align-items: center;
	justify-content: flex-end;
	height: 90rpx;
	background-color: #2C2C2C;
	padding: 0 30rpx;
	box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.2);
}

/* App title styles */
.app-title {
	padding: 40rpx 0;
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

/* 内容卡片样式 */
.dark-content {
	background-color: #2C2C2C;
	margin: 30rpx;
	padding: 40rpx 30rpx;
	border-radius: 20rpx;
	color: #FFFFFF;
	box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.3);
}

/* 选项卡样式 */
.tab {
	display: flex;
	justify-content: space-around;
	margin-bottom: 40rpx;
	border-bottom: 1rpx solid #3A3A3A;
	padding-bottom: 20rpx;
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

/* 输入框样式 */
.inputb {
	margin-bottom: 30rpx;
}

.inputb .text, .inputb .text1, .inputb .text2 {
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

/* 按钮样式 */
.buttb {
	display: flex;
	justify-content: space-around;
	margin-top: 40rpx;
}

.butt {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	width: 48%;
	height: 100rpx;
	line-height: 100rpx;
	text-align: center;
	border-radius: 50rpx;
	font-size: 32rpx;
	font-weight: bold;
	transition: all 0.3s ease;
}

.butt1 {
	background-color: #40DFBF;
	color: #FFFFFF;
	box-shadow: 0 5px 15px rgba(0, 122, 255, 0.3);
}

.butt1:active {
	background-color: #0065d1;
	transform: translateY(2px);
}

.butt2 {
	background-color: rgba(255, 255, 255, 0.1);
	color: #FFFFFF;
	border: 1px solid rgba(255, 255, 255, 0.2);
}

.butt2:active {
	background-color: rgba(255, 255, 255, 0.15);
	transform: translateY(2px);
}

.img2 {
	width: 50rpx;
	height: 50rpx;
}

.navbar {
	height: 90rpx;
	display: flex;
	align-items: center;
	justify-content: space-between;
	background-color: #1A1A1A;
	padding: 0 30rpx;
	box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.2);
}

.back-btn {
	width: 60rpx;
	height: 60rpx;
	display: flex;
	align-items: center;
	justify-content: center;
}

.back-icon {
	width: 40rpx;
	height: 40rpx;
}

.page-title {
	font-size: 36rpx;
	font-weight: bold;
	color: #FFFFFF;
	position: absolute;
	left: 50%;
	transform: translateX(-50%);
}

.navbar-right {
	width: 60rpx;
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
/* 语言选择器文字颜色 */
.langbutt {
	color: #40DFBF !important;
}

.langbutt .gui-primary-text,
.langbutt .gui-select-menu-title text {
	color: #40DFBF !important;
}

/* 下拉菜单样式 */
.gui-select-menus,
.gui-select-menu,
.gui-bg-white {
	background-color: #2C2C2C !important;
}

.gui-select-menus .gui-primary-text {
	color: #FFFFFF !important;
}
</style>