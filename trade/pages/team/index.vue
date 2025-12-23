<template>
	<view class="team-container">
		<!-- 导航栏 -->
		<view class="navbar">
			<view class="navbar-left" @tap="navigateBack">
				<text class="navbar-icon ri-arrow-left-s-line"></text>
			</view>
			<view class="navbar-title">我的团队</view>
		</view>
		
		<!-- 收益统计卡片 -->
		<view class="stats-card">
			<view class="stats-header">
				<text class="stats-title">团队总收益</text>
				<text class="stats-value">{{ teamStats.totalReward || '0.00' }} USDT</text>
			</view>
			
			<view class="stats-detail">
				<view class="stat-item">
					<text class="stat-label">团队人数</text>
					<text class="stat-value">{{ teamMembers.length || 0 }}</text>
				</view>
			</view>
		</view>
		
		<!-- 团队成员列表 -->
		<view class="team-list-card">
			<view class="list-header">
				<text class="header-item" style="flex: 2;">账户</text>
				<text class="header-item">等级</text>
				<text class="header-item" style="flex: 2;">注册时间</text>
			</view>
			
			<view class="list-content">
				<template v-if="teamMembers.length > 0">
					<view class="list-item" v-for="(member, index) in teamMembers" :key="index">
						<text class="item-text" style="flex: 2;">{{ maskAccount(member.username) }}</text>
						<text class="item-text">{{ 'Level ' + member.level }}</text>
						<text class="item-text" style="flex: 2;">{{ formatDate(member.register_time) }}</text>
					</view>
				</template>
				<view class="empty-data" v-else>
					<image class="empty-image" src="/static/images/empty-data.png"></image>
					<text class="empty-text">暂无团队成员</text>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
export default {
	data() {
		return {
			teamStats: {
				totalReward: '0.00'
			},
			teamMembers: []
		}
	},
	onLoad() {
		this.fetchTeamData();
	},
	methods: {
		navigateBack() {
			uni.navigateBack();
		},
		
		// 获取团队数据
		fetchTeamData() { 
			// 显示加载
			uni.showLoading({
				title: '加载中...'
			});
			
			this.req({
				url: "user/teaminfo", 
				success: (res) => {
					console.log(res)
					if (res.code === 1) {
						const data = res.data;
						this.teamStats = data.stats || this.teamStats;
						this.teamMembers = data.members || [];
					} else {
						uni.showToast({
							title: res.msg || '获取团队数据失败',
							icon: 'none'
						});
					}
					uni.hideLoading();
				},
				fail: (err) => {
					uni.showToast({
						title: '网络错误，请稍后重试',
						icon: 'none'
					});
					console.error('获取团队数据失败:', err);
					uni.hideLoading();
				}
			});
		},
		
		// 账号脱敏处理
		maskAccount(account) {
			if (!account) return '';
			
			if (account.includes('@')) {
				// 邮箱脱敏
				const parts = account.split('@');
				const name = parts[0];
				const domain = parts[1];
				
				if (name.length <= 3) {
					return name + '@' + domain;
				} else {
					return name.substring(0, 3) + '***@' + domain;
				}
			} else {
				// 手机号脱敏
				if (account.length <= 5) return account;
				return account.substring(0, 3) + '****' + account.substring(account.length - 3);
			}
		},
		
		// 格式化日期
		formatDate(timestamp) {
			if (!timestamp) return '';
			
			const date = new Date(timestamp * 1000);
			const year = date.getFullYear();
			const month = (date.getMonth() + 1).toString().padStart(2, '0');
			const day = date.getDate().toString().padStart(2, '0');
			
			return `${year}-${month}-${day}`;
		}
	}
}
</script>

<style>
.team-container {
	min-height: 100vh;
	background-color: #f5f7fa;
	padding-bottom: 20px;
}

.navbar {
	display: flex;
	align-items: center;
	height: 44px;
	background-color: #ffffff;
	border-bottom: 1px solid #eef0f5;
	padding: 0 15px;
	position: relative;
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

.stats-card {
	margin: 15px;
	background-color: #40DFBF;
	border-radius: 10px;
	padding: 20px;
	color: #fff;
	box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
}

.stats-header {
	margin-bottom: 15px;
}

.stats-title {
	font-size: 16px;
	opacity: 0.9;
}

.stats-value {
	display: block;
	font-size: 24px;
	font-weight: bold;
	margin-top: 5px;
}

.stats-detail {
	display: flex;
	justify-content: space-between;
	border-top: 1px solid rgba(255, 255, 255, 0.2);
	padding-top: 15px;
}

.stat-item {
	flex: 1;
	text-align: center;
}

.stat-label {
	font-size: 14px;
	opacity: 0.8;
}

.stat-value {
	display: block;
	font-size: 20px;
	font-weight: 500;
	margin-top: 5px;
}

.team-list-card {
	margin: 15px;
	background-color: #fff;
	border-radius: 10px;
	overflow: hidden;
	box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
}

.list-header {
	display: flex;
	background-color: #f8f9fa;
	padding: 15px;
	border-bottom: 1px solid #eef0f5;
}

.header-item {
	flex: 1;
	font-size: 14px;
	color: #666;
	text-align: center;
}

.list-content {
	max-height: 400px;
	overflow-y: auto;
}

.list-item {
	display: flex;
	padding: 15px;
	border-bottom: 1px solid #eef0f5;
}

.item-text {
	flex: 1;
	font-size: 14px;
	color: #333;
	text-align: center;
}

.empty-data {
	padding: 40px 0;
	text-align: center;
}

.empty-image {
	width: 80px;
	height: 80px;
	margin-bottom: 10px;
}

.empty-text {
	font-size: 14px;
	color: #999;
}
</style> 