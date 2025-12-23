<template>
	<view class="invite-container">
		<view class="invite-card">
			<view class="card-header">
				<image class="invite-icon" src="/static/images/invite-icon.png"></image>
				<text class="invite-title">邀请好友加入我们</text>
			</view>
			
			<view class="reward-info">
				<text class="reward-text">邀请3位好友注册并完成交易</text>
				<text class="reward-amount">获得团队奖励</text>
			</view>
			
			<view class="referral-code">
				<text class="code-label">您的专属邀请码</text>
				<view class="code-box">
					<text class="code-value">{{referralCode}}</text>
					<button class="copy-btn" @tap="copyCode">复制</button>
				</view>
			</view>
			
			<view class="referral-link">
				<text class="link-label">邀请链接</text>
				<view class="link-box">
					<text class="link-value">{{referralLink}}</text>
					<button class="copy-btn" @tap="copyLink">复制</button>
				</view>
			</view>
			
		</view>
		  
		<view class="referral-rules">
			<view class="rules-title">活动规则</view>
			<view class="rules-content">
				<view class="rule-item">1. 通过您的专属邀请码或链接邀请好友注册。</view>
				<view class="rule-item">2. 被邀请人需完成交易所连接和首笔交易。</view>
				<view class="rule-item">3. 邀请奖励将在被邀请人完成首笔交易后24小时内发放。</view>  
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				referralCode: '',
				referralLink: '',
				inviteStats: {
					invitedCount: 0,
					registeredCount: 0,
					totalReward: 0
				}
			}
		},
		onLoad() {
			// 获取用户ID
			this.userId = uni.getStorageSync('userid');
			
			// 加载邀请码
			this.loadInviteCode();
		},
		methods: {
			// 加载邀请码
			loadInviteCode() {
				this.req({
					url: "user/invitecode",  
					success: (res) => {
						if (res.code == 1) { 
							this.referralCode = res.data.invite_code || ''; 
							// 生成邀请链接
							this.generateInviteLink();
							
							// 更新邀请统计
							if (res.data.invite_stats) {
								this.inviteStats = res.data.invite_stats;
							}
						} else {
							uni.showToast({
								title: res.msg || '获取邀请码失败',
								icon: 'none'
							});
						}
					},
					fail: (err) => {
						uni.showToast({
							title: '网络请求失败，请重试',
							icon: 'none'
						});
					}
				});
			},
			
			// 生成邀请链接
			generateInviteLink() {
				if (!this.referralCode) return;
				
				// 生成完整的URL，使用当前域名+路径
				let baseUrl = '';
				// #ifdef H5
				baseUrl = window.location.protocol + '//' + window.location.host;
				// #endif 
				// 构建注册页面的URL，使用code参数
				this.referralLink = baseUrl + '/#/pages/login/register?code=' + this.referralCode;
			},
			
			copyCode() {
				uni.setClipboardData({
					data: this.referralCode,
					success: () => {
						uni.showToast({
							title: '邀请码已复制',
							icon: 'success'
						});
					}
				});
			},
			
			copyLink() {
				uni.setClipboardData({
					data: this.referralLink,
					success: () => {
						uni.showToast({
							title: '邀请链接已复制',
							icon: 'success'
						});
					}
				});
			},
			
			shareToWechat() {
				this.showShareMessage('微信');
			},
			
			shareToQQ() {
				this.showShareMessage('QQ');
			},
			
			shareToWeibo() {
				this.showShareMessage('微博');
			},
			
			shareMore() {
				uni.showActionSheet({
					itemList: ['复制链接', '短信分享', '邮件分享'],
					success: (res) => {
						if (res.tapIndex === 0) {
							this.copyLink();
						} else {
							this.showShareMessage('其他应用');
						}
					}
				});
			},
			
			showShareMessage(platform) {
				uni.showToast({
					title: '已分享到' + platform,
					icon: 'success'
				});
			}
		}
	}
</script>

<style>
	.invite-container {
		padding: 30rpx;
		background-color: #F5F5F5;
		min-height: 100vh;
	}
	
	.invite-card {
		background-color: #FFFFFF;
		border-radius: 12rpx;
		padding: 30rpx;
		margin-bottom: 30rpx;
	}
	
	.card-header {
		display: flex;
		flex-direction: column;
		align-items: center;
		margin-bottom: 30rpx;
	}
	
	.invite-icon {
		width: 120rpx;
		height: 120rpx;
		margin-bottom: 20rpx;
	}
	
	.invite-title {
		font-size: 36rpx;
		font-weight: bold;
		color: #333;
	}
	
	.reward-info {
		text-align: center;
		margin-bottom: 40rpx;
	}
	
	.reward-text {
		font-size: 28rpx;
		color: #666;
		margin-bottom: 10rpx;
		display: block;
	}
	
	.reward-amount {
		font-size: 32rpx;
		color: #333;
		display: block;
	}
	
	.highlight {
		color: #FF9500;
		font-weight: bold;
	}
	
	.referral-code, .referral-link {
		margin-bottom: 30rpx;
	}
	
	.code-label, .link-label {
		font-size: 28rpx;
		color: #666;
		margin-bottom: 10rpx;
		display: block;
	}
	
	.code-box, .link-box {
		display: flex;
		justify-content: space-between;
		align-items: center;
		border: 1rpx solid #E0E0E0;
		border-radius: 8rpx;
		padding: 20rpx;
	}
	
	.code-value, .link-value {
		font-size: 28rpx;
		color: #333;
		font-weight: bold;
		flex: 1;
		word-break: break-all;
	}
	
	.link-value {
		font-size: 24rpx;
	}
	
	.copy-btn {
		width: 120rpx;
		height: 60rpx;
		line-height: 60rpx;
		font-size: 24rpx;
		margin-left: 20rpx;
		background-color: #007AFF;
		color: #FFFFFF;
		border-radius: 30rpx;
	}
	
	.share-methods {
		display: flex;
		justify-content: space-around;
		margin-top: 40rpx;
	}
	
	.share-btn {
		display: flex;
		flex-direction: column;
		align-items: center;
		background-color: transparent;
		width: 140rpx;
		padding: 0;
		line-height: 1.5;
	}
	
	.share-btn::after {
		border: none;
	}
	
	.share-icon {
		width: 80rpx;
		height: 80rpx;
		margin-bottom: 10rpx;
	}
	
	.referral-stats, .referral-rules {
		background-color: #FFFFFF;
		border-radius: 12rpx;
		padding: 30rpx;
		margin-bottom: 30rpx;
	}
	
	.stats-title, .rules-title {
		font-size: 32rpx;
		font-weight: bold;
		color: #333;
		margin-bottom: 30rpx;
	}
	
	.stats-card {
		display: flex;
		justify-content: space-around;
		align-items: center;
	}
	
	.stats-item {
		display: flex;
		flex-direction: column;
		align-items: center;
	}
	
	.stats-value {
		font-size: 40rpx;
		font-weight: bold;
		color: #007AFF;
		margin-bottom: 10rpx;
	}
	
	.stats-label {
		font-size: 24rpx;
		color: #666;
	}
	
	.stats-divider {
		width: 1rpx;
		height: 60rpx;
		background-color: #E0E0E0;
	}
	
	.rules-content {
		padding: 20rpx;
		background-color: #F8F8F8;
		border-radius: 8rpx;
	}
	
	.rule-item {
		font-size: 26rpx;
		color: #666;
		margin-bottom: 16rpx;
		line-height: 1.5;
	}
</style> 