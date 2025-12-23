<template>
	<view class="wallet-container">
		<!-- 顶部导航栏 -->
		<view class="navbar">
			<view class="back-btn" @tap="navigateBack">
				<text class="ri-arrow-left-line"></text>
			</view>
			<text class="page-title">{{ $t('钱包') }}</text>
			<view class="navbar-right"></view>
		</view>
		
		<!-- 钱包余额卡片 -->
		<view class="balance-card">
			<view class="balance-title">{{ $t('总资产') }} (USDT)</view>
			<view class="balance-amount">{{ formatBalance(balance) }}</view>
			<view class="balance-actions">
				<view class="action-btn deposit" @tap="navigateTo('/pages/recharge/depositDetail')">
					<text class="ri-arrow-down-line"></text>
					<text class="action-text">{{ $t('充值') }}</text>
				</view>
				<view class="action-btn withdraw" @tap="navigateTo('/pages/recharge/withdrawal')">
					<text class="ri-arrow-up-line"></text>
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
				<view class="transaction-list" v-if="transactions.length > 0">
					<view class="transaction-item" v-for="(item, index) in transactions" :key="index">
						<view class="transaction-info">
							<text class="transaction-type">{{ $t(item.business_type) }}</text>
							<text class="transaction-date">{{ formatTimestamp(item.create_time) }}</text>
						</view>
						<view class="transaction-amount" :class="{ 'amount-positive': item.money > 0, 'amount-negative': item.money < 0 }">
							{{ item.money > 0 ? '+' : '' }}{{ item.money }} USDT
						</view>
					</view>
					
					<!-- 加载状态 -->
					<view class="loading-more" v-if="loading">
						<text class="loading-text">{{ $t('加载中...') }}</text>
					</view>
					
					<!-- 无更多数据 -->
					<view class="no-more" v-if="!hasMore && transactions.length > 0">
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
			transactions: [],
			userId: 0,
			currentPage: 1,
			pageSize: 10,
			hasMore: true,
			loading: false,
			totalRecords: 0
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
		this.loadWalletData();
	},
	methods: {
		// 重置数据
		resetData() {
			this.transactions = [];
			this.currentPage = 1;
			this.hasMore = true;
			this.loading = false;
		},
		
		// 加载钱包数据
		loadWalletData() {
			// 获取钱包余额
			this.loadWalletBalance();
			
			// 获取交易记录
			this.loadTransactions();
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
						}
					} else {
						uni.showToast({
							title: this.$t(res.msg) || this.$t('获取余额失败'),
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
		loadTransactions() {
			if (this.loading || !this.hasMore) return;
			
			this.loading = true;
			
			this.req({
				url: "user/transactions",
				data: {
					userid: this.userId,
					page: this.currentPage,
					limit: this.pageSize,
					types: 'recharge,withdraw' // 只获取充值和提现记录
				},
				success: (res) => {
					if (res.code == 1) {
						const newRecords = res.data.transactions || [];
						
						// 过滤充值和提现记录
						const filteredRecords = newRecords.filter(record => 
							record.business_type && 
							(record.business_type.toLowerCase().includes('recharge') || 
							record.business_type.toLowerCase().includes('withdraw'))
						);
						
						this.transactions = [...this.transactions, ...filteredRecords];
						this.totalRecords = res.data.total || 0;
						
						// 判断是否还有更多数据
						this.hasMore = this.transactions.length < this.totalRecords;
						
						// 更新页码
						if (filteredRecords.length > 0) {
							this.currentPage++;
						}
					} else {
						uni.showToast({
							title: this.$t(res.msg) || this.$t('获取交易记录失败'),
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
				this.loadTransactions();
			}
		},
		
		// 格式化余额显示
		formatBalance(balance) {
			return parseFloat(balance).toFixed(2);
		},
		
		// 格式化时间戳显示为日期
		formatTimestamp(timestamp) {
			if (!timestamp) return '';
			
			const date = new Date(timestamp * 1000);
			return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
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
							title: '上传成功',
							icon: 'success'
						});
						
						// 存储上传后的图片URL
						this.uploadedImage = data.data.url || '';
						
						// 触发uploaded事件
						this.$emit('uploaded', this.uploadedImage);
					} else {
						uni.showToast({
							title: data.msg || '上传失败',
							icon: 'none'
						});
					}
				},
				fail: (err) => {
					uni.showToast({
						title: '上传失败，请重试',
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
		}
	}
}
</script>

<style>
.wallet-container {
	background-color: #F5F5F5;
	min-height: 100vh;
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

/* 余额卡片样式 */
.balance-card {
	background-color: #007AFF;
	margin: 30rpx;
	padding: 40rpx;
	border-radius: 20rpx;
	color: #FFFFFF;
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

/* 交易记录样式 */
.transactions-section {
	background-color: #FFFFFF;
	margin: 30rpx;
	padding: 30rpx;
	border-radius: 20rpx;
	height: calc(100vh - 380rpx);
	display: flex;
	flex-direction: column;
}

.section-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding-bottom: 20rpx;
	border-bottom: 1rpx solid #F0F0F0;
	margin-bottom: 20rpx;
}

.section-title {
	font-size: 32rpx;
	font-weight: bold;
	color: #333333;
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
	border-bottom: 1rpx solid #F0F0F0;
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
	color: #333333;
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

.amount-positive {
	color: #34C759;
}

.amount-negative {
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
</style>