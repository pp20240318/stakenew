<template>
	<view class="team-container">
		<!-- 导航栏 -->
	    <view class="navbar">
			<view class="back-btn" @tap="navigateBack"> 
       			 <text class="back-icon">&#8592;</text>
			</view>
			<text class="page-title">{{ $t('我的团队') }}</text>
			<view class="navbar-right"></view>
		</view>
		
		<!-- 收益统计卡片 -->
		<view class="stats-card">
			<view class="stats-header">
				<text class="stats-title">{{ $t('团队总收益') }}</text>
				<text class="stats-value">{{ teamStats.totalReward || '0.00' }} USDT</text>
			</view>
			
			<view class="stats-detail">
				<view class="stat-item">
					<text class="stat-label">{{ $t('团队人数') }}</text>
					<text class="stat-value">{{ teamMembers.length || 0 }}</text>
				</view>
			</view>
		</view>
		
		<!-- 日期筛选 -->
		<view class="date-filter-card">
			<view class="date-filter">
				<view class="date-input" @tap="showStartDatePicker">
					<text class="date-label">{{ $t('开始日期') }}:</text>
					<text class="date-value">{{ startDate || $t('请选择开始日期') }}</text>
				</view>
				<view class="date-separator">-</view>
				<view class="date-input" @tap="showEndDatePicker">
					<text class="date-label">{{ $t('结束日期') }}:</text>
					<text class="date-value">{{ endDate || $t('请选择结束日期') }}</text>
				</view>
				<view class="filter-btn" @tap="applyDateFilter">
					<text>{{ $t('筛选') }}</text>
				</view>
			</view>
		</view>
		
		<!-- 团队成员列表 -->
		<view class="team-list-card">
			<view class="list-header">
				<text class="header-item" style="flex: 2;">{{ $t('账户') }}</text>
				<text class="header-item">{{ $t('等级') }}</text>
				<text class="header-item" style="">{{ $t('盈利') }}</text>
				<text class="header-item" style="flex: 2;">{{ $t('注册时间') }}</text>
			</view>
			
			<view class="list-content">
				<template v-if="teamMembers.length > 0">
					<view class="list-item" v-for="(member, index) in teamMembers" :key="index">
						<text class="item-text" style="flex: 2;">{{ maskAccount(member.username) }}</text>
						<text class="item-text">{{ 'Level ' + member.level }}</text>
					    <text class="item-text">{{   member.profit_amount }}</text>
						<text class="item-text" style="flex: 2;">{{ formatDate(member.register_time) }}</text>
					</view>
				</template>
				<view class="empty-data" v-else>
					<image class="empty-image" src="/static/images/empty-data.png"></image>
					<text class="empty-text">{{ $t('暂无团队成员') }}</text>
				</view>
			</view>
		</view>
		
		<!-- GraceUI日期选择器 - 仅H5平台 -->
		<view v-if="showDatePickerModal" class="grace-mask" @tap="cancelDatePicker" catchtouchmove="true">
			<view class="grace-date-picker" catchtap="true">
				<view class="grace-date-header">
					<view class="grace-date-header-btn" @tap="cancelDatePicker">{{ $t('取消') }}</view>
					<view class="grace-date-header-btn confirm" @tap="confirmDatePicker">{{ $t('确定') }}</view>
				</view>
				<view class="grace-date-content">
					<!-- 年月日选择器 -->
					<picker-view indicator-style="height: 50px;" class="grace-date-picker-view" :value="pickerValue" @change="pickerChange">
						<picker-view-column>
							<view class="grace-date-item" v-for="(year, index) in years" :key="index">
								{{ year }}
							</view>
						</picker-view-column>
						<picker-view-column>
							<view class="grace-date-item" v-for="(month, index) in months" :key="index">
								{{ month }}
							</view>
						</picker-view-column>
						<picker-view-column>
							<view class="grace-date-item" v-for="(day, index) in days" :key="index">
								{{ day }}
							</view>
						</picker-view-column>
					</picker-view>
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
			teamMembers: [],
			startDate: '',
			endDate: '',
			showDatePickerModal: false,
			currentDatePickerType: '', // 'start' 或 'end'
			
			// 日期选择器数据
			pickerValue: [0, 0, 0],
			years: [],
			months: [],
			days: [],
			selectedYear: new Date().getFullYear(),
			selectedMonth: new Date().getMonth() + 1,
			selectedDay: new Date().getDate()
		}
	},
	created() {
		// 初始化日期选择器数据
		this.initDatePicker();
	},
	onLoad() {
		this.fetchTeamData();
	},
	methods: {
		navigateBack() {
			uni.navigateBack();
		},
		
		// 初始化日期选择器
		initDatePicker() {
			const currentYear = new Date().getFullYear();
			
			// 生成年份数组（当前年份前后10年）
			this.years = [];
			for (let i = currentYear - 10; i <= currentYear + 10; i++) {
				this.years.push(i);
			}
			
			// 生成月份数组
			this.months = [];
			for (let i = 1; i <= 12; i++) {
				this.months.push(i);
			}
			
			// 更新日期数组
			this.updateDays();
			
			// 设置默认值
			const yearIndex = this.years.indexOf(this.selectedYear);
			const monthIndex = this.months.indexOf(this.selectedMonth);
			const dayIndex = this.days.indexOf(this.selectedDay);
			
			this.pickerValue = [yearIndex, monthIndex, dayIndex];
		},
		
		// 更新日期数组
		updateDays() {
			const daysInMonth = new Date(this.selectedYear, this.selectedMonth, 0).getDate();
			this.days = [];
			for (let i = 1; i <= daysInMonth; i++) {
				this.days.push(i);
			}
		},
		
		// 显示开始日期选择器
		showStartDatePicker() {
			this.currentDatePickerType = 'start';
			this.setInitialDateFromString(this.startDate);
			this.showDatePickerModal = true;
		},
		
		// 显示结束日期选择器
		showEndDatePicker() {
			this.currentDatePickerType = 'end';
			this.setInitialDateFromString(this.endDate);
			this.showDatePickerModal = true;
		},
		
		// 设置日期选择器初始值
		setInitialDateFromString(dateStr) {
			if (dateStr) {
				const dateParts = dateStr.split('-');
				if (dateParts.length === 3) {
					this.selectedYear = parseInt(dateParts[0]);
					this.selectedMonth = parseInt(dateParts[1]);
					this.selectedDay = parseInt(dateParts[2]);
				}
			} else {
				// 使用当前日期
				const now = new Date();
				this.selectedYear = now.getFullYear();
				this.selectedMonth = now.getMonth() + 1;
				this.selectedDay = now.getDate();
			}
			
			// 更新日期数组
			this.updateDays();
			
			// 设置选择器值
			const yearIndex = this.years.indexOf(this.selectedYear);
			const monthIndex = this.months.indexOf(this.selectedMonth);
			const dayIndex = this.days.indexOf(this.selectedDay);
			
			this.pickerValue = [yearIndex, monthIndex, dayIndex];
		},
		
		// 处理选择器变化
		pickerChange(e) {
			const values = e.detail.value;
			
			// 更新选中的年月日
			this.selectedYear = this.years[values[0]];
			this.selectedMonth = this.months[values[1]];
			
			// 检查日期是否需要更新（例如，如果月份改变，天数可能需要调整）
			const prevDays = this.days.length;
			this.updateDays();
			
			// 如果天数变化了，确保日期选择合法
			if (this.days.length !== prevDays) {
				if (values[2] >= this.days.length) {
					values[2] = this.days.length - 1;
				}
			}
			
			this.selectedDay = this.days[values[2]];
			
			// 更新选择器值
			this.pickerValue = values;
		},
		
		// 取消日期选择
		cancelDatePicker() {
			this.showDatePickerModal = false;
		},
		
		// 确认日期选择
		confirmDatePicker() {
			// 格式化选中的日期
			const formattedDate = `${this.selectedYear}-${String(this.selectedMonth).padStart(2, '0')}-${String(this.selectedDay).padStart(2, '0')}`;
			
			// 根据当前类型更新对应的日期
			if (this.currentDatePickerType === 'start') {
				this.startDate = formattedDate;
			} else if (this.currentDatePickerType === 'end') {
				this.endDate = formattedDate;
			}
			
			// 关闭选择器
			this.showDatePickerModal = false;
		},
		
		// 应用日期筛选
		applyDateFilter() {
			// 验证日期选择
			if (this.startDate && this.endDate) {
				const startTimestamp = this.dateToTimestamp(this.startDate);
				const endTimestamp = this.dateToTimestamp(this.endDate);
				
				if (startTimestamp > endTimestamp) {
					uni.showToast({
						title: this.$t('开始日期不能大于结束日期'),
						icon: 'none'
					});
					return;
				}
			}
			
			// 刷新数据
			this.fetchTeamData();
		},
		
		// 获取团队数据
		fetchTeamData() { 
			// 显示加载
			uni.showLoading({
				title: this.$t('加载中...')
			});
			
			// 准备请求参数
			const params = {};
			
			// 添加日期筛选参数
			if (this.startDate) {
				params.start_date = this.dateToTimestamp(this.startDate + ' 00:00:00');
			}
			
			if (this.endDate) {
				params.end_date = this.dateToTimestamp(this.endDate + ' 23:59:59');
			}
			
			this.req({
				url: "user/teaminfo",
				data: params,
				success: (res) => {
					console.log(res)
					if (res.code === 1) {
						const data = res.data;
						this.teamStats = data.stats || this.teamStats;
						this.teamMembers = data.members || [];
					} else {
						uni.showToast({
							title: res.msg || this.$t('获取团队数据失败'),
							icon: 'none'
						});
					}
					uni.hideLoading();
				},
				fail: (err) => {
					uni.showToast({
						title: this.$t('网络错误，请稍后重试'),
						icon: 'none'
					});
					console.error('获取团队数据失败:', err);
					uni.hideLoading();
				}
			});
		},
		
		// 转换日期为时间戳
		dateToTimestamp(dateStr) {
			if (!dateStr) return null;
			return Math.floor(new Date(dateStr).getTime() / 1000);
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

.stats-card {
	margin: 15px;
	background-color: #007AFF;
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

/* 添加日期筛选样式 */
.date-filter-card {
	margin: 15px;
	background-color: #fff;
	border-radius: 10px;
	overflow: hidden;
	box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
}

.date-filter {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 15px;
}

.date-input {
	display: flex;
	flex-direction: column;
	flex: 1;
}

.date-label {
	font-size: 12px;
	color: #666;
	margin-bottom: 5px;
}

.date-value {
	font-size: 14px;
	color: #333;
}

.date-separator {
	color: #333;
	margin: 0 10px;
	align-self: flex-end;
	padding-bottom: 5px;
}

.filter-btn {
	background-color: #007AFF;
	padding: 8px 15px;
	border-radius: 5px;
	margin-left: 10px;
	color: #fff;
}

/* GraceUI日期选择器样式 */
.grace-mask {
	position: fixed;
	left: 0;
	top: 0;
	right: 0;
	bottom: 0;
	background-color: rgba(0, 0, 0, 0.5);
	z-index: 99;
	display: flex;
	align-items: flex-end;
	justify-content: center;
}

.grace-date-picker {
	width: 100%;
	background-color: #FFFFFF;
	border-radius: 12px 12px 0 0;
	overflow: hidden;
}

.grace-date-header {
	display: flex;
	justify-content: space-between;
	padding: 15px;
	border-bottom: 1px solid #f1f1f1;
}

.grace-date-header-btn {
	font-size: 16px;
	color: #333;
	padding: 0 10px;
}

.grace-date-header-btn.confirm {
	color: #007AFF;
}

.grace-date-content {
	padding: 10px 0;
}

.grace-date-picker-view {
	width: 100%;
	height: 220px;
}

.grace-date-item {
	height: 50px;
	line-height: 50px;
	text-align: center;
	font-size: 16px;
	color: #333;
}
</style> 