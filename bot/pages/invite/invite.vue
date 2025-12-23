<template>
	<view class="invite-container">
			<view class="navbar">
				<view class="back-btn" @tap="navigateBack"> 
					<text class="back-icon">&#8592;</text>
				</view>
				<text class="page-title">{{ $t('邀请好友') }}</text>
				<view class="navbar-right"></view>
			</view>

		<view class="invite-card">
			<view class="card-header">
				<image class="invite-icon" src="/static/images/invite-icon.png"></image>
				<text class="invite-title">{{ $t('邀请好友加入我们') }}</text>
			</view>
			
			
			<view class="referral-code">
				<text class="code-label">{{ $t('您的专属邀请码') }}</text>
				<view class="code-box">
					<text class="code-value">{{referralCode}}</text>
					<button class="copy-btn" @tap="copyCode">{{ $t('复制') }}</button>
				</view>
			</view>
			
			<view class="referral-link">
				<text class="link-label">{{ $t('邀请链接') }}</text>
				<view class="link-box">
					<text class="link-value">{{referralLink}}</text>
					<button class="copy-btn" @tap="copyLink">{{ $t('复制') }}</button>
				</view>
			</view>
			
		</view>
		  
		<view class="referral-rules">
			<view class="rules-title">{{ $t('活动规则') }}</view>
			<view class="rules-content">
				<view class="rule-item">{{ $t('1. 通过您的专属邀请码或链接邀请好友注册。') }}</view>
				<view class="rule-item">{{ $t('2. 邀请好友可以提升机器人等级') }}</view> 
				<view class="rule-item">{{ $t('一级开启200U ') }}</view> 
				<view class="rule-item">{{ $t('二级开启500U（需要介绍3-5人才能开启）') }}</view> 
				<view class="rule-item">{{ $t('三级开启1000U（需要介绍5-10人才能开启）') }}</view> 
				<view class="rule-item">{{ $t('四级开启2000U（需要介绍10-15人才能开启）') }}</view> 
				<view class="rule-item">{{ $t('五级开启5000U（需要介绍25人以上）') }}</view> 

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
								title: res.msg || this.$t('获取邀请码失败'),
								icon: 'none'
							});
						}
					},
					fail: (err) => {
						uni.showToast({
							title: this.$t('网络请求失败，请重试'),
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
							title: this.$t('邀请码已复制'),
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
							title: this.$t('邀请链接已复制'),
							icon: 'success'
						});
					}
				});
			},
				
			// 返回上一页
			navigateBack() {
				uni.navigateBack();
			},
			
			shareToWechat() {
				this.showShareMessage(this.$t('微信'));
			},
			
			shareToQQ() {
				this.showShareMessage(this.$t('QQ'));
			},
			
			shareToWeibo() {
				this.showShareMessage(this.$t('微博'));
			},
			
			shareMore() {
				uni.showActionSheet({
					itemList: [this.$t('复制链接'), this.$t('短信分享'), this.$t('邮件分享')],
					success: (res) => {
						if (res.tapIndex === 0) {
							this.copyLink();
						} else {
							this.showShareMessage(this.$t('其他应用'));
						}
					}
				});
			},
			
			showShareMessage(platform) {
				uni.showToast({
					title: this.$t('已分享到') + platform,
					icon: 'success'
				});
			}
		}
	}
</script>

<style>

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

.navbar-left {
	position: absolute;
	left: 15px;
}

.navbar-icon {
	font-size: 24px;
	color: #333;
}

.navbar-title {
	flex: 1;
	text-align: center;
	font-size: 18px;
	font-weight: 500;
	color: #333;
}
	.invite-container { 
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