<template>
  <view class="bot-container">
    <!-- 头部状态 -->
    <view class="status-card" :class="{ running: isRunning, stopped: !isRunning }">
      <view class="status-content">
        <view class="status-icon" :class="{ running: isRunning, analyzing: isAnalyzing }">
          <uni-icons :type="botStatusIcon" size="28" color="#fff"></uni-icons>
        </view>
        <view class="status-info">
          <text class="status-label">{{ t('bot.status') }}</text>
          <text class="status-value">{{ botStatusText }}</text>
        </view>
      </view>
      <view class="action-buttons">
        <button 
          class="action-btn start-btn" 
          v-if="!isRunning" 
          @click="startBot"
          :disabled="!canStartToday || !isExchangeConnected"
        >
          {{ t('bot.start') }}
        </button>
        <button 
          class="action-btn stop-btn" 
          v-else 
          @click="stopBot"
        >
          {{ t('bot.stop') }}
        </button>
      </view>
    </view>

    <!-- 机器人信息 -->
    <view class="info-card">
      <view class="info-header">
        <text class="info-title">{{ t('bot.botInfo') }}</text>
      </view>
      
      <view class="info-content">
        <view class="info-row">
          <view class="info-item">
            <text class="item-label">{{ t('bot.todayProfit') }}</text>
            <text class="item-value profit">{{ formatProfit(botInfo.todayProfit) }}</text>
          </view>
          <view class="info-item">
            <text class="item-label">{{ t('bot.totalProfit') }}</text>
            <text class="item-value profit">{{ formatProfit(botInfo.totalProfit) }}</text>
          </view>
        </view>
        
        <view class="info-row">
          <view class="info-item">
            <text class="item-label">{{ t('bot.tradeCount') }}</text>
            <text class="item-value">{{ botInfo.tradeCount || 0 }}</text>
          </view>
          <view class="info-item">
            <text class="item-label">{{ t('bot.todayRunCount') }}</text>
            <text class="item-value">{{ botInfo.todayRunCount || 0 }}/{{ tradingLimit }}</text>
          </view>
        </view>
        
        <view class="info-row" v-if="isRunning">
          <view class="info-item full-width">
            <text class="item-label">{{ t('bot.startTime') }}</text>
            <text class="item-value">{{ formatTime(botInfo.startTime) }}</text>
          </view>
        </view>
        
        <view class="info-row" v-if="!canStartToday">
          <view class="info-item full-width limit-warning">
            <uni-icons type="info" size="16" color="#ff9800"></uni-icons>
            <text>{{ t('bot.dailyLimitReached') }}</text>
          </view>
        </view>
        
        <view class="info-row" v-if="!isExchangeConnected">
          <view class="info-item full-width limit-warning">
            <uni-icons type="info" size="16" color="#ff9800"></uni-icons>
            <text>{{ t('bot.needExchangeConnect') }}</text>
          </view>
        </view>
      </view>
    </view>

    <!-- 交易历史 -->
    <view class="history-card">
      <view class="history-header">
        <text class="history-title">{{ t('bot.tradingHistory') }}</text>
        
        <view class="date-filters">
          <view 
            class="date-filter" 
            :class="{ active: currentDateFilter === 'today' }" 
            @click="setDateFilter('today')">
            {{ t('bot.today') }}
          </view>
          <view 
            class="date-filter" 
            :class="{ active: currentDateFilter === 'week' }" 
            @click="setDateFilter('week')">
            {{ t('bot.thisWeek') }}
          </view>
          <view 
            class="date-filter" 
            :class="{ active: currentDateFilter === 'month' }" 
            @click="setDateFilter('month')">
            {{ t('bot.thisMonth') }}
          </view>
        </view>
      </view>
      
      <view class="history-content">
        <view class="empty-state" v-if="tradingHistory.length === 0">
          <uni-icons type="list" size="40" color="#ccc"></uni-icons>
          <text>{{ t('bot.noTradingHistory') }}</text>
        </view>
        
        <view class="trade-list" v-else>
          <view 
            class="trade-item" 
            v-for="(trade, index) in tradingHistory" 
            :key="index"
            @click="navigateToDetail(trade.id)"
          >
            <view class="trade-info">
              <view class="trade-pair">{{ trade.symbol }}</view>
              <view class="trade-time">{{ formatTime(trade.time) }}</view>
            </view>
            
            <view class="trade-details">
              <view class="trade-type" :class="{ buy: trade.side === 'BUY', sell: trade.side === 'SELL' }">
                {{ trade.side }}
              </view>
              <view class="trade-price">{{ formatCurrency(trade.price) }}</view>
              <view class="trade-amount">{{ trade.amount }}</view>
              <view class="trade-profit" :class="{ positive: trade.profit > 0, negative: trade.profit < 0 }">
                {{ formatProfit(trade.profit) }}
              </view>
            </view>
          </view>
        </view>
      </view>
    </view>
  </view>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useBotStore } from '@/stores/bot'
import { useExchangeStore } from '@/stores/exchange'
import { useUserStore } from '@/stores/user'
import { useI18n } from 'vue-i18n'

const botStore = useBotStore()
const exchangeStore = useExchangeStore()
const userStore = useUserStore()
const { t } = useI18n()

// 自动刷新计时器
const refreshTimer = ref(null)

// 日期过滤器
const currentDateFilter = ref('today')

// 计算机器人状态
const botStatus = computed(() => botStore.getBotStatus)
const isRunning = computed(() => botStatus.value === 'running')
const isAnalyzing = computed(() => botStatus.value === 'analyzing')

// 获取交易所连接状态
const isExchangeConnected = computed(() => exchangeStore.getIsConnected)

// 获取机器人信息
const botInfo = computed(() => botStore.getBotInfo)

// 获取交易历史
const tradingHistory = computed(() => botStore.getTradingHistory)

// 获取用户交易限制
const tradingLimit = computed(() => botStore.getTradingLimit)

// 检查是否可以今天启动
const canStartToday = computed(() => botStore.getCanStartToday)

// 计算机器人状态图标
const botStatusIcon = computed(() => {
  if (isRunning.value) return 'spinner-cycle'
  if (isAnalyzing.value) return 'refreshempty'
  return 'poweroff'
})

// 计算机器人状态文本
const botStatusText = computed(() => {
  if (isRunning.value) return t('bot.running')
  if (isAnalyzing.value) return t('bot.analyzing')
  return t('bot.stopped')
})

// 组件挂载时获取数据
onMounted(async () => {
  // 获取机器人状态
  await botStore.getBotStatus()
  
  // 获取交易历史
  loadTradingHistory()
  
  // 设置自动刷新
  refreshTimer.value = setInterval(() => {
    if (isRunning.value) {
      botStore.getBotStatus()
    }
  }, 30000) // 每30秒刷新一次
})

// 组件卸载时清除计时器
onUnmounted(() => {
  if (refreshTimer.value) {
    clearInterval(refreshTimer.value)
  }
})

// 设置日期过滤器
function setDateFilter(filter) {
  currentDateFilter.value = filter
  loadTradingHistory()
}

// 加载交易历史
async function loadTradingHistory() {
  let startDate, endDate
  const today = new Date()
  
  endDate = new Date(today)
  
  switch (currentDateFilter.value) {
    case 'today':
      startDate = new Date(today.setHours(0, 0, 0, 0))
      break
    case 'week':
      // 计算本周的开始（周日为一周的开始）
      const day = today.getDay()
      startDate = new Date(today)
      startDate.setDate(today.getDate() - day)
      startDate.setHours(0, 0, 0, 0)
      break
    case 'month':
      // 计算本月的开始
      startDate = new Date(today.getFullYear(), today.getMonth(), 1)
      break
    default:
      startDate = new Date(today.setHours(0, 0, 0, 0))
  }
  
  await botStore.getTradingHistory({
    startDate: startDate.getTime(),
    endDate: endDate.getTime()
  })
}

// 启动机器人
async function startBot() {
  if (!isExchangeConnected.value) {
    uni.showToast({
      title: t('bot.connectExchangeFirst'),
      icon: 'none'
    })
    return
  }
  
  if (!canStartToday.value) {
    uni.showToast({
      title: t('bot.dailyLimitReached'),
      icon: 'none'
    })
    return
  }
  
  try {
    await botStore.startBot()
    uni.showToast({
      title: t('bot.botStarted'),
      icon: 'success'
    })
  } catch (error) {
    uni.showToast({
      title: error.message || t('common.error'),
      icon: 'none'
    })
  }
}

// 停止机器人
async function stopBot() {
  try {
    await botStore.stopBot()
    uni.showToast({
      title: t('bot.botStopped'),
      icon: 'success'
    })
  } catch (error) {
    uni.showToast({
      title: error.message || t('common.error'),
      icon: 'none'
    })
  }
}

// 格式化时间
function formatTime(timestamp) {
  if (!timestamp) return '-'
  const date = new Date(timestamp)
  return `${date.getFullYear()}-${padZero(date.getMonth() + 1)}-${padZero(date.getDate())} ${padZero(date.getHours())}:${padZero(date.getMinutes())}`
}

// 格式化利润
function formatProfit(profit) {
  if (profit === undefined || profit === null) return '$0.00'
  return profit >= 0 ? `+$${profit.toFixed(2)}` : `-$${Math.abs(profit).toFixed(2)}`
}

// 格式化货币
function formatCurrency(value) {
  if (!value) return '0.00'
  return parseFloat(value).toFixed(2)
}

// 补零
function padZero(num) {
  return num < 10 ? `0${num}` : num
}

// 导航到交易详情
function navigateToDetail(tradeId) {
  uni.navigateTo({
    url: `/pages/detail/trade?id=${tradeId}`
  })
}
</script>

<style lang="scss">
.bot-container {
  padding: 30rpx;
  background-color: #f8f9fa;
  min-height: 100vh;
}

.status-card {
  display: flex;
  justify-content: space-between;
  padding: 30rpx;
  border-radius: 12rpx;
  margin-bottom: 30rpx;
  background: linear-gradient(135deg, #ff5f6d, #ffc371);
  color: #fff;
  
  &.running {
    background: linear-gradient(135deg, #4facfe, #00f2fe);
  }
  
  &.stopped {
    background: linear-gradient(135deg, #7f7fd5, #91eae4);
  }
  
  .status-content {
    display: flex;
    align-items: center;
    
    .status-icon {
      width: 60rpx;
      height: 60rpx;
      border-radius: 50%;
      background-color: rgba(255, 255, 255, 0.2);
      display: flex;
      justify-content: center;
      align-items: center;
      margin-right: 20rpx;
      
      &.running {
        animation: spin 2s linear infinite;
      }
      
      &.analyzing {
        animation: pulse 1.5s ease-in-out infinite;
      }
    }
    
    .status-info {
      display: flex;
      flex-direction: column;
      
      .status-label {
        font-size: 24rpx;
        opacity: 0.8;
      }
      
      .status-value {
        font-size: 36rpx;
        font-weight: bold;
      }
    }
  }
  
  .action-buttons {
    .action-btn {
      min-width: 160rpx;
      height: 70rpx;
      line-height: 70rpx;
      border-radius: 35rpx;
      font-size: 28rpx;
      padding: 0 30rpx;
    }
    
    .start-btn {
      background-color: rgba(255, 255, 255, 0.2);
      color: #fff;
      
      &:disabled {
        opacity: 0.5;
      }
    }
    
    .stop-btn {
      background-color: rgba(255, 0, 0, 0.2);
      color: #fff;
    }
  }
}

.info-card, .history-card {
  background-color: #fff;
  border-radius: 12rpx;
  margin-bottom: 30rpx;
  overflow: hidden;
}

.info-header, .history-header {
  padding: 20rpx 30rpx;
  border-bottom: 1px solid #f0f0f0;
}

.info-title, .history-title {
  font-size: 30rpx;
  font-weight: bold;
  color: #333;
}

.info-content {
  padding: 30rpx;
}

.info-row {
  display: flex;
  margin-bottom: 30rpx;
  
  &:last-child {
    margin-bottom: 0;
  }
  
  .info-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    
    &.full-width {
      flex: 2;
    }
    
    &.limit-warning {
      flex-direction: row;
      align-items: center;
      background-color: #fff8e1;
      padding: 15rpx;
      border-radius: 6rpx;
      
      text {
        margin-left: 10rpx;
        color: #ff9800;
        font-size: 24rpx;
      }
    }
    
    .item-label {
      font-size: 24rpx;
      color: #999;
      margin-bottom: 10rpx;
    }
    
    .item-value {
      font-size: 32rpx;
      color: #333;
      font-weight: 500;
      
      &.profit {
        color: #52c41a;
      }
    }
  }
}

.history-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  
  .date-filters {
    display: flex;
    
    .date-filter {
      padding: 8rpx 20rpx;
      font-size: 24rpx;
      color: #666;
      border-radius: 20rpx;
      margin-left: 10rpx;
      
      &.active {
        background-color: #e6f7ff;
        color: #1890ff;
        font-weight: 500;
      }
    }
  }
}

.history-content {
  padding: 0 30rpx 30rpx;
  max-height: 800rpx;
  overflow-y: auto;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60rpx 0;
  color: #999;
  
  text {
    margin-top: 20rpx;
    font-size: 28rpx;
  }
}

.trade-list {
  padding-top: 20rpx;
}

.trade-item {
  border-bottom: 1px solid #f5f5f5;
  padding: 20rpx 0;
  
  &:last-child {
    border-bottom: none;
  }
  
  .trade-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10rpx;
    
    .trade-pair {
      font-weight: bold;
      color: #333;
    }
    
    .trade-time {
      font-size: 24rpx;
      color: #999;
    }
  }
  
  .trade-details {
    display: flex;
    justify-content: space-between;
    
    .trade-type, .trade-price, .trade-amount, .trade-profit {
      font-size: 24rpx;
    }
    
    .trade-type {
      width: 80rpx;
      text-align: center;
      padding: 4rpx 0;
      border-radius: 4rpx;
      font-weight: bold;
      
      &.buy {
        background-color: rgba(82, 196, 26, 0.1);
        color: #52c41a;
      }
      
      &.sell {
        background-color: rgba(255, 77, 79, 0.1);
        color: #ff4d4f;
      }
    }
    
    .trade-profit {
      font-weight: bold;
      
      &.positive {
        color: #52c41a;
      }
      
      &.negative {
        color: #ff4d4f;
      }
    }
  }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.6; }
  100% { opacity: 1; }
}
</style> 