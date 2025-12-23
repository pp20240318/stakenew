<template>
	<view class="withdrawal-container">
		<!-- 顶部导航栏 -->
		<view class="navbar">
			<view class="back-btn" @tap="$goBack()">
				<image src="/static/images/icons/back-arrow.svg" class="back-icon"></image>
			</view>
			<text class="page-title">{{ $t('提现') }}</text> 
			<view class="navbar-right"></view>

		</view>
		
		<!-- 提现内容 -->
		<view class="withdrawal-content">
			<!-- 账户余额卡片 -->
			<view class="balance-card">
				<view class="balance-title">{{ $t('账户余额') }}</view>
				<view class="balance-amount">{{usermoney}}<span class="currency-symbol">$</span></view>
				<view class="balance-limits">
					<view class="limit-item">{{ $t('最小提现金额') }}: <text class="limit-value">${{minwithmoney}}</text></view>
					<view class="limit-item">{{ $t('最大提现金额') }}: <text class="limit-value">${{maxwithmoney}}</text></view>
				</view>
			</view>
			
			<!-- 提现表单 -->
			<view class="form-card">
				<!-- 提现类型选择 -->
				<view class="form-item select-item">
					<select v-model="selecttype" @change="changetype()" class="select-control">
						<option value="1" class="option-item">USDT</option>
						<option value="5" class="option-item">MYR</option>
					<!-- 	<option value="4" class="option-item">ETH</option> -->
					</select>
				</view>
				
				<!-- 提现金额 -->
				<view class="form-item">
					<input type="text" v-model="money" :placeholder="$t('请输入金额')" class="input-control" />
				</view>
				
				<!-- 手续费信息 -->
				<!-- <view class="fee-info">
					<view class="fee-item">{{show_name}}:USDT {{show_percent}}</view>
					<view class="fee-item">{{ $t('手续费') }} $ {{(money*show_percent*handling_fee)}}</view>
				</view> -->
				
				<!-- 提现地址 -->
				<view v-if="selecttype==1" class="form-item">
					<input type="text" v-model="address" :placeholder="$t('请输入地址')" class="input-control" />
				</view>
				<view v-if="selecttype==5" class="form-item">
					<textarea  rows ="3" v-model="address" :placeholder="$t('请输入账户')" class="input-control" style="height:130px" ></textarea>
				</view>
				<!-- 银行卡信息部分 -->
				<template v-if="selecttype==2">
					<view class="form-item">
						<input type="text" v-model="name" :placeholder="$t('请输入姓名')" class="input-control" />
					</view>
					
					<view class="form-item">
						<input type="text" v-model="bankno" :placeholder="$t('请输入银行账号')" class="input-control" />
					</view>
					
					<view class="form-item">
						<input type="text" v-model="bankname" :placeholder="$t('请输入银行名称')" class="input-control" />
					</view>
					
					<view class="form-item">
						<input type="text" v-model="banktype" :placeholder="$t('请输入法定货币')" class="input-control" />
					</view>
					
					<view class="form-item">
						<input type="text" v-model="bankdname" :placeholder="$t('请输入支行名称')" class="input-control" />
					</view>
					
					<view class="form-item">
						<input type="text" v-model="bankcode" :placeholder="$t('请输入银行邮编')" class="input-control" />
					</view>
				</template>
				
				<!-- 支付密码 -->
				<view class="form-item">
					<input type="text" v-model="paypassword" :placeholder="$t('请输入支付密码')" class="input-control" />
				</view>
				
				<!-- 提交按钮 -->
				<button class="submit-btn" @tap="submit()">{{ $t('提交') }}</button>
				
				<!-- 温馨提示 -->
				<view class="tips-section">
					<view class="tips-title">{{ $t('温馨提示') }}</view>
					<view class="tips-item">1. {{ $t('支持USDT-TRC20快速提现') }}</view>
					<view class="tips-item">2. {{ $t('提现后等待客服审核') }}</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		data: function() {
			return {
				minwithmoney:0,
				maxwithmoney:0, 
				usermoney:0,
				money:0,
				handling_fee:0,
				address:'',
				name:'',
				bankno:'',
				bankname:'',
				bankdname:'',
				banktype:'',
				bankcode:'',
				paypassword:'',  
				selecttype:1,
				show_percent:'1',
				show_name:'USDT',
				usdt_percent:'',
				btc_percent:'',
				eth_percent:'',
				usdc_percent:'',
			}
		},
		onReady: function() {
	       
		},
		onLoad: function() {
			
		},
		onShow: function() {
	       var userid=uni.getStorageSync('userid');
	       			if(!userid) this.$gopage("/pages/login/index");
	       this.loadData();
		},
		mounted() { 
		    
		},
		methods: {  
			submit : function () { 
				var t=this;
				if(!t.money||t.money==''||isNaN(t.money)){
					return t.$toast(t.$t('请输入金额'));
				}else{
					if(t.money*t.show_percent>t.usermoney){
						return t.$toast(t.$t('提现')+' '+t.$t('金额')+' '+t.$t('必须')+'>= '+t.$t('账户余额'));
					}
					if(t.money*t.show_percent>t.maxwithmoney){
						return t.$toast(t.$t('提现')+' '+t.$t('金额')+' '+t.$t('必须')+'<= '+t.$t('最大提现金额')); 
					}
					if(t.money*t.show_percent<t.minwithmoney){
						return t.$toast(t.$t('提现')+' '+t.$t('金额')+' '+t.$t('必须')+'>= '+t.$t('最小提现金额'));  
					}
				}
				
				if(t.selecttype=='2'){
					if(!t.name||t.name==''){
						return t.$toast(t.$t('请输入姓名'));
					}
					if(!t.bankno||t.bankno==''){
						return t.$toast(t.$t('请输入银行帐号'));
					}
					if(!t.bankname||t.bankname==''){
						return t.$toast(t.$t('请输入银行名称'));
					}
					if(!t.banktype||t.banktype==''){
						return t.$toast(t.$t('请输入法定货币'));
					}
					if(!t.bankdname||t.bankdname==''){
						return t.$toast(t.$t('请输入支行名称'));
					}
					if(!t.bankcode||t.bankcode==''){
						return t.$toast(t.$t('请输入银行邮编'));
					}
				}else{
					if(!t.address||t.address==''){
						return t.$toast(t.$t('请输入地址'));
					}
				} 
				if(!t.paypassword||t.paypassword==''){
					return t.$toast(t.$t('请输入支付密码'));
				}
				var userid=uni.getStorageSync('userid');
				t.req({
					url: "recharge/savewithdrawal", 
					Loading: !1,
					data:{
						selecttype:t.selecttype,money:t.money,address:t.address,name:t.name,
					    bankno:t.bankno,bankname:t.bankname,banktype:t.banktype,bankdname:t.bankdname,bankcode:t.bankcode,
					    paypassword:t.paypassword,userid:userid,show_percent:t.show_percent
					},
					success: function (i) {  
						if(i.code==0){
							return t.$toast(t.$t(i.msg));
						} 
						 t.$toast(t.$t('成功'));
						t.$gopage("/pages/wallet/index");   
					}
				})
			},
			changetype(){
				var t = this;  
				var thispercent="",thisname=""; 
				if(t.selecttype==1){ 
					thispercent=t.usdt_percent;
					thisname="USDT";
				}else if(t.selecttype==2){ 
					thispercent=t.btc_percent;
					thisname="BTC";
				}else if(t.selecttype==5){ 
					thispercent=t.usdc_percent;
					thisname="USDC";
				}
				else{ 
					thispercent=t.eth_percent;
					thisname="ETH";
				}
				t.show_name=thisname;
				t.show_percent=thispercent; 
			},
	        loadData(){
	        	var t = this; 
				var userid=uni.getStorageSync('userid');
	        	t.req({
	        		url: "recharge/withdrawal", 
	        		Loading: !1,
					data:{'userid':userid},
	        		success: function (i) {  
	        			 t.handling_fee=i.data.handling_fee;
						 t.minwithmoney=i.data.minwithmoney;
						 t.maxwithmoney=i.data.maxwithmoney;
						 t.usermoney=i.data.usermoney;
						 t.usdt_percent=i.data.usdt_percent;
						 t.btc_percent=i.data.btc_percent;
						 t.eth_percent=i.data.eth_percent;
						 t.usdc_percent=i.data.usdc_percent;
	        		}
	        	})
	        } 
		},
	}
</script>

<style>
.withdrawal-container {
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

.back-btn {
	width: 60rpx;
	height: 60rpx;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 40rpx;
}

.ri-arrow-left-line:before {
	content: "←";
}

.page-title {
	font-size: 36rpx;
	font-weight: bold;
	color: #FFFFFF;
}

.navbar-right {
	font-size: 28rpx;
	color: #007AFF;
}

.records-btn {
	font-size: 28rpx;
}

/* 提现内容区域 */
.withdrawal-content {
	padding: 30rpx;
}

/* 余额卡片 */
.balance-card {
	background-color: #007AFF;
	border-radius: 20rpx;
	padding: 30rpx;
	margin-bottom: 20rpx;
	color: #FFFFFF;
	box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.3);
}

.balance-title {
	font-size: 28rpx;
	margin-bottom: 10rpx;
}

.balance-amount {
	font-size: 48rpx;
	font-weight: bold;
	margin-bottom: 20rpx;
}

.currency-symbol {
	font-size: 28rpx;
	font-weight: normal;
	margin-left: 5rpx;
}

.balance-limits {
	display: flex;
	flex-direction: column;
	gap: 5rpx;
	margin-top: 10rpx;
}

.limit-item {
	font-size: 24rpx;
	opacity: 0.9;
}

.limit-value {
	font-weight: bold;
}

/* 表单卡片 */
.form-card {
	background-color: #2C2C2C;
	border-radius: 20rpx;
	padding: 30rpx;
	box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.2);
}

.form-item {
	margin-bottom: 20rpx;
}

.select-item {
	margin-bottom: 30rpx;
}

.select-control {
	width: 100%;
	height: 80rpx;
	background-color: #3A3A3A;
	border: 1px solid #4A4A4A;
	border-radius: 8rpx;
	padding: 0 20rpx;
	font-size: 28rpx;
	color: #FFFFFF;
}

.option-item {
	font-size: 28rpx;
}

.input-control {
	width: 100%;
	height: 80rpx;
	background-color: #3A3A3A;
	border: 1px solid #4A4A4A;
	border-radius: 8rpx;
	padding: 0 20rpx;
	font-size: 28rpx;
	color: #FFFFFF;
	box-sizing: border-box;
}

/* 费用信息 */
.fee-info {
	background-color: #3A3A3A;
	border-radius: 8rpx;
	padding: 20rpx;
	margin-bottom: 30rpx;
}

.fee-item {
	font-size: 26rpx;
	color: #CCCCCC;
	margin-bottom: 10rpx;
}

.fee-item:last-child {
	margin-bottom: 0;
}

/* 提交按钮 */
.submit-btn {
	width: 100%;
	line-height: 88rpx;
	background-color: #007AFF;
	color: #FFFFFF;
	font-size: 32rpx;
	border-radius: 44rpx;
	border: none;
	margin: 40rpx 0;
}

/* 温馨提示 */
.tips-section {
	margin-top: 20rpx;
}

.tips-title {
	font-size: 28rpx;
	color: #FFFFFF;
	font-weight: bold;
	margin-bottom: 10rpx;
}

.tips-item {
	font-size: 24rpx;
	color: #999999;
	margin-bottom: 5rpx;
	line-height: 1.6;
}
</style>