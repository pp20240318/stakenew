<template>
  <view class="trade-details-container">
        <view class="navbar">
        <view  @tap="navigateBack">
          <image src="/static/images/icons/back-arrow.svg" class="back-icon"></image>
        </view>
        <text class="page-title">{{ $t('交易记录') }}</text>
        <view class="navbar-right"></view>
		</view>
		
    
    <!-- 交易列表 -->
    <view v-if="tradeType === '1'" class="trade-list">
      <!-- 日期筛选
      <view class="date-filter">
        <view class="date-input" @tap="showStartDatePicker">
          <text class="date-label">{{ $t('开始日期') }}:</text>
          <text class="date-value">{{ startDate || $t('选择日期') }}</text>
        </view>
        <view class="date-separator">-</view>
        <view class="date-input" @tap="showEndDatePicker">
          <text class="date-label">{{ $t('结束日期') }}:</text>
          <text class="date-value">{{ endDate || $t('选择日期') }}</text>
        </view>
        <view class="filter-btn" @tap="applyDateFilter">
          <text>{{ $t('筛选') }}</text>
        </view>
      </view>
       -->
      <!-- 加载状态 -->
      <view v-if="isLoading && !isLoadingMore" class="loading-container">
        <view class="loading-spinner"></view>
        <text class="loading-text">{{ $t('加载中...') }}</text>
      </view>
      
      <view v-else>
        <view v-for="(trade, index) in tradeList" :key="index" class="trade-item"  >
          <view class="trade-item-header">
            <text class="trade-symbol">{{ trade.symbol }}</text>
            <text class="trade-time">{{ formatDateTime(trade.create_time || trade.time) }}</text>
          </view>
          <view class="trade-item-body">
            <view class="trade-info">
              <text class="trade-type" :class="{ 'text-success': trade.side === 'buy', 'text-danger': trade.side === 'sell' }">
                {{ trade.side }}
              </text>
              <text class="trade-amount" style="display:none">{{ trade.amount }}</text>
              <text class="trade-price"  style="display:none">{{ trade.price }} USDT</text>
            </view>
            <view class="trade-profit" :class="{ 'text-success': trade.profit > 0, 'text-danger': trade.profit < 0 }">
              {{ Number(trade.money).toFixed(2) }} USDT
            </view>
          </view>
        </view>
        
        <!-- 无数据状态 -->
        <view v-if="tradeList.length === 0 && !isLoading" class="empty-state">
          <text class="empty-text">{{ $t('暂无交易记录') }}</text>
        </view>
        
        <!-- 分页加载更多 -->
        <view v-if="tradeList.length > 0" class="pagination">
          <view v-if="isLoadingMore" class="loading-more">
            <view class="loading-spinner small"></view>
            <text class="loading-text">{{ $t('加载更多...') }}</text>
          </view>
          <view v-else-if="hasMore" class="load-more-btn" @click="loadMore">
            <text>{{ $t('加载更多') }}</text>
          </view>
          <view v-else class="no-more">
            <text class="no-more-text">{{ $t('没有更多数据') }}</text>
          </view>
        </view>
      </view>
    </view>
    
    <!-- 单个交易详情 -->
    <view v-else-if="tradeDetail" class="trade-details-card">
      <view class="trade-symbol-section">
        <view class="trade-symbol">{{ tradeDetail.symbol }}</view>
        <view 
          class="trade-profit" 
          :class="{ 
            'text-success': tradeDetail.profit > 0, 
            'text-danger': tradeDetail.profit < 0 
          }"
        >
          {{ tradeDetail.profit > 0 ? '+' : '' }}{{ tradeDetail.profit.toFixed(2) }} USDT
        </view>
      </view>
      
      <view class="divider"></view>
      
      <view class="trade-info-list">
        <view class="trade-info-item">
          <text class="info-label">{{ $t('bot.tradeType') }}</text>
          <text 
            class="info-value" 
            :class="{ 
              'text-success': tradeDetail.side === 'buy', 
              'text-danger': tradeDetail.side === 'sell' 
            }"
          >
            {{ tradeDetail.side === 'buy' ? $t('bot.buy') : $t('bot.sell') }}
          </text>
        </view>
        
        <view class="trade-info-item">
          <text class="info-label">{{ $t('bot.price') }}</text>
          <text class="info-value">{{ tradeDetail.price }} USDT</text>
        </view>
        
        <view class="trade-info-item">
          <text class="info-label">{{ $t('bot.amount') }}</text>
          <text class="info-value">{{ tradeDetail.amount }}</text>
        </view>
        
        <view class="trade-info-item">
          <text class="info-label">{{ $t('bot.totalValue') }}</text>
          <text class="info-value">{{ (tradeDetail.price * tradeDetail.amount).toFixed(2) }} USDT</text>
        </view>
        
        <view class="trade-info-item">
          <text class="info-label">{{ $t('bot.time') }}</text>
          <text class="info-value">{{ formatDateTime(tradeDetail.time) }}</text>
        </view>
        
        <view class="trade-info-item">
          <text class="info-label">{{ $t('bot.status') }}</text>
          <text 
            class="info-value status-tag" 
            :class="{ 
              'status-completed': tradeDetail.status === 'completed',
              'status-profit': tradeDetail.profit > 0,
              'status-loss': tradeDetail.profit < 0
            }"
          >
            {{ tradeDetail.profit > 0 ? $t('bot.profit') : $t('bot.loss') }}
          </text>
        </view>
      </view>
    </view>
      
  </view>
</template>

<script>
export default {
  data() {
    return {
      tradeId: null,
      tradeDetail: null,
      tradeType: null,
      tradeList: [],
      pageTitle: '',
      isLoading: false,
      isLoadingMore: false,
      hasMore: true,
      page: 1,
      pageSize: 10,
      totalPages: 1,
      startDate: '',
      endDate: '',
      showDatePicker: false,
      currentPickerType: '' // 'start' or 'end'
    }
  },
  onLoad(options) {
    if (options.type) {
      this.tradeType = options.type
      this.pageTitle = options.type === '1' ? this.$t('交易记录') : this.$t('bot.tradeDetail')
      if (options.type === '1') {
        this.getTradeList()
      }
    }
    if (options.id) {
      this.tradeId = options.id
      this.getTradeDetail()
    }
  },
  // 页面上拉触底事件的处理函数
  onReachBottom() {
    if (this.tradeType === '1' && this.hasMore && !this.isLoading && !this.isLoadingMore) {
      this.loadMore()
    }
  },
  // 下拉刷新
  onPullDownRefresh() {
    if (this.tradeType === '1') {
      this.refreshList()
    }
    uni.stopPullDownRefresh()
  },
  methods: {
    // 返回上一页
    navigateBack() {
      uni.navigateBack()
    },
    
    // 刷新列表
    refreshList() {
      this.page = 1
      this.tradeList = []
      this.hasMore = true
      this.getTradeList()
    },
    
    // 加载更多
    loadMore() {
      if (this.hasMore && !this.isLoading) {
        this.page++
        this.getTradeList(true)
      }
    },
    
    // 获取交易列表
    getTradeList(isLoadMore = false) {
      if (isLoadMore) {
        this.isLoadingMore = true
      } else {
        this.isLoading = true
      }
      
      const params = {
        type: 1, // 手动交易 
        page: this.page,
        limit: this.pageSize
      }
      
      // 添加日期筛选参数
      if (this.startDate) {
        params.start_time = this.dateToTimestamp(this.startDate + ' 00:00:00')
      }
      
      if (this.endDate) {
        params.end_time = this.dateToTimestamp(this.endDate + ' 23:59:59')
      }
      
      this.req({
        url: "trade/records",
        data: params,
        success: (res) => {
          if (res.code === 1) {
            const data = res.data || {}
            const newTrades = data.list || []
            
            if (isLoadMore) {
              this.tradeList = [...this.tradeList, ...newTrades]
            } else {
              this.tradeList = newTrades
            }
            
            // 处理分页信息
            this.totalPages = Math.ceil((data.total || 0) / this.pageSize)
            this.hasMore = this.page < this.totalPages
            
            // 如果没有数据，显示提示
            if (this.tradeList.length === 0 && !isLoadMore) {
              uni.showToast({
                title: this.$t('暂无交易记录'),
                icon: 'none'
              })
            }
          } else {
            uni.showToast({
              title: res.msg || this.$t('获取交易记录失败'),
              icon: 'none'
            })
          }
        },
        fail: (err) => {
          console.error(this.$t('获取交易记录失败:'), err)
          uni.showToast({
            title: this.$t('获取交易记录失败'),
            icon: 'none'
          })
        },
        complete: () => {
          if (isLoadMore) {
            this.isLoadingMore = false
          } else {
            this.isLoading = false
          }
        }
      })
    },
    
    // 显示交易详情
    showTradeDetail(trade) {
      uni.navigateTo({
        url: `/pages/details/trade-details/index?id=${trade.id}`
      })
    },
    
    // 获取交易详情
    getTradeDetail() {
      this.isLoading = true
      this.req({
        url: "trade/detail",
        data: {
          id: this.tradeId,
          userid: uni.getStorageSync('userid')
        },
        success: (res) => {
          if (res.code === 1) {
            this.tradeDetail = res.data
          } else {
            uni.showToast({
              title: res.msg || this.$t('获取交易详情失败'),
              icon: 'none'
            })
          }
        },
        fail: (err) => {
          console.error(this.$t('获取交易详情失败:'), err)
          uni.showToast({
            title: this.$t('获取交易详情失败'),
            icon: 'none'
          })
        },
        complete: () => {
          this.isLoading = false
        }
      })
    },
    
    // 格式化日期时间
    formatDateTime(timestamp) {
      if (!timestamp) return '-'
      
      // 检查是否为字符串格式时间戳
      if (typeof timestamp === 'string') {
        // 尝试转换为数字
        timestamp = Number(timestamp)
        // 如果不是有效数字，返回原值
        if (isNaN(timestamp)) {
          return timestamp
        }
      }
      
      // 如果时间戳是10位数（秒级），转换为13位（毫秒级）
      if (timestamp.toString().length === 10) {
        timestamp = timestamp * 1000
      }
      
      const date = new Date(timestamp)
      // 检查是否为有效日期
      if (isNaN(date.getTime())) {
        return '-'
      }
      
      const year = date.getFullYear()
      const month = (date.getMonth() + 1).toString().padStart(2, '0')
      const day = date.getDate().toString().padStart(2, '0')
      const hours = date.getHours().toString().padStart(2, '0')
      const minutes = date.getMinutes().toString().padStart(2, '0')
      const seconds = date.getSeconds().toString().padStart(2, '0')
      
      return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`
    },
    
    // 转换日期为时间戳
    dateToTimestamp(dateStr) {
      if (!dateStr) return null
      return Math.floor(new Date(dateStr).getTime() / 1000)
    },
    
    // 显示开始日期选择器
    showStartDatePicker() {
      this.currentPickerType = 'start'
      this.showDatePicker = true
      uni.showToast({
        title: this.$t('请选择开始日期'),
        icon: 'none'
      })
      
      uni.datePicker({
        date: this.startDate,
        success: (res) => {
          this.startDate = res.date
        }
      })
    },
    
    // 显示结束日期选择器
    showEndDatePicker() {
      this.currentPickerType = 'end'
      this.showDatePicker = true
      uni.showToast({
        title: this.$t('请选择结束日期'),
        icon: 'none'
      })
      
      uni.datePicker({
        date: this.endDate,
        success: (res) => {
          this.endDate = res.date
        }
      })
    },
    
    // 应用日期筛选
    applyDateFilter() {
      this.refreshList()
    },
    
    // 导出交易详情
    exportTradeDetail() {
      uni.showToast({
        title: this.$t('bot.exportSuccess'),
        icon: 'success'
      })
    }
  }
}
</script>

<style lang="scss">

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
.trade-details-container {
  min-height: 100vh; 
  background-color: #1A1A1A;
  color: #FFFFFF;
}

.trade-details-header {
  display: flex;
  align-items: center;
  margin-bottom: 30px;
  position: relative;
  height: 50px;
}

.back-btn {
  display: flex;
  align-items: center;
  font-size: 16px;
  color: #FFFFFF;
  z-index: 10;
}

.back-icon {
  margin-right: 5px;
  font-size: 18px;
}

.back-text {
  font-size: 16px;
}

.trade-details-title {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  font-size: 18px;
  font-weight: bold;
  color: #FFFFFF;
}

.trade-details-card {
  background-color: #2C2C2C;
  border-radius: 10px;
  padding: 20px;
  margin-bottom: 15px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

.trade-symbol-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.trade-symbol {
  font-size: 20px;
  font-weight: bold;
  color: #FFFFFF;
}

.trade-profit {
  font-size: 20px;
  font-weight: bold;
}

.divider {
  height: 1px;
  background-color: rgba(255, 255, 255, 0.1);
  margin: 15px 0;
}

.trade-info-list {
  margin-top: 15px;
}

.trade-info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.trade-info-item:last-child {
  border-bottom: none;
}

.info-label {
  font-size: 14px;
  color: rgba(255, 255, 255, 0.7);
}

.info-value {
  font-size: 14px;
  font-weight: bold;
  color: #FFFFFF;
}

.status-tag {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  color: white;
}

.status-completed {
  background-color: #40DFBF;
}

.status-profit {
  background-color: #4FD964;
}

.status-loss {
  background-color: #FF3B30;
}

.trade-chart-card {
  background-color: #2C2C2C;
  border-radius: 10px;
  padding: 20px;
  margin-bottom: 15px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

.chart-title {
  font-size: 16px;
  font-weight: bold;
  color: #FFFFFF;
  margin-bottom: 15px;
}

.chart-placeholder {
  height: 200px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.chart-mock {
  width: 100%;
  height: 100%;
  background-color: rgba(255, 255, 255, 0.05);
  display: flex;
  justify-content: center;
  align-items: center;
  border-radius: 8px;
  color: rgba(255, 255, 255, 0.5);
}

.trade-actions {
  margin-top: 20px;
}

.btn {
  background-color: #2C2C2C;
  color: #FFFFFF;
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 5px;
  padding: 12px 20px;
  font-size: 16px;
  width: 100%;
  text-align: center;
}

.btn-outline {
  background-color: transparent;
}

.btn-block {
  display: block;
  width: 100%;
}

.trade-list {
  margin-top: 20px;
}

.trade-item {
  background-color: #2C2C2C;
  border-radius: 10px;
  padding: 15px;
  margin-bottom: 15px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

.trade-item-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.trade-symbol {
  font-size: 16px;
  font-weight: bold;
  color: #FFFFFF;
}

.trade-time {
  font-size: 14px;
  color: rgba(255, 255, 255, 0.7);
}

.trade-item-body {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.trade-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.trade-type {
  font-size: 14px;
  font-weight: 500;
}

.trade-amount {
  font-size: 14px;
  color: #FFFFFF;
}

.trade-price {
  font-size: 14px;
  color: rgba(255, 255, 255, 0.7);
}

.trade-profit {
  font-size: 16px;
  font-weight: bold;
}

.text-success {
  color: #4FD964;
}

.text-danger {
  color: #FF3B30;
}

.empty-state {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 40px 0;
}

.empty-text {
  font-size: 16px;
  color: rgba(255, 255, 255, 0.7);
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 0;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid rgba(255, 255, 255, 0.1);
  border-radius: 50%;
  border-top-color: #40DFBF;
  animation: spin 0.8s linear infinite;
  margin-bottom: 10px;
}

.loading-spinner.small {
  width: 20px;
  height: 20px;
  border-width: 2px;
}

.loading-text {
  font-size: 14px;
  color: rgba(255, 255, 255, 0.7);
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.pagination {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 20px 0;
}

.load-more-btn {
  background-color: #2C2C2C;
  padding: 10px 20px;
  border-radius: 20px;
  color: #40DFBF;
  font-size: 14px;
  margin-top: 10px;
}

.loading-more {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 10px 0;
}

.no-more {
  padding: 15px 0;
  display: flex;
  justify-content: center;
}

.no-more-text {
  font-size: 14px;
  color: rgba(255, 255, 255, 0.7);
}

.date-filter {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background-color: #2C2C2C;
  border-radius: 10px;
  padding: 12px 15px;
  margin: 0 15px 15px;
}

.date-input {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.date-label {
  font-size: 12px;
  color: rgba(255, 255, 255, 0.7);
  margin-bottom: 5px;
}

.date-value {
  font-size: 14px;
  color: #FFFFFF;
}

.date-separator {
  color: #FFFFFF;
  margin: 0 10px;
  align-self: flex-end;
  padding-bottom: 5px;
}

.filter-btn {
  background-color: #40DFBF;
  padding: 8px 15px;
  border-radius: 5px;
  margin-left: 10px;
}
</style> 