<template>
  <view class="exchange-container">
    <!-- 未连接状态 -->
    <block v-if="!isConnected">
      <view class="exchange-header">
        <view class="exchange-title">{{ $t('exchange.title') }}</view>
      </view>
      
      <view class="connect-tip-card">
        <view class="connect-tip-text">{{ $t('exchange.connectTip') }}</view>
      </view>
      
      <view class="exchange-form-card">
        <view class="form-group">
          <label class="form-label">{{ $t('exchange.chooseExchange') }}</label>
          <picker 
            :range="exchangeNameList" 
            @change="handleExchangeChange" 
            :value="selectedExchangeIndex"
          >
            <view class="picker-view">
              <text>{{ selectedExchangeName || $t('exchange.chooseExchange') }}</text>
              <text class="picker-arrow">▼</text>
            </view>
          </picker>
        </view>
        
        <view class="form-group">
          <label class="form-label">{{ $t('exchange.apiKey') }}</label>
          <input 
            class="form-input" 
            type="text" 
            v-model="form.apiKey" 
            :placeholder="$t('exchange.apiKey')"
          />
        </view>
        
        <view class="form-group">
          <label class="form-label">{{ $t('exchange.apiSecret') }}</label>
          <input 
            class="form-input" 
            type="password" 
            v-model="form.apiSecret" 
            :placeholder="$t('exchange.apiSecret')"
          />
        </view>
        
        <view class="form-group">
          <label class="form-label">{{ $t('exchange.remarks') }}</label>
          <input 
            class="form-input" 
            type="text" 
            v-model="form.remarks" 
            :placeholder="$t('exchange.remarks')"
          />
        </view>
        
        <view class="security-tip">
          <text>{{ $t('exchange.securityTip') }}</text>
        </view>
        
        <button class="btn btn-primary btn-block" @click="handleConnect">
          {{ $t('exchange.connect') }}
        </button>
      </view>
    </block>
    
    <!-- 已连接状态 -->
    <block v-else>
      <view class="exchange-header">
        <view class="exchange-title">{{ $t('exchange.title') }}</view>
        <view class="exchange-status connected">
          {{ $t('exchange.connected') }}
        </view>
      </view>
      
      <view class="exchange-info-card">
        <view class="exchange-name">
          <text>{{ exchangeInfo.exchangeName }}</text>
          <text class="exchange-remark" v-if="exchangeInfo.remarks">
            ({{ exchangeInfo.remarks }})
          </text>
        </view>
        
        <view class="total-assets">
          <text class="assets-label">{{ $t('exchange.totalAssets') }}</text>
          <text class="assets-value">{{ totalAssets }} USDT</text>
        </view>
        
        <view class="divider"></view>
        
        <view class="balance-list">
          <view class="balance-title">{{ $t('exchange.balance') }}</view>
          <view 
            class="balance-item" 
            v-for="(item, index) in balances" 
            :key="index"
          >
            <text class="balance-asset">{{ item.asset }}</text>
            <text class="balance-amount">{{ item.free }}</text>
          </view>
        </view>
        
        <button class="btn btn-outline btn-block" @click="handleDisconnect">
          {{ $t('exchange.disconnect') }}
        </button>
      </view>
    </block>
  </view>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'

export default {
  data() {
    return {
      form: {
        apiKey: '',
        apiSecret: '',
        remarks: ''
      },
      selectedExchangeIndex: 0
    }
  },
  computed: {
    ...mapGetters('exchange', [
      'isConnected',
      'exchangeInfo',
      'exchangeList',
      'totalAssets',
      'balances'
    ]),
    
    // 交易所名称列表
    exchangeNameList() {
      return this.exchangeList.map(item => item.name)
    },
    
    // 选中的交易所名称
    selectedExchangeName() {
      if (this.selectedExchangeIndex >= 0 && this.exchangeList.length > 0) {
        return this.exchangeList[this.selectedExchangeIndex].name
      }
      return ''
    },
    
    // 选中的交易所ID
    selectedExchangeId() {
      if (this.selectedExchangeIndex >= 0 && this.exchangeList.length > 0) {
        return this.exchangeList[this.selectedExchangeIndex].id
      }
      return ''
    }
  },
  onLoad() {
    // 获取账户信息
    if (this.isConnected) {
      this.getAccountInfo()
    }
  },
  methods: {
    ...mapActions('exchange', [
      'connectExchange',
      'disconnectExchange',
      'getAccountInfo'
    ]),
    
    // 处理交易所选择
    handleExchangeChange(e) {
      this.selectedExchangeIndex = e.detail.value
    },
    
    // 处理连接交易所
    async handleConnect() {
      // 表单验证
      if (!this.selectedExchangeId || !this.form.apiKey || !this.form.apiSecret) {
        uni.showToast({
          title: this.$t('common.failed'),
          icon: 'none'
        })
        return
      }
      
      try {
        uni.showLoading({
          title: this.$t('common.loading')
        })
        
        await this.connectExchange({
          exchangeId: this.selectedExchangeId,
          apiKey: this.form.apiKey,
          apiSecret: this.form.apiSecret,
          remarks: this.form.remarks,
          exchangeList: this.exchangeList
        })
        
        uni.hideLoading()
        
        uni.showToast({
          title: this.$t('exchange.connectSuccess'),
          icon: 'success'
        })
      } catch (error) {
        uni.hideLoading()
        uni.showToast({
          title: this.$t('exchange.connectFailed'),
          icon: 'none'
        })
      }
    },
    
    // 处理断开连接
    async handleDisconnect() {
      uni.showModal({
        title: this.$t('common.tip'),
        content: this.$t('exchange.disconnect') + '?',
        confirmText: this.$t('common.confirm'),
        cancelText: this.$t('common.cancel'),
        success: async (res) => {
          if (res.confirm) {
            try {
              uni.showLoading({
                title: this.$t('common.loading')
              })
              
              await this.disconnectExchange()
              
              uni.hideLoading()
              
              this.form = {
                apiKey: '',
                apiSecret: '',
                remarks: ''
              }
              
              uni.showToast({
                title: this.$t('common.success'),
                icon: 'success'
              })
            } catch (error) {
              uni.hideLoading()
              uni.showToast({
                title: this.$t('common.failed'),
                icon: 'none'
              })
            }
          }
        }
      })
    }
  }
}
</script>

<style lang="scss">
.exchange-container {
  min-height: 100vh;
  padding: 20px;
  background-color: var(--bg-color);
}

.exchange-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.exchange-title {
  font-size: 20px;
  font-weight: bold;
  color: var(--text-color);
}

.exchange-status {
  font-size: 14px;
  padding: 4px 8px;
  border-radius: 12px;
  background-color: var(--success-color);
  color: white;
}

.connect-tip-card {
  background-color: var(--card-bg-color);
  border-radius: 10px;
  padding: 15px;
  margin-bottom: 15px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.connect-tip-text {
  color: var(--text-color);
  font-size: 14px;
  line-height: 1.5;
}

.exchange-form-card {
  background-color: var(--card-bg-color);
  border-radius: 10px;
  padding: 20px;
  margin-bottom: 15px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.picker-view {
  width: 100%;
  height: 44px;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  padding: 0 15px;
  font-size: 14px;
  color: var(--text-color);
  background-color: var(--card-bg-color);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.picker-arrow {
  font-size: 12px;
  color: var(--text-color-secondary);
}

.security-tip {
  font-size: 12px;
  color: var(--text-color-secondary);
  margin-bottom: 15px;
}

.exchange-info-card {
  background-color: var(--card-bg-color);
  border-radius: 10px;
  padding: 20px;
  margin-bottom: 15px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.exchange-name {
  font-size: 18px;
  font-weight: bold;
  color: var(--text-color);
  margin-bottom: 10px;
}

.exchange-remark {
  font-size: 14px;
  font-weight: normal;
  color: var(--text-color-secondary);
  margin-left: 5px;
}

.total-assets {
  display: flex;
  flex-direction: column;
  margin-bottom: 15px;
}

.assets-label {
  font-size: 14px;
  color: var(--text-color-secondary);
  margin-bottom: 5px;
}

.assets-value {
  font-size: 24px;
  font-weight: bold;
  color: var(--text-color);
}

.balance-list {
  margin-top: 15px;
}

.balance-title {
  font-size: 16px;
  font-weight: bold;
  color: var(--text-color);
  margin-bottom: 10px;
}

.balance-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 0;
  border-bottom: 1px solid var(--border-color);
}

.balance-item:last-child {
  border-bottom: none;
}

.balance-asset {
  font-size: 14px;
  color: var(--text-color);
}

.balance-amount {
  font-size: 14px;
  font-weight: bold;
  color: var(--text-color);
}

.btn {
  margin-top: 15px;
}
</style> 