<template>
	<view class="container">
		<view class="balance-info">
			<text class="balance-label">{{t('withdraw.accountBalance')}}</text>
			<view class="balance-amount">
				<text class="amount">$</text>
				<text class="currency">{{userStore.formatBalance(userInfo?.balance)}}</text>
			</view>
			<view class="limit-info">
				<text class="limit-text">{{t('withdraw.minAmount')}}: $100.0</text>
				<text class="limit-text">{{t('withdraw.maxAmount')}}: $10{{t('withdraw.tenThousand')}}</text>
			</view>
		</view>

		<view class="currency-select">
			<!-- <picker @change="onCurrencyChange" :value="currencyIndex" :range="currencyTypes">
				<view class="picker-value">{{currencyTypes[currencyIndex]}}</view>
			</picker> -->
			<text class="picker-value">TRC20-USDT</text>
		</view>

		<view class="amount-input-container">
			<input 
				type="digit" 
				v-model="amount" 
				placeholder="0"
				placeholder-class="input-placeholder"
				class="amount-input"
			/>
		</view>
		
		<!-- <view class="currency-info">
			<text>USDT: USDT 1</text>
		</view> -->
		
		<view class="fee-info">
			<text>{{t('withdraw.fee')}} 0 {{t('withdraw.dollar')}}</text>
		</view>

		<view class="address-input">
			<input 
				type="text" 
				:placeholder="t('withdraw.enterAddress')"
				placeholder-class="input-placeholder"
				class="address-input-field"
				v-model="address"
			/>
		</view>

		<view class="password-input">
			<input 
				type="password" 
				:placeholder="t('withdraw.enterPayPassword')"
				placeholder-class="input-placeholder"
				class="password-input-field"
				v-model="password"
			/>
		</view>

		<view class="tips-section">
			<text class="tip-title">{{t('withdraw.tips')}}</text>
			<text class="tip-text">1.{{t('withdraw.tip1')}}</text>
			<text class="tip-text">2.{{t('withdraw.tip2')}}</text>
		</view>

		<view class="bottom-button">
			<button class="submit-btn" @click="handleWithdraw">{{t('withdraw.confirmWithdraw')}}</button>
		</view>
	</view>
</template>

<script setup>
import { ref } from 'vue'
import { useUserStore } from '@/stores/user'
import { addWithdrawl } from '@/api/wallet'
import { getBalance } from '@/api/user.js'
import { t } from '@/i18n/index.js'
import { onShow } from '@dcloudio/uni-app'

const amount = ref('')
const address = ref('')
const password = ref('')
const userStore = useUserStore()
const userInfo = ref({})

// 货币类型
const currencyTypes = ['USDT']
const currencyIndex = ref(0)

onShow(()=>{
	userStore.checkLogin()
	
	// Refresh balance from API
	getBalance().then(res => {
		if (res.code === 1 && res.data) {
			userStore.userInfo.balance = res.data.balance
		}
	})
	
	userInfo.value = userStore.userInfo
})

// 货币选择变更
const onCurrencyChange = (e) => {
	currencyIndex.value = e.detail.value
}

const handleWithdraw = () => {
	if (!amount.value) {
		uni.showToast({
			title: t('withdraw.pleaseEnterAmount'),
			icon: 'none'
		})
		return
	}
	
	if (Number(amount.value) < 100) {
		uni.showToast({
			title: t('withdraw.amountTooSmall'),
			icon: 'none'
		})
		return
	}
	//提现金额不能超过10万
	if (Number(amount.value) > 100000) {
		uni.showToast({
			title: t('withdraw.amountTooLarge'),
			icon: 'none'
		})
		return
	}
	//提现金额不能超过账户余额
	if (Number(amount.value) > Number(userStore.userInfo?.balance)) {
		uni.showToast({
			title: t('withdraw.insufficientBalance'),
			icon: 'none'
		})
		return
	}
	
	if (!address.value) {
		uni.showToast({
			title: t('withdraw.pleaseEnterAddress'),
			icon: 'none'
		})
		return
	}
	
	if (!password.value) {
		uni.showToast({
			title: t('withdraw.pleaseEnterPassword'),
			icon: 'none'
		})
		return
	}
	addWithdrawl({
		selecttype:1,
		money: amount.value,
		address: address.value,
		paypassword: password.value,
		show_percent:1
	}).then(res => {
		if (res.code == 1) {
			uni.showToast({
				title: res.msg,
				icon: 'none'
			})
			
			// Wait for toast to show before navigating back
			setTimeout(() => {
				uni.navigateBack()
			}, 1500)
		}
		else{
			uni.showToast({
				title: res.msg,
				icon: 'none'
			})
			return
		}

	})
	// uni.showToast({
	// 	title: t('withdraw.applicationSubmitted'),
	// 	icon: 'success'
	// })
}
</script>

<style>
.container {
	padding: 20rpx;
	background-color: #0D1F2D;
	min-height: 100vh;
}

.balance-info {
	background-color: rgba(16, 179, 176, 0.1);
	border-radius: 15rpx;
	padding: 30rpx;
	margin-bottom: 40rpx;
}

.balance-label {
	color: #999;
	font-size: 28rpx;
	margin-bottom: 10rpx;
	display: block;
}

.balance-amount {
	display: flex;
	align-items: baseline;
}

.currency {
	color: #fff;
	font-size: 32rpx;
	margin-left: 10rpx;
}

.amount {
	color: #fff;
	font-size: 48rpx;
	font-weight: bold;
}

.limit-info {
	margin-top: 10rpx;
}

.limit-text {
	color: #999;
	font-size: 24rpx;
	line-height: 1.5;
	display: block;
}

.section-title {
	color: #fff;
	font-size: 32rpx;
	margin-bottom: 20rpx;
	display: block;
}

.currency-select {
	background-color: rgba(255, 255, 255, 0.05);
	border-radius: 15rpx;
	padding: 20rpx;
	margin-bottom: 20rpx;
}

.picker-value {
	color: #fff;
	font-size: 32rpx;
}

.amount-input-container {
	background-color: rgba(255, 255, 255, 0.05);
	border-radius: 15rpx;
	padding: 20rpx;
	margin-bottom: 20rpx;
}

.amount-input {
	color: #fff;
	font-size: 40rpx;
	width: 100%;
}

.currency-info, .fee-info {
	color: #999;
	font-size: 28rpx;
	margin-bottom: 20rpx;
}

.address-input, .password-input {
	background-color: rgba(255, 255, 255, 0.05);
	border-radius: 15rpx;
	padding: 20rpx;
	margin-bottom: 20rpx;
}

.address-input-field, .password-input-field {
	color: #fff;
	font-size: 32rpx;
	width: 100%;
}

.input-placeholder {
	color: #666;
}

.bank-card {
	background-color: rgba(255, 255, 255, 0.05);
	border-radius: 15rpx;
	padding: 30rpx;
	display: flex;
	align-items: center;
	margin-bottom: 40rpx;
}

.card-info {
	flex: 1;
}

.bank-name {
	color: #fff;
	font-size: 28rpx;
	margin-bottom: 10rpx;
	display: block;
}

.card-number {
	color: #999;
	font-size: 24rpx;
}

.add-card {
	display: flex;
	align-items: center;
	color: #10B3B0;
	font-size: 28rpx;
	flex: 1;
}

.add-card .fas {
	margin-right: 10rpx;
}

.arrow {
	color: #999;
	font-size: 28rpx;
}

.tips-section {
	margin: 40rpx 0;
	padding: 0 20rpx;
}

.tip-title {
	color: #999;
	font-size: 28rpx;
	margin-bottom: 10rpx;
	display: block;
}

.tip-text {
	color: #999;
	font-size: 24rpx;
	line-height: 1.8;
	display: block;
}

.bottom-button {
	position: fixed;
	left: 0;
	right: 0;
	bottom: 0;
	padding: 20rpx;
	background-color: #0D1F2D;

}

.submit-btn {
	background-color: #10B3B0;
	color: #fff;
	font-size: 32rpx;
	padding: 20rpx;
	border-radius: 10rpx;
	height: 120rpx;
}
</style>