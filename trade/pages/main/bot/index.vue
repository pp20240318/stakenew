<template>
  <view class="bot-container">
    <view class="bot-header">
      <view class="bot-title">{{ $t('bot.title') }}</view>
      <view 
        class="bot-status"
        :class="{ 
          'status-running': botStatus === 'running',
          'status-analyzing': botStatus === 'analyzing',
          'status-stopped': botStatus === 'stopped'
        }"
      >
        {{ $t(`bot.${botStatus}`) }}
      </view>
    </view>
    
    <!-- 机器人控制卡片 -->
    <view class="bot-control-card">
      <view class="bot-info">
        <view class="bot-info-item">
          <text class="info-label">{{ $t('bot.todayProfit') }}</text>
          <text 
            class="info-value" 
            :class="{ 
              'text-success': botInfo.todayProfit > 0, 
              'text-danger': botInfo.todayProfit < 0 
            }"
          >
            {{ botInfo.todayProfit > 0 ? '+' : '' }}{{ botInfo.todayProfit.toFixed(2) }} USDT
          </text>
        </view>
        
        <view class="bot-info-item">
          <text class="info-label">{{ $t('bot.totalProfit') }}</text>
          <text 
            class="info-value" 
            :class="{ 
              'text-success': botInfo.totalProfit > 0, 
              'text-danger': botInfo.totalProfit < 0 
            }"
          >
            {{ botInfo.totalProfit > 0 ? '+' : '' }}{{ botInfo.totalProfit.toFixed(2) }} USDT
          </text>
        </view>
        
        <view class="bot-info-item">
          <text class="info-label">{{ $t('bot.tradeCount') }}</text>
          <text class="info-value">{{ botInfo.tradeCount }}</text>
        </view>
      </view>
      
      <!-- 交易额度信息 -->
      <view class="trading-limit-info">
        <view class="limit-header">
          <text class="limit-title">{{ $t('bot.tradingLimit') }}</text>
          <text class="limit-value">{{ tradingLimit }} USDT</text>
        </view>
        
        <view class="limit-tip">
          {{ $t('bot.upgradeTip') }}
        </view>
      </view>
      
      <!-- 分析中提示 -->
      <view class="analyzing-tip" v-if="botStatus === 'analyzing'">
        <text class="analyzing-icon">⚠️</text>
        <text class="analyzing-text">{{ $t('bot.botAnalyzing') }}</text>
      </view>
      
      <!-- 启动/停止按钮 -->
      <button 
        class="btn btn-block" 
        :class="{ 
          'btn-primary': botStatus === 'stopped', 
          'btn-danger': botStatus === 'running' 
        }"
        @click="handleBotAction"
        :disabled="botStatus === 'analyzing' || (!canStartToday && botStatus === 'stopped')"
      >
        {{ botStatus === 'running' ? $t('bot.stop') : $t('bot.start') }}
      </button>
      
      <!-- 每日限制提示 -->
      <view class="daily-limit-tip" v-if="!canStartToday && botStatus === 'stopped'">
        {{ $t('bot.limitExceeded') }}
      </view>
    </view>
    
    <!-- 交易记录 -->
    <view class="trade-history-card">
      <view class="history-header">
        <text class="history-title">{{ $t('bot.tradeHistory') }}</text>
        
        <!-- 日期筛选 -->
        <view class="date-filter">
          <text 
            class="filter-item" 
            :class="{ active: dateFilter === 'today' }"
            @click="changeDateFilter('today')"
          >
            {{ $t('bot.today') }}
          </text>
          <text 
            class="filter-item" 
            :class="{ active: dateFilter === 'week' }"
            @click="changeDateFilter('week')"
          >
            {{ $t('bot.week') }}
          </text>
          <text 
            class="filter-item" 
            :class="{ active: dateFilter === 'month' }"
            @click="changeDateFilter('month')"
          >
            {{ $t('bot.month') }}
          </text>
        </view>
      </view>
      
      <!-- 交易列表 -->
      <view class="trade-list">
        <view 
          class="trade-item" 
          v-for="(item, index) in tradingHistory" 
          :key="index"
          @click="viewTradeDetail(item)"
        >
          <view class="trade-top">
            <text class="trade-symbol">{{ item.symbol }}</text>
            <text 
              class="trade-profit" 
              :class="{ 
                'text-success': item.profit > 0, 
                'text-danger': item.profit < 0 
              }"
            >
              {{ item.profit > 0 ? '+' : '' }}{{ item.profit.toFixed(2) }} USDT
            </text>
          </view>
          
          <view class="trade-bottom">
            <text class="trade-info">
              {{ item.side === 'buy' ? $t('bot.buyPrice') : $t('bot.sellPrice') }}: {{ item.price }} USDT
              • {{ $t('bot.amount') }}: {{ item.amount }}
            </text>
            <text class="trade-time">{{ formatTime(item.time) }}</text>
          </view>
        </view>
        
        <!-- 无数据提示 -->
        <view class="no-data" v-if="tradingHistory.length === 0">
          {{ $t('common.noData') }}
        </view>
      </view>
    </view>
  </view>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'

export default {
  data() {
    return {
      dateFilter: 'today' // today, week, month
    }
  },
  computed: {
    ...mapGetters('exchange', ['isConnected']),
    ...mapGetters('bot', [
      'botStatus',
      'botInfo',
      'tradingHistory',
      'tradingLimit',
      'canStartToday'
    ])
  },
  onLoad() {
    // 获取机器人状态
    this.getBotStatus()
    
    // 获取交易历史
    this.getTradingHistory({ dateRange: this.dateFilter })
  },
  methods: {
    ...mapActions('bot', [
      'startBot',
      'stopBot',
      'getBotStatus',
      'getTradingHistory'
    ]),
    
    // 处理机器人启动/停止
    async handleBotAction() {
      if (!this.isConnected) {
        uni.showToast({
          title: this.$t('exchange.connectTip'),
          icon: 'none'
        })
        
        setTimeout(() => {
          uni.switchTab({
            url: '/pages/main/exchange'
          })
        }, 1500)
        
        return
      }
      
      try {
        uni.showLoading({
          title: this.$t('common.loading')
        })
        
        if (this.botStatus === 'running') {
          // 停止机器人
          await this.stopBot()
          
          uni.hideLoading()
          
          uni.showToast({
            title: this.$t('bot.stopSuccess'),
            icon: 'success'
          })
        } else {
          // 启动机器人
          await this.startBot()
          
          uni.hideLoading()
          
          uni.showToast({
            title: this.$t('bot.startSuccess'),
            icon: 'success'
          })
          
          // 刷新交易历史
          this.getTradingHistory({ dateRange: this.dateFilter })
        }
      } catch (error) {
        uni.hideLoading()
        uni.showToast({
          title: error.message || this.$t('common.failed'),
          icon: 'none'
        })
      }
    },
    
    // 更改日期筛选
    changeDateFilter(filter) {
      this.dateFilter = filter
      this.getTradingHistory({ dateRange: filter })
    },
    
    // 查看交易详情
    viewTradeDetail(trade) {
      uni.navigateTo({
        url: `/pages/details/trade-details?id=${trade.id}`
      })
    },
    
    // 格式化时间
    formatTime(timestamp) {
      const date = new Date(timestamp)
      const hours = date.getHours().toString().padStart(2, '0')
      const minutes = date.getMinutes().toString().padStart(2, '0')
      
      return `${hours}:${minutes}`
    }
  }
}
</script>

<style lang="scss">
.bot-container {
  min-height: 100vh;
  padding: 20px;
  background-color: var(--bg-color);
}

.bot-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.bot-title {
  font-size: 20px;
  font-weight: bold;
  color: var(--text-color);
}

.bot-status {
  font-size: 14px;
  padding: 4px 8px;
  border-radius: 12px;
  color: white;
}

.status-running {
  background-color: var(--primary-color);
}

.status-analyzing {
  background-color: var(--warning-color);
}

.status-stopped {
  background-color: var(--text-color-secondary);
}

.bot-control-card {
  background-color: var(--card-bg-color);
  border-radius: 10px;
  padding: 20px;
  margin-bottom: 15px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.bot-info {
  display: flex;
  justify-content: space-between;
  margin-bottom: 20px;
}

.bot-info-item {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.info-label {
  font-size: 12px;
  color: var(--text-color-secondary);
  margin-bottom: 5px;
}

.info-value {
  font-size: 16px;
  font-weight: bold;
  color: var(--text-color);
}

.trading-limit-info {
  background-color: rgba(0, 0, 0, 0.03);
  border-radius: 8px;
  padding: 15px;
  margin-bottom: 20px;
}

.limit-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.limit-title {
  font-size: 14px;
  color: var(--text-color);
}

.limit-value {
  font-size: 14px;
  font-weight: bold;
  color: var(--primary-color);
}

.limit-tip {
  font-size: 12px;
  color: var(--text-color-secondary);
}

.analyzing-tip {
  display: flex;
  align-items: center;
  background-color: #FFF8E1;
  border-radius: 8px;
  padding: 10px;
  margin-bottom: 20px;
}

.analyzing-icon {
  font-size: 16px;
  margin-right: 8px;
}

.analyzing-text {
  font-size: 12px;
  color: var(--warning-color);
  flex: 1;
}

.daily-limit-tip {
  font-size: 12px;
  color: var(--danger-color);
  text-align: center;
  margin-top: 10px;
}

.trade-history-card {
  background-color: var(--card-bg-color);
  border-radius: 10px;
  padding: 20px;
  margin-bottom: 15px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.history-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.history-title {
  font-size: 16px;
  font-weight: bold;
  color: var(--text-color);
}

.date-filter {
  display: flex;
  background-color: rgba(0, 0, 0, 0.03);
  border-radius: 20px;
  overflow: hidden;
}

.filter-item {
  font-size: 12px;
  padding: 4px 10px;
  color: var(--text-color-secondary);
}

.filter-item.active {
  background-color: var(--primary-color);
  color: white;
}

.trade-list {
  max-height: 400px;
  overflow-y: auto;
}

.trade-item {
  padding: 15px 0;
  border-bottom: 1px solid var(--border-color);
}

.trade-item:last-child {
  border-bottom: none;
}

.trade-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 5px;
}

.trade-symbol {
  font-size: 16px;
  font-weight: bold;
  color: var(--text-color);
}

.trade-profit {
  font-size: 16px;
  font-weight: bold;
}

.trade-bottom {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.trade-info {
  font-size: 12px;
  color: var(--text-color-secondary);
}

.trade-time {
  font-size: 12px;
  color: var(--text-color-secondary);
}

.no-data {
  text-align: center;
  padding: 30px 0;
  color: var(--text-color-secondary);
  font-size: 14px;
}
</style> 