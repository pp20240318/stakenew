<template>
	<view class="settings-container">
		<view class="settings-section user-section">
			<view class="user-info">
				<image class="user-avatar" src="/static/images/avatar.png"></image>
				<view class="user-details">
					<text class="user-name">张三</text>
					<text class="user-email">zhangsan@example.com</text>
				</view>
			</view>
			<button class="edit-profile-btn" @tap="editProfile">编辑个人资料</button>
		</view>
		
		<view class="settings-section">
			<view class="section-title">账户安全</view>
			
			<view class="settings-item" @tap="goToPage('/pages/settings/security/change-password')">
				<text class="item-title">修改密码</text>
				<view class="item-right">
					<text class="arrow">></text>
				</view>
			</view>
			
			<view class="settings-item" @tap="goToPage('/pages/settings/security/google-auth')">
				<text class="item-title">Google 两步验证</text>
				<view class="item-right">
					<text class="item-status">未开启</text>
					<text class="arrow">></text>
				</view>
			</view>
			
			<view class="settings-item" @tap="goToPage('/pages/settings/security/sms-auth')">
				<text class="item-title">短信验证</text>
				<view class="item-right">
					<text class="item-status active">已开启</text>
					<text class="arrow">></text>
				</view>
			</view>
		</view>
		
		<view class="settings-section">
			<view class="section-title">交易设置</view>
			
			<view class="settings-item">
				<text class="item-title">默认交易货币</text>
				<view class="item-right">
					<picker mode="selector" :range="currencies" @change="onCurrencyChange">
						<text class="item-value">{{ defaultCurrency }}</text>
					</picker>
					<text class="arrow">></text>
				</view>
			</view>
			
			<view class="settings-item">
				<text class="item-title">交易确认</text>
				<view class="item-right">
					<switch :checked="tradeConfirm" @change="toggleTradeConfirm" color="#40DFBF"/>
				</view>
			</view>
		</view>
		
		<view class="settings-section">
			<view class="section-title">通知设置</view>
			
			<view class="settings-item">
				<text class="item-title">价格提醒</text>
				<view class="item-right">
					<switch :checked="priceAlert" @change="togglePriceAlert" color="#40DFBF"/>
				</view>
			</view>
			
			<view class="settings-item">
				<text class="item-title">交易通知</text>
				<view class="item-right">
					<switch :checked="tradeNotification" @change="toggleTradeNotification" color="#40DFBF"/>
				</view>
			</view>
			
			<view class="settings-item">
				<text class="item-title">系统公告</text>
				<view class="item-right">
					<switch :checked="systemNotification" @change="toggleSystemNotification" color="#40DFBF"/>
				</view>
			</view>
		</view>
		
		<view class="settings-section">
			<view class="section-title">应用设置</view>
			
			<view class="settings-item" @tap="goToPage('/pages/settings/language/language')">
				<text class="item-title">语言设置</text>
				<view class="item-right">
					<text class="item-value">简体中文</text>
					<text class="arrow">></text>
				</view>
			</view>
			
			<view class="settings-item">
				<text class="item-title">深色模式</text>
				<view class="item-right">
					<switch :checked="darkMode" @change="toggleDarkMode" color="#40DFBF"/>
				</view>
			</view>
			
			<view class="settings-item">
				<text class="item-title">清除缓存</text>
				<view class="item-right">
					<text class="item-value">{{ cacheSize }}</text>
					<text class="arrow">></text>
				</view>
			</view>
		</view>
		
		<view class="settings-section">
			<view class="section-title">其他</view>
			
			<view class="settings-item" @tap="goToPage('/pages/settings/about/about')">
				<text class="item-title">关于我们</text>
				<view class="item-right">
					<text class="arrow">></text>
				</view>
			</view>
			
			<view class="settings-item" @tap="goToPage('/pages/settings/about/terms')">
				<text class="item-title">用户协议</text>
				<view class="item-right">
					<text class="arrow">></text>
				</view>
			</view>
			
			<view class="settings-item" @tap="goToPage('/pages/settings/about/privacy')">
				<text class="item-title">隐私政策</text>
				<view class="item-right">
					<text class="arrow">></text>
				</view>
			</view>
			
			<view class="settings-item" @tap="feedback">
				<text class="item-title">意见反馈</text>
				<view class="item-right">
					<text class="arrow">></text>
				</view>
			</view>
		</view>
		
		<view class="logout-section">
			<button class="logout-btn" @tap="logout">退出登录</button>
		</view>
		
		<view class="version-info">
			<text>版本号: v1.0.0</text>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				defaultCurrency: 'USDT',
				currencies: ['USDT', 'BTC', 'ETH', 'BNB'],
				tradeConfirm: true,
				priceAlert: true,
				tradeNotification: true,
				systemNotification: true,
				darkMode: false,
				cacheSize: '25.6MB'
			}
		},
		methods: {
			editProfile() {
				uni.navigateTo({
					url: '/pages/profile/profile'
				});
			},
			
			goToPage(url) {
				uni.navigateTo({
					url: url
				});
			},
			
			onCurrencyChange(e) {
				const index = e.detail.value;
				this.defaultCurrency = this.currencies[index];
				
				uni.showToast({
					title: '设置已保存',
					icon: 'success'
				});
			},
			
			toggleTradeConfirm(e) {
				this.tradeConfirm = e.detail.value;
				this.showSaveToast();
			},
			
			togglePriceAlert(e) {
				this.priceAlert = e.detail.value;
				this.showSaveToast();
			},
			
			toggleTradeNotification(e) {
				this.tradeNotification = e.detail.value;
				this.showSaveToast();
			},
			
			toggleSystemNotification(e) {
				this.systemNotification = e.detail.value;
				this.showSaveToast();
			},
			
			toggleDarkMode(e) {
				this.darkMode = e.detail.value;
				// 应用深色模式的逻辑
				this.showSaveToast();
			},
			
			showSaveToast() {
				uni.showToast({
					title: '设置已保存',
					icon: 'success'
				});
			},
			
			feedback() {
				uni.showModal({
					title: '意见反馈',
					content: '请通过以下方式联系我们:\nEmail: support@tradebot.com\n电话: 400-123-4567',
					showCancel: false,
					confirmText: '确定'
				});
			},
			
			logout() {
				uni.showModal({
					title: '确认退出',
					content: '确定要退出登录吗？',
					success: (res) => {
						if (res.confirm) {
							// 清除登录状态
							// 跳转到登录页
							uni.reLaunch({
								url: '/pages/login/index'
							});
						}
					}
				});
			}
		}
	}
</script>

<style>
	.settings-container {
		padding: 30rpx;
		background-color: #F5F5F5;
		min-height: 100vh;
	}
	
	.settings-section {
		background-color: #FFFFFF;
		border-radius: 12rpx;
		padding: 30rpx;
		margin-bottom: 30rpx;
	}
	
	.user-section {
		display: flex;
		flex-direction: column;
		align-items: center;
	}
	
	.user-info {
		display: flex;
		align-items: center;
		width: 100%;
		margin-bottom: 30rpx;
	}
	
	.user-avatar {
		width: 128rpx;
		height: 128rpx;
		border-radius: 64rpx;
		margin-right: 30rpx;
	}
	
	.user-details {
		flex: 1;
	}
	
	.user-name {
		font-size: 36rpx;
		font-weight: bold;
		color: #333;
		margin-bottom: 10rpx;
		display: block;
	}
	
	.user-email {
		font-size: 28rpx;
		color: #666;
		display: block;
	}
	
	.edit-profile-btn {
		width: 100%;
		height: 80rpx;
		line-height: 80rpx;
		background-color: #F2F2F2;
		color: #333;
		font-size: 28rpx;
		border-radius: 8rpx;
	}
	
	.section-title {
		font-size: 28rpx;
		color: #999;
		margin-bottom: 20rpx;
	}
	
	.settings-item {
		display: flex;
		justify-content: space-between;
		align-items: center;
		height: 100rpx;
		border-bottom: 1rpx solid #F2F2F2;
	}
	
	.settings-item:last-child {
		border-bottom: none;
	}
	
	.item-title {
		font-size: 30rpx;
		color: #333;
	}
	
	.item-right {
		display: flex;
		align-items: center;
	}
	
	.item-value, .item-status {
		font-size: 28rpx;
		color: #999;
		margin-right: 10rpx;
	}
	
	.item-status.active {
		color: #4CD964;
	}
	
	.arrow {
		font-size: 28rpx;
		color: #CCCCCC;
	}
	
	.logout-section {
		padding: 20rpx 0;
	}
	
	.logout-btn {
		width: 100%;
		height: 90rpx;
		line-height: 90rpx;
		background-color: #FF3B30;
		color: #FFFFFF;
		font-size: 32rpx;
		border-radius: 8rpx;
	}
	
	.version-info {
		text-align: center;
		margin-top: 30rpx;
		margin-bottom: 50rpx;
		color: #999;
		font-size: 24rpx;
	}
</style> 