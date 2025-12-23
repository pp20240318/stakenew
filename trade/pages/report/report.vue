<template>
	<view class="report-container">
		<view class="report-header">
			<view class="total-profit">
				<text class="label">总收益 (USDT)</text>
				<text class="amount profit">+2,345.67</text>
			</view>
			
			<view class="profit-period">
				<button 
					v-for="(period, index) in periods" 
					:key="index" 
					class="period-btn" 
					:class="{ active: currentPeriod === period.value }"
					@tap="switchPeriod(period.value)"
				>{{ period.label }}</button>
			</view>
		</view>
		
		<view class="profit-chart">
			<view class="chart-title">收益趋势</view>
			<view class="chart-placeholder">
				<text>收益图表将在此显示</text>
				<text class="chart-note">（实际开发中接入图表库）</text>
			</view>
		</view>
		
		<view class="trade-statistics">
			<view class="statistics-title">交易统计</view>
			
			<view class="stats-cards">
				<view class="stats-card">
					<text class="stats-label">交易总次数</text>
					<text class="stats-value">128</text>
				</view>
				
				<view class="stats-card">
					<text class="stats-label">成功率</text>
					<text class="stats-value">76%</text>
				</view>
				
				<view class="stats-card">
					<text class="stats-label">平均收益</text>
					<text class="stats-value profit">+18.32 USDT</text>
				</view>
				
				<view class="stats-card">
					<text class="stats-label">最大收益</text>
					<text class="stats-value profit">+320.45 USDT</text>
				</view>
			</view>
		</view>
		
		<view class="trade-records">
			<view class="records-title">
				<text>交易记录</text>
				<view class="filter-btn" @tap="showFilters">
					<text>筛选</text>
					<text class="filter-icon">▼</text>
				</view>
			</view>
			
			<view class="trade-filter" v-if="showFilterOptions">
				<view class="filter-row">
					<view class="filter-item">
						<text class="filter-label">交易对</text>
						<picker mode="selector" :range="pairOptions" @change="onPairChange">
							<view class="picker-value">{{ currentPair || '全部' }}</view>
						</picker>
					</view>
					
					<view class="filter-item">
						<text class="filter-label">类型</text>
						<picker mode="selector" :range="typeOptions" @change="onTypeChange">
							<view class="picker-value">{{ currentType || '全部' }}</view>
						</picker>
					</view>
				</view>
				
				<view class="filter-row">
					<view class="filter-item">
						<text class="filter-label">开始日期</text>
						<picker mode="date" @change="onStartDateChange">
							<view class="picker-value">{{ startDate || '选择日期' }}</view>
						</picker>
					</view>
					
					<view class="filter-item">
						<text class="filter-label">结束日期</text>
						<picker mode="date" @change="onEndDateChange">
							<view class="picker-value">{{ endDate || '选择日期' }}</view>
						</picker>
					</view>
				</view>
				
				<view class="filter-actions">
					<button class="filter-btn reset" @tap="resetFilters">重置</button>
					<button class="filter-btn apply" @tap="applyFilters">应用</button>
				</view>
			</view>
			
			<view class="record-list">
				<view class="record-item" v-for="(record, index) in tradeRecords" :key="index" @tap="viewTradeDetail(record.id)">
					<view class="record-header">
						<view class="record-left">
							<text class="record-pair">{{ record.pair }}</text>
							<text class="record-type" :class="{ 'buy-text': record.type === 'buy', 'sell-text': record.type === 'sell' }">
								{{ record.type === 'buy' ? '买入' : '卖出' }}
							</text>
						</view>
						<view class="record-right">
							<text class="record-profit" :class="{ 'profit-up': record.profit > 0, 'profit-down': record.profit < 0 }">
								{{ record.profit > 0 ? '+' : '' }}{{ record.profit }} USDT
							</text>
						</view>
					</view>
					
					<view class="record-details">
						<view class="detail-row">
							<text class="detail-label">数量</text>
							<text class="detail-value">{{ record.amount }} {{ record.pair.split('/')[0] }}</text>
						</view>
						<view class="detail-row">
							<text class="detail-label">价格</text>
							<text class="detail-value">{{ record.price }} {{ record.pair.split('/')[1] }}</text>
						</view>
						<view class="detail-row">
							<text class="detail-label">时间</text>
							<text class="detail-value">{{ record.time }}</text>
						</view>
					</view>
				</view>
			</view>
			
			<view class="pagination">
				<button class="page-btn prev" :disabled="currentPage === 1" @tap="prevPage">上一页</button>
				<text class="page-info">{{ currentPage }}/{{ totalPages }}</text>
				<button class="page-btn next" :disabled="currentPage === totalPages" @tap="nextPage">下一页</button>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				periods: [
					{ label: '今日', value: 'today' },
					{ label: '本周', value: 'week' },
					{ label: '本月', value: 'month' },
					{ label: '全部', value: 'all' }
				],
				currentPeriod: 'month',
				
				showFilterOptions: false,
				pairOptions: ['全部', 'BTC/USDT', 'ETH/USDT', 'BNB/USDT', 'SOL/USDT'],
				typeOptions: ['全部', '买入', '卖出'],
				currentPair: '',
				currentType: '',
				startDate: '',
				endDate: '',
				
				currentPage: 1,
				totalPages: 5,
				
				tradeRecords: [
					{
						id: 'T123456789',
						pair: 'BTC/USDT',
						type: 'buy',
						amount: '0.0235',
						price: '42,580.25',
						time: '2023-06-15 14:32',
						profit: 12.82
					},
					{
						id: 'T123456788',
						pair: 'ETH/USDT',
						type: 'sell',
						amount: '0.6350',
						price: '1,578.35',
						time: '2023-06-14 09:15',
						profit: 7.04
					},
					{
						id: 'T123456787',
						pair: 'BNB/USDT',
						type: 'buy',
						amount: '4.0750',
						price: '245.78',
						time: '2023-06-12 18:45',
						profit: -9.05
					},
					{
						id: 'T123456786',
						pair: 'SOL/USDT',
						type: 'buy',
						amount: '12.1500',
						price: '82.45',
						time: '2023-06-10 11:22',
						profit: 23.47
					},
					{
						id: 'T123456785',
						pair: 'XRP/USDT',
						type: 'sell',
						amount: '1750.2200',
						price: '0.5725',
						time: '2023-06-08 15:33',
						profit: -3.25
					}
				]
			}
		},
		methods: {
			switchPeriod(period) {
				this.currentPeriod = period;
				// 在实际应用中，这里应该根据所选时间段获取数据
			},
			
			showFilters() {
				this.showFilterOptions = !this.showFilterOptions;
			},
			
			onPairChange(e) {
				const index = e.detail.value;
				this.currentPair = index === 0 ? '' : this.pairOptions[index];
			},
			
			onTypeChange(e) {
				const index = e.detail.value;
				this.currentType = index === 0 ? '' : this.typeOptions[index];
			},
			
			onStartDateChange(e) {
				this.startDate = e.detail.value;
			},
			
			onEndDateChange(e) {
				this.endDate = e.detail.value;
			},
			
			resetFilters() {
				this.currentPair = '';
				this.currentType = '';
				this.startDate = '';
				this.endDate = '';
			},
			
			applyFilters() {
				this.showFilterOptions = false;
				// 在实际应用中，这里应该根据筛选条件获取数据
			},
			
			prevPage() {
				if (this.currentPage > 1) {
					this.currentPage--;
					// 在实际应用中，这里应该获取上一页的数据
				}
			},
			
			nextPage() {
				if (this.currentPage < this.totalPages) {
					this.currentPage++;
					// 在实际应用中，这里应该获取下一页的数据
				}
			},
			
			viewTradeDetail(id) {
				uni.navigateTo({
					url: `/pages/trade/detail?id=${id}`
				});
			}
		}
	}
</script>

<style>
	.report-container {
		padding: 30rpx;
		background-color: #F5F5F5;
		min-height: 100vh;
	}
	
	.report-header {
		background: linear-gradient(135deg, #40DFBF, #00BFFF);
		padding: 40rpx 30rpx;
		border-radius: 12rpx;
		color: #FFFFFF;
		margin-bottom: 30rpx;
	}
	
	.total-profit {
		margin-bottom: 30rpx;
	}
	
	.label {
		font-size: 28rpx;
		opacity: 0.8;
		display: block;
		margin-bottom: 10rpx;
	}
	
	.amount {
		font-size: 48rpx;
		font-weight: bold;
		display: block;
	}
	
	.profit {
		color: #4CD964;
	}
	
	.loss {
		color: #FF3B30;
	}
	
	.profit-period {
		display: flex;
		justify-content: space-between;
	}
	
	.period-btn {
		flex: 1;
		margin: 0 10rpx;
		height: 60rpx;
		line-height: 60rpx;
		font-size: 24rpx;
		background-color: rgba(255, 255, 255, 0.2);
		color: #FFFFFF;
		border-radius: 30rpx;
	}
	
	.period-btn.active {
		background-color: #FFFFFF;
		color: #40DFBF;
	}
	
	.profit-chart, .trade-statistics, .trade-records {
		background-color: #FFFFFF;
		border-radius: 12rpx;
		padding: 30rpx;
		margin-bottom: 30rpx;
	}
	
	.chart-title, .statistics-title, .records-title {
		font-size: 32rpx;
		font-weight: bold;
		color: #333;
		margin-bottom: 20rpx;
	}
	
	.records-title {
		display: flex;
		justify-content: space-between;
		align-items: center;
	}
	
	.filter-btn {
		display: flex;
		align-items: center;
		font-size: 28rpx;
		color: #40DFBF;
	}
	
	.filter-icon {
		margin-left: 6rpx;
		font-size: 24rpx;
	}
	
	.chart-placeholder {
		height: 300rpx;
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
		border: 1rpx dashed #CCC;
		border-radius: 8rpx;
		color: #999;
	}
	
	.chart-note {
		font-size: 24rpx;
		margin-top: 10rpx;
	}
	
	.stats-cards {
		display: flex;
		flex-wrap: wrap;
		justify-content: space-between;
	}
	
	.stats-card {
		width: 48%;
		padding: 20rpx;
		background-color: #F8F8F8;
		border-radius: 8rpx;
		margin-bottom: 20rpx;
	}
	
	.stats-label {
		font-size: 24rpx;
		color: #666;
		display: block;
		margin-bottom: 10rpx;
	}
	
	.stats-value {
		font-size: 32rpx;
		font-weight: bold;
		color: #333;
		display: block;
	}
	
	.trade-filter {
		background-color: #F8F8F8;
		padding: 20rpx;
		border-radius: 8rpx;
		margin-bottom: 20rpx;
	}
	
	.filter-row {
		display: flex;
		justify-content: space-between;
		margin-bottom: 20rpx;
	}
	
	.filter-item {
		width: 48%;
	}
	
	.filter-label {
		font-size: 24rpx;
		color: #666;
		display: block;
		margin-bottom: 10rpx;
	}
	
	.picker-value {
		height: 70rpx;
		line-height: 70rpx;
		padding: 0 20rpx;
		background-color: #FFFFFF;
		border-radius: 6rpx;
		font-size: 28rpx;
		color: #333;
	}
	
	.filter-actions {
		display: flex;
		justify-content: flex-end;
	}
	
	.filter-btn {
		width: 160rpx;
		height: 70rpx;
		line-height: 70rpx;
		font-size: 28rpx;
		margin-left: 20rpx;
		border-radius: 6rpx;
	}
	
	.reset {
		background-color: #F2F2F2;
		color: #666;
	}
	
	.apply {
		background-color: #40DFBF;
		color: #FFFFFF;
	}
	
	.record-list {
		margin-bottom: 30rpx;
	}
	
	.record-item {
		padding: 20rpx;
		background-color: #F8F8F8;
		border-radius: 8rpx;
		margin-bottom: 20rpx;
	}
	
	.record-header {
		display: flex;
		justify-content: space-between;
		margin-bottom: 20rpx;
	}
	
	.record-left {
		display: flex;
		align-items: center;
	}
	
	.record-pair {
		font-size: 28rpx;
		font-weight: bold;
		color: #333;
		margin-right: 15rpx;
	}
	
	.record-type {
		font-size: 24rpx;
		padding: 4rpx 12rpx;
		border-radius: 20rpx;
	}
	
	.buy-text {
		background-color: rgba(76, 217, 100, 0.1);
		color: #4CD964;
	}
	
	.sell-text {
		background-color: rgba(255, 59, 48, 0.1);
		color: #FF3B30;
	}
	
	.record-profit {
		font-size: 32rpx;
		font-weight: bold;
	}
	
	.profit-up {
		color: #4CD964;
	}
	
	.profit-down {
		color: #FF3B30;
	}
	
	.record-details {
		background-color: #FFFFFF;
		border-radius: 6rpx;
		padding: 15rpx;
	}
	
	.detail-row {
		display: flex;
		justify-content: space-between;
		margin-bottom: 10rpx;
	}
	
	.detail-row:last-child {
		margin-bottom: 0;
	}
	
	.detail-label {
		font-size: 26rpx;
		color: #666;
	}
	
	.detail-value {
		font-size: 26rpx;
		color: #333;
	}
	
	.pagination {
		display: flex;
		justify-content: center;
		align-items: center;
	}
	
	.page-btn {
		width: 160rpx;
		height: 70rpx;
		line-height: 70rpx;
		font-size: 28rpx;
		margin: 0 20rpx;
		border-radius: 6rpx;
		background-color: #40DFBF;
		color: #FFFFFF;
	}
	
	.page-btn[disabled] {
		background-color: #F2F2F2;
		color: #999;
	}
	
	.page-info {
		font-size: 28rpx;
		color: #666;
	}
</style> 