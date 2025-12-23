<template>
	<view class="deposit-container">
		<!-- 顶部导航栏 -->
		<view class="navbar">
			<view class="back-btn" @tap="$goBack()">
				<text class="ri-arrow-left-line"></text>
			</view>
			<text class="page-title">{{ $t('充值记录') }}</text>
			<view class="navbar-right"></view>
		</view>
		
		<!-- 记录列表 -->
		<view class="records-content">
			<!-- 表头 -->
			<view class="records-header">
				<text class="header-item">{{ $t('时间') }}</text>
				<text class="header-item">{{ $t('类型') }}</text>
				<text class="header-item">{{ $t('状态') }}</text>
				<text class="header-item">{{ $t('金额') }}</text>
			</view>
			
			<!-- 列表内容 -->
			<view class="records-list">
				<view class="record-item" v-for="(item, index) in datalist" :key="index">
					<text class="record-data">{{ $formatDate(item.create_time) }}</text>
					<text class="record-data">SYSTEM</text>
					<text class="record-data" :class="{
						'status-pending': item.status === '0',
						'status-success': item.status === '1', 
						'status-failed': item.status === '2'
					}">{{ showtype(item.status) }}</text>
					<text class="record-data">{{ item.into_account }}</text>
				</view>
				
				<!-- 空状态提示 -->
				<view v-if="datalist.length === 0" class="empty-state">
					<text class="empty-text">{{ $t('暂无充值记录') }}</text>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		data: function() {
			return {
				erc_address:'',
				erc_addressur:'',
				datalist:[],
				erc_count:'',
				currentTime:'',
				// 记录需要上传的图片数据
				needPploadedImgs : [],
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
			showtype(status){
				if(status=='0') return this.$t("审核中");
				else if(status=='1') return this.$t("成功");
				else if(status=='2') return this.$t("失败");
				else return "";
			},
	        loadData(){
	        	var t = this; 
				var userid=uni.getStorageSync('userid');
	        	t.req({
	        		url: "recharge/depositlist", 
	        		Loading: !1,
					data:{'userid':userid},
	        		success: function (i) {  
	        			 t.datalist=i.data.list; 
	        		}
	        	})
	        } 
		},
	}
</script>

<style>
.deposit-container {
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

/* 记录内容样式 */
.records-content {
	background-color: #FFFFFF;
	margin: 30rpx;
	border-radius: 20rpx;
	padding: 30rpx;
	box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.05);
}

.records-header {
	display: flex;
	padding: 20rpx 0;
	border-bottom: 1px solid #EEEEEE;
	margin-bottom: 20rpx;
}

.header-item {
	flex: 1;
	text-align: center;
	font-size: 28rpx;
	font-weight: bold;
	color: #333333;
}

.records-list {
	display: flex;
	flex-direction: column;
}

.record-item {
	display: flex;
	padding: 20rpx 0;
	border-bottom: 1px solid #F5F5F5;
}

.record-data {
	flex: 1;
	text-align: center;
	font-size: 26rpx;
	color: #666666;
}

/* 状态样式 */
.status-pending {
	color: #E6A23C;
}

.status-success {
	color: #67C23A;
}

.status-failed {
	color: #F56C6C;
}

/* 空状态提示 */
.empty-state {
	padding: 100rpx 0;
	display: flex;
	justify-content: center;
	align-items: center;
}

.empty-text {
	font-size: 28rpx;
	color: #909399;
}
</style>