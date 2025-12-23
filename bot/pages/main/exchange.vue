<template>
  <view class="exchange-container">
    <!-- 头部 -->
    <view class="header">
      <view class="title">{{ t('exchange.title') }}</view>
    </view>

    <!-- 交易所连接状态 -->
    <view class="connection-status">
      <view class="status-indicator" :class="{ connected: isConnected }">
        <view class="status-dot"></view>
        <text>{{ isConnected ? t('exchange.connected') : t('exchange.disconnected') }}</text>
      </view>
      <text v-if="isConnected" class="exchange-name">{{ exchangeInfo.name }}</text>
    </view>

    <!-- 账户资产信息 -->
    <view class="account-info" v-if="isConnected">
      <view class="total-assets">
        <text class="label">{{ t('exchange.totalAssets') }}</text>
        <text class="value">${{ totalAssets.toFixed(2) }}</text>
      </view>
      
      <view class="assets-list">
        <view class="asset-item" v-for="(balance, index) in balances" :key="index">
          <text class="asset-name">{{ balance.asset }}</text>
          <text class="asset-value">{{ balance.free }}</text>
        </view>
      </view>
      
      <button class="disconnect-btn" @click="disconnectExchange">
        {{ t('exchange.disconnect') }}
      </button>
    </view>

    <!-- 交易所连接表单 -->
    <view class="connect-form" v-if="!isConnected">
      <view class="form-title">{{ t('exchange.connectExchange') }}</view>
      
      <view class="select-group">
        <text class="label">{{ t('exchange.selectExchange') }}</text>
        <picker @change="onExchangeChange" :value="selectedExchangeIndex" :range="exchangeNames">
          <view class="picker">
            {{ exchangeNames[selectedExchangeIndex] }}
          </view>
        </picker>
      </view>
      
      <view class="input-group">
        <text class="label">{{ t('exchange.apiKey') }}</text>
        <input type="text" v-model="form.apiKey" :placeholder="t('exchange.enterApiKey')" />
      </view>
      
      <view class="input-group">
        <text class="label">{{ t('exchange.apiSecret') }}</text>
        <input type="password" v-model="form.apiSecret" :placeholder="t('exchange.enterApiSecret')" password="true" />
      </view>
      
      <view class="input-group" v-if="requiresPassphrase">
        <text class="label">{{ t('exchange.passphrase') }}</text>
        <input type="password" v-model="form.passphrase" :placeholder="t('exchange.enterPassphrase')" password="true" />
      </view>
      
      <button class="connect-btn" @click="handleConnect">
        {{ t('exchange.connect') }}
      </button>
    </view>
  </view>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useExchangeStore } from '@/stores/exchange'
import { useI18n } from 'vue-i18n'

const exchangeStore = useExchangeStore()
const { t } = useI18n()

// 表单数据
const form = reactive({
  apiKey: '',
  apiSecret: '',
  passphrase: ''
})

// 选中的交易所索引
const selectedExchangeIndex = ref(0)

// 获取交易所列表
const exchangeList = computed(() => exchangeStore.getExchangeList)

// 获取所有交易所名称列表
const exchangeNames = computed(() => exchangeList.value.map(exchange => exchange.name))

// 检查是否连接
const isConnected = computed(() => exchangeStore.getIsConnected)

// 获取交易所信息
const exchangeInfo = computed(() => exchangeStore.getExchangeInfo)

// 获取账户信息
const accountInfo = computed(() => exchangeStore.getAccountInfo)

// 获取总资产
const totalAssets = computed(() => exchangeStore.getTotalAssets)

// 获取余额列表
const balances = computed(() => exchangeStore.getBalances)

// 当前选择的交易所是否需要密码短语
const requiresPassphrase = computed(() => {
  const selectedExchange = exchangeList.value[selectedExchangeIndex.value]
  return selectedExchange && selectedExchange.requiresPassphrase
})

// 组件挂载时检查连接状态
onMounted(async () => {
  // 如果已连接，获取最新账户信息
  if (isConnected.value) {
    try {
      await exchangeStore.getAccountInfo()
    } catch (error) {
      uni.showToast({
        title: error.message || t('common.error'),
        icon: 'none'
      })
    }
  }
})

// 交易所选择改变
function onExchangeChange(e) {
  selectedExchangeIndex.value = e.detail.value
}

// 连接交易所
async function handleConnect() {
  // 验证表单
  if (!form.apiKey || !form.apiSecret) {
    uni.showToast({
      title: t('exchange.enterApiCredentials'),
      icon: 'none'
    })
    return
  }
  
  if (requiresPassphrase.value && !form.passphrase) {
    uni.showToast({
      title: t('exchange.enterPassphrase'),
      icon: 'none'
    })
    return
  }
  
  const selectedExchange = exchangeList.value[selectedExchangeIndex.value]
  
  try {
    uni.showLoading({
      title: t('common.loading')
    })
    
    await exchangeStore.connectExchange({
      exchangeId: selectedExchange.id,
      name: selectedExchange.name,
      apiKey: form.apiKey,
      apiSecret: form.apiSecret,
      passphrase: form.passphrase
    })
    
    uni.hideLoading()
    
    uni.showToast({
      title: t('exchange.connectSuccess'),
      icon: 'success'
    })
  } catch (error) {
    uni.hideLoading()
    uni.showToast({
      title: error.message || t('exchange.connectFailed'),
      icon: 'none'
    })
  }
}

// 断开交易所连接
function disconnectExchange() {
  uni.showModal({
    title: t('exchange.confirmDisconnect'),
    content: t('exchange.disconnectWarning'),
    success: (res) => {
      if (res.confirm) {
        exchangeStore.disconnectExchange()
        uni.showToast({
          title: t('exchange.disconnected'),
          icon: 'success'
        })
      }
    }
  })
}
</script>

<style lang="scss">
.exchange-container {
  padding: 30rpx;
  background-color: #f8f9fa;
  min-height: 100vh;
}

.header {
  margin-bottom: 30rpx;
  
  .title {
    font-size: 36rpx;
    font-weight: bold;
    color: #333;
  }
}

.connection-status {
  background-color: #fff;
  padding: 20rpx;
  border-radius: 10rpx;
  margin-bottom: 30rpx;
  display: flex;
  justify-content: space-between;
  align-items: center;
  
  .status-indicator {
    display: flex;
    align-items: center;
    
    .status-dot {
      width: 20rpx;
      height: 20rpx;
      border-radius: 50%;
      background-color: #ff4d4f;
      margin-right: 10rpx;
    }
    
    &.connected .status-dot {
      background-color: #52c41a;
    }
  }
  
  .exchange-name {
    font-weight: bold;
    color: #1890ff;
  }
}

.account-info {
  background-color: #fff;
  padding: 30rpx;
  border-radius: 10rpx;
  margin-bottom: 30rpx;
  
  .total-assets {
    display: flex;
    justify-content: space-between;
    margin-bottom: 30rpx;
    padding-bottom: 20rpx;
    border-bottom: 1px solid #f0f0f0;
    
    .label {
      color: #666;
    }
    
    .value {
      font-size: 32rpx;
      font-weight: bold;
      color: #1890ff;
    }
  }
  
  .assets-list {
    max-height: 400rpx;
    overflow-y: auto;
    
    .asset-item {
      display: flex;
      justify-content: space-between;
      padding: 15rpx 0;
      border-bottom: 1px solid #f8f8f8;
      
      .asset-name {
        color: #333;
        font-weight: 500;
      }
      
      .asset-value {
        color: #666;
      }
    }
  }
  
  .disconnect-btn {
    margin-top: 30rpx;
    background-color: #ff4d4f;
    color: #fff;
  }
}

.connect-form {
  background-color: #fff;
  padding: 30rpx;
  border-radius: 10rpx;
  
  .form-title {
    font-size: 30rpx;
    font-weight: bold;
    margin-bottom: 30rpx;
  }
  
  .select-group, .input-group {
    margin-bottom: 25rpx;
    
    .label {
      display: block;
      margin-bottom: 10rpx;
      color: #666;
    }
    
    .picker {
      height: 70rpx;
      line-height: 70rpx;
      padding: 0 20rpx;
      background-color: #f8f8f8;
      border-radius: 6rpx;
    }
    
    input {
      height: 70rpx;
      background-color: #f8f8f8;
      border-radius: 6rpx;
      padding: 0 20rpx;
    }
  }
  
  .connect-btn {
    margin-top: 30rpx;
    background: linear-gradient(to right, #1890ff, #1677ff);
    color: #fff;
  }
}
</style> 