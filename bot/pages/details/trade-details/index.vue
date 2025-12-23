<template>
  <view class="trade-details-container">
    <view class="trade-details-header">
      <view class="back-btn" @click="navigateBack">
        <text class="back-icon">&#8592;</text>
        <text>{{ $t('common.back') }}</text>
      </view>
      <view class="trade-details-title">{{ $t('bot.tradeDetail') }}</view>
    </view>
    
    <view class="trade-details-card" v-if="tradeDetail">
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
    
    <view class="trade-chart-card">
      <view class="chart-title">{{ $t('bot.priceChart') }}</view>
      <view class="chart-placeholder">
        <!-- 这里应该放置价格图表，使用echarts等图表库 -->
        <view class="chart-mock">
          <text>{{ $t('bot.chartPlaceholder') }}</text>
        </view>
      </view>
    </view>
    
    <view class="trade-actions">
      <button class="btn btn-outline btn-block" @click="exportTradeDetail">
        {{ $t('bot.exportDetail') }}
      </button>
    </view>
  </view>
</template>

<script>
export default {
  data() {
    return {
      tradeId: null,
      tradeDetail: null
    }
  },
  onLoad(options) {
    if (options.id) {
      this.tradeId = options.id
      this.getTradeDetail()
    }
  },
  methods: {
    // 返回上一页
    navigateBack() {
      uni.navigateBack()
    },
    
    // 获取交易详情
    getTradeDetail() {
      // 这里模拟获取交易详情
      // 实际应用中应该从API获取
      setTimeout(() => {
        this.tradeDetail = {
          id: this.tradeId,
          symbol: 'BTC/USDT',
          side: Math.random() > 0.5 ? 'buy' : 'sell',
          price: 30000 + Math.random() * 2000,
          amount: (Math.random() * 0.1).toFixed(4),
          time: new Date().getTime(),
          profit: (Math.random() * 100 - 50).toFixed(2),
          status: 'completed'
        }
      }, 500)
    },
    
    // 格式化日期时间
    formatDateTime(timestamp) {
      const date = new Date(timestamp)
      const year = date.getFullYear()
      const month = (date.getMonth() + 1).toString().padStart(2, '0')
      const day = date.getDate().toString().padStart(2, '0')
      const hours = date.getHours().toString().padStart(2, '0')
      const minutes = date.getMinutes().toString().padStart(2, '0')
      const seconds = date.getSeconds().toString().padStart(2, '0')
      
      return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`
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
.trade-details-container {
  min-height: 100vh;
  padding: 20px;
  background-color: var(--bg-color);
}

.trade-details-header {
  display: flex;
  align-items: center;
  margin-bottom: 30px;
  position: relative;
}

.back-btn {
  display: flex;
  align-items: center;
  font-size: 16px;
  color: var(--text-color);
}

.back-icon {
  margin-right: 5px;
  font-size: 18px;
}

.trade-details-title {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  font-size: 18px;
  font-weight: bold;
  color: var(--text-color);
}

.trade-details-card {
  background-color: var(--card-bg-color);
  border-radius: 10px;
  padding: 20px;
  margin-bottom: 15px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
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
  color: var(--text-color);
}

.trade-profit {
  font-size: 20px;
  font-weight: bold;
}

.trade-info-list {
  margin-top: 15px;
}

.trade-info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 0;
  border-bottom: 1px solid var(--border-color);
}

.trade-info-item:last-child {
  border-bottom: none;
}

.info-label {
  font-size: 14px;
  color: var(--text-color-secondary);
}

.info-value {
  font-size: 14px;
  font-weight: bold;
  color: var(--text-color);
}

.status-tag {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  color: white;
}

.status-completed {
  background-color: var(--info-color);
}

.status-profit {
  background-color: var(--success-color);
}

.status-loss {
  background-color: var(--danger-color);
}

.trade-chart-card {
  background-color: var(--card-bg-color);
  border-radius: 10px;
  padding: 20px;
  margin-bottom: 15px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.chart-title {
  font-size: 16px;
  font-weight: bold;
  color: var(--text-color);
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
  background-color: rgba(0, 0, 0, 0.03);
  display: flex;
  justify-content: center;
  align-items: center;
  border-radius: 8px;
}

.trade-actions {
  margin-top: 20px;
}
</style> 