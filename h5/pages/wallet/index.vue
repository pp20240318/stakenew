<template>
	<view class="wallet-container">
		<!-- 顶部导航栏 -->
		<view class="navbar">
			<view  @tap="navigateBack">
				<image src="/static/images/icons/back-arrow.svg" class="back-icon"></image>
			</view>
			<text class="page-title">{{ $t('钱包') }}</text>
			<view class="navbar-right"></view>
		</view>
		
		<!-- 钱包余额卡片 -->
		<view class="balance-card">
			<view class="balance-title">{{ $t('总资产') }} (USDT)</view>
			<view class="balance-amount">{{ formatBalance(balance) }}</view>
			<view class="balance-actions">
				<view class="action-btn deposit" @tap="go('/pages/recharge/depositDetail')">
					<image src="/static/images/icons/arrow-down.svg" class="action-icon"></image>
					<text class="action-text">{{ $t('充值') }}</text>
				</view>
				<view class="action-btn withdraw" @tap="go('/pages/recharge/withdrawal')">
					<image src="/static/images/icons/arrow-up.svg" class="action-icon"></image>
					<text class="action-text">{{ $t('提现') }}</text>
				</view>
			</view>
		</view>
		
		<!-- 交易记录 -->
		<view class="transactions-section">
			<view class="section-header">
				<text class="section-title">{{ $t('交易记录') }}</text>
			</view>
			
			<scroll-view 
				scroll-y="true" 
				class="transaction-list-scroll"
				@scrolltolower="loadMore"
				lower-threshold="50"
			>
				<view class="transaction-list" v-if="records.length > 0">
					<view v-for="(item, index) in records" :key="index" class="transaction-item">
						<view class="transaction-info">
							<view class="transaction-type">
								<text>{{ $t(item.type === 'recharge' ? '充值' : '提现') }}</text>
								<text class="transaction-status" :class="{'status-completed': item.status == 1, 'status-pending': item.status == 0, 'status-rejected': item.status == 2}">
									{{ $t(item.status == 0 ? '待审核' : (item.status == 1 ? '已完成' : (item.status == 2 ? '已拒绝' : '未知状态'))) }}
								</text>
							</view>
							<view class="transaction-date">{{ formatDateTime(item.formatted_time) }}</view>
							<view class="transaction-id">{{ $t('ID') }}: {{ item.id }}</view>
						</view>
						<view class="transaction-amount" :class="{'amount-in': item.type === 'recharge', 'amount-out': item.type === 'withdrawal'}">
							{{ item.type === 'recharge' ? '+' : '-' }}{{ formatBalance(item.target_num) }}
						</view>
					</view>
					
					<!-- 加载状态 -->
					<view class="loading-more" v-if="loading">
						<text class="loading-text">{{ $t('加载中...') }}</text>
					</view>
					
					<!-- 无更多数据 -->
					<view class="no-more" v-if="!hasMore && records.length > 0">
						<text class="no-more-text">{{ $t('没有更多数据了') }}</text>
					</view>
				</view>
				
				<view class="empty-state" v-else>
					<text class="empty-text">{{ $t('暂无交易记录') }}</text>
				</view>
			</scroll-view>
		</view>
	</view>
</template>

<script>
export default {
	data() {
		return {
			balance: 0,
			records: [],
			userId: 0,
			currentPage: 1,
			pageSize: 10,
			hasMore: true,
			loading: false,
			totalRecords: 0,
			truename:0
		}
	},
	onLoad() {
		// 获取用户ID
		this.userId = uni.getStorageSync('userid');
		
		// 加载钱包数据
		this.loadWalletData();
	},
	onShow() {
		// 页面显示时重新获取数据
		this.resetData(); 
	},
	methods: {
		go(url){  
			if(this.truename==1||url=="/pages/recharge/depositDetail")
				this.navigateTo(url);
			else { 
					uni.showToast({
						title: this.$t('请先进行实名认证，才可以充值,提现'),
						icon: 'none'
					});
			} 
				
		},
		// 重置数据
		resetData() {
			this.records = [];
			this.currentPage = 1;
			this.hasMore = true;
			this.loading = false;
		},
		
		// 加载钱包数据
		loadWalletData() {
			// 获取钱包余额
			this.loadWalletBalance();
			
			// 获取交易记录
			this.getTransactionList();
		},
		
		// 加载钱包余额
		loadWalletBalance() {
			this.req({
				url: "user/index",
				data: {
					userid: this.userId
				},
				success: (res) => {
					if (res.code == 1) {
						// 设置用户余额
						if (res.data && res.data.userdata) {
							this.balance = res.data.userdata.money || 0;
						    this.truename = res.data.userdata.true_name||0;
						}
					} else {
						uni.showToast({
							title: res.msg || this.$t('获取余额失败'),
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
		
		// 加载交易记录
		getTransactionList() {
			if (this.loading || !this.hasMore) return;
			
			this.loading = true;
			
			this.req({
				url: "user/transactions",
				data: {
					userid: this.userId,
					page: this.currentPage,
					limit: this.pageSize
				},
				success: (res) => {
					if (res.code == 1) {
						const newRecords = res.data.transactions || [];
						
						this.records = [...this.records, ...newRecords];
						this.totalRecords = res.data.total || 0;
						
						// 判断是否还有更多数据
						this.hasMore = this.records.length < this.totalRecords;
						
						// 更新页码
						if (newRecords.length > 0) {
							this.currentPage++;
						}
					} else {
						uni.showToast({
							title: res.msg || this.$t('获取交易记录失败'),
							icon: 'none'
						});
					}
					
					this.loading = false;
				},
				fail: (err) => {
					uni.showToast({
						title: this.$t('网络请求失败，请重试'),
						icon: 'none'
					});
					this.loading = false;
				}
			});
		},
		
		// 加载更多
		loadMore() {
			if (this.hasMore && !this.loading) {
				this.getTransactionList();
			}
		},
		
		// 格式化余额显示
		formatBalance(balance) {
			return parseFloat(balance).toFixed(2);
		},
		
		// 导航到指定页面
		navigateTo(url) {
			this.$gopage(url);
		},
		
		// 返回上一页
		navigateBack() {
			uni.navigateBack();
		},
		
		// 选择图片并上传
		chooseAndUploadImage() {
			uni.chooseImage({
				count: 1, // 最多可以选择的图片张数
				sizeType: ['compressed'], // 可以指定是原图还是压缩图，默认二者都有
				sourceType: ['album', 'camera'], // 从相册选择或使用相机拍照
				success: (res) => {
					const tempFilePaths = res.tempFilePaths;
					this.uploadImage(tempFilePaths[0]);
				},
				fail: (err) => {
					uni.showToast({
						title: this.$t('选择图片失败'),
						icon: 'none'
					});
				}
			});
		},
		
		// 上传图片到服务器
		uploadImage(filePath) {
			this.uploading = true;
			
			const uploadTask = uni.uploadFile({
				url: this.baseUrl + 'recharge/uploadimg', // 替换为您的上传地址
				filePath: filePath,
				name: 'file',
				header: {
					'token': uni.getStorageSync('token')
				},
				formData: {
					'userid': this.userId
				},
				success: (res) => {
					let data;
					try {
						data = JSON.parse(res.data);
					} catch (e) {
						data = res.data;
					}
					
					if (data.code === 1) {
						// 上传成功
						uni.showToast({
							title: this.$t('上传成功'),
							icon: 'success'
						});
						
						// 存储上传后的图片URL
						this.uploadedImage = data.data.url || '';
						
						// 触发uploaded事件
						this.$emit('uploaded', this.uploadedImage);
					} else {
						uni.showToast({
							title: data.msg || this.$t('上传失败'),
							icon: 'none'
						});
					}
				},
				fail: (err) => {
					uni.showToast({
						title: this.$t('上传失败，请重试'),
						icon: 'none'
					});
				},
				complete: () => {
					this.uploading = false;
				}
			});
			
			// 监听上传进度
			uploadTask.onProgressUpdate((res) => {
				this.uploadProgress = res.progress;
			});
		},
		
		// 格式化日期时间
		formatDateTime(dateTimeStr) {
			if (!dateTimeStr) return '';
			
			try {
				const date = new Date(dateTimeStr);
				
				// 检查日期是否有效
				if (isNaN(date.getTime())) {
					// 尝试解析其他格式，例如 "YYYY-MM-DD HH:MM:SS"
					const parts = dateTimeStr.split(/[- :]/);
					// 创建新的日期对象 (月份需要减1，因为JavaScript中月份是从0开始的)
					if (parts.length >= 6) {
						date = new Date(parts[0], parts[1]-1, parts[2], parts[3], parts[4], parts[5]);
					}
				}
				
				// 如果日期仍然无效，返回原始字符串
				if (isNaN(date.getTime())) {
					return dateTimeStr;
				}
				
				const day = String(date.getDate()).padStart(2, '0');
				const month = String(date.getMonth() + 1).padStart(2, '0');
				const year = date.getFullYear();
				const hours = String(date.getHours()).padStart(2, '0');
				const minutes = String(date.getMinutes()).padStart(2, '0');
				
				return `${day}/${month}/${year} ${hours}:${minutes}`;
			} catch (e) {
				console.error('日期格式化错误:', e);
				return dateTimeStr;
			}
		},
		
		// 获取状态文本键
		getStatusText(status) {
			switch (Number(status)) {
				case 0:
					return '待审核';
				case 1:
					return '已完成';
				case 2:
					return '已拒绝';
				default:
					return '未知状态';
			}
		}
	}
}
</script>

<style lang="scss" scoped>
.wallet-container {
	background-color: #1A1A1A;
	min-height: 100vh;
}

/* 导航栏样式 */
.navbar {
	height: 90rpx;
	display: flex;
	align-items: center;
	justify-content: space-between;
	background-color: #1A1A1A;
	padding: 0 30rpx;
	box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.2);
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

/* 余额卡片样式 */
.balance-card {
	background-color: #007AFF;
	margin: 30rpx;
	padding: 40rpx;
	border-radius: 20rpx;
	color: #FFFFFF;
	box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.3);
}

.balance-title {
	font-size: 28rpx;
	opacity: 0.8;
	margin-bottom: 10rpx;
}

.balance-amount {
	font-size: 60rpx;
	font-weight: bold;
	margin-bottom: 40rpx;
}

.balance-actions {
	display: flex;
	justify-content: space-around;
}

.action-btn {
	display: flex;
	flex-direction: column;
	align-items: center;
	background-color: rgba(255, 255, 255, 0.2);
	padding: 20rpx 40rpx;
	border-radius: 50rpx;
}

.action-text {
	font-size: 24rpx;
	margin-top: 6rpx;
}

.action-icon {
	width: 36rpx;
	height: 36rpx;
}

/* 交易记录样式 */
.transactions-section {
	background-color: #2C2C2C;
	margin: 30rpx;
	padding: 30rpx;
	border-radius: 20rpx;
	height: calc(100vh - 380rpx);
	display: flex;
	flex-direction: column;
	box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.2);
}

.section-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding-bottom: 20rpx;
	border-bottom: 1rpx solid #3A3A3A;
	margin-bottom: 20rpx;
}

.section-title {
	font-size: 32rpx;
	font-weight: bold;
	color: #FFFFFF;
}

.transaction-list-scroll {
	flex: 1;
	height: 100%;
}

.transaction-list {
	display: flex;
	flex-direction: column;
}

.transaction-item {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 20rpx 0;
	border-bottom: 1rpx solid #3A3A3A;
}

.transaction-item:last-child {
	border-bottom: none;
}

.transaction-info {
	display: flex;
	flex-direction: column;
}

.transaction-type {
	font-size: 28rpx;
	color: #FFFFFF;
	margin-bottom: 6rpx;
}

.transaction-date {
	font-size: 24rpx;
	color: #999999;
}

.transaction-amount {
	font-size: 32rpx;
	font-weight: bold;
}

.amount-in {
	color: #34C759;
}

.amount-out {
	color: #FF3B30;
}

.empty-state {
	display: flex;
	justify-content: center;
	padding: 60rpx 0;
}

.empty-text {
	font-size: 28rpx;
	color: #999999;
}

/* 加载更多样式 */
.loading-more, .no-more {
	display: flex;
	justify-content: center;
	padding: 30rpx 0;
}

.loading-text, .no-more-text {
	font-size: 24rpx;
	color: #999999;
}

.transaction-status {
	margin-left: 10px;
	font-size: 12px;
}

.status-completed {
	color: #34C759;
}

.status-pending {
	color: #FF9500;
}

.status-rejected {
	color: #FF3B30;
}

.transaction-id {
	font-size: 22rpx;
	color: #777777;
	margin-top: 4rpx;
}
</style>