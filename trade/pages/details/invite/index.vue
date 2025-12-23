<template>
  <view class="invite-container">
    <view class="invite-header">
      <view class="back-btn" @click="navigateBack">
        <text class="back-icon">&#8592;</text>
        <text>{{ $t('common.back') }}</text>
      </view>
      <view class="invite-title">{{ $t('agent.invite') }}</view>
    </view>
    
    <view class="invite-card">
      <view class="invite-code-section">
        <view class="invite-label">{{ $t('agent.yourInviteCode') }}</view>
        <view class="invite-code">{{ inviteCode }}</view>
        <button class="btn btn-primary btn-block" @click="copyInviteCode">
          {{ $t('common.copy') }}
        </button>
      </view>
      
      <view class="divider"></view>
      
      <view class="invite-tip-section">
        <view class="invite-tip-title">{{ $t('agent.inviteTip') }}</view>
        <view class="invite-steps">
          <view class="invite-step">
            <view class="step-number">1</view>
            <view class="step-text">{{ $t('agent.inviteStep1') }}</view>
          </view>
          <view class="invite-step">
            <view class="step-number">2</view>
            <view class="step-text">{{ $t('agent.inviteStep2') }}</view>
          </view>
          <view class="invite-step">
            <view class="step-number">3</view>
            <view class="step-text">{{ $t('agent.inviteStep3') }}</view>
          </view>
        </view>
      </view>
    </view>
    
    <view class="invite-benefits-card">
      <view class="benefits-title">{{ $t('agent.inviteBenefits') }}</view>
      <view class="benefits-list">
        <view class="benefit-item">
          <text class="benefit-icon">💰</text>
          <text class="benefit-text">{{ $t('agent.benefit1') }}</text>
        </view>
        <view class="benefit-item">
          <text class="benefit-icon">⬆️</text>
          <text class="benefit-text">{{ $t('agent.benefit2') }}</text>
        </view>
        <view class="benefit-item">
          <text class="benefit-icon">🔄</text>
          <text class="benefit-text">{{ $t('agent.benefit3') }}</text>
        </view>
      </view>
    </view>
    
    <view class="share-section">
      <button class="btn btn-outline share-btn" @click="shareToWechat">
        <text class="share-icon">WeChat</text>
      </button>
      <button class="btn btn-outline share-btn" @click="shareToWhatsapp">
        <text class="share-icon">WhatsApp</text>
      </button>
      <button class="btn btn-outline share-btn" @click="shareToTelegram">
        <text class="share-icon">Telegram</text>
      </button>
    </view>
  </view>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  computed: {
    ...mapGetters('user', ['inviteCode'])
  },
  methods: {
    // 返回上一页
    navigateBack() {
      uni.navigateBack()
    },
    
    // 复制邀请码
    copyInviteCode() {
      uni.setClipboardData({
        data: this.inviteCode,
        success: () => {
          uni.showToast({
            title: this.$t('common.copySuccess'),
            icon: 'success'
          })
        }
      })
    },
    
    // 分享到微信
    shareToWechat() {
      this.shareToApp('WeChat')
    },
    
    // 分享到WhatsApp
    shareToWhatsapp() {
      this.shareToApp('WhatsApp')
    },
    
    // 分享到Telegram
    shareToTelegram() {
      this.shareToApp('Telegram')
    },
    
    // 分享到应用
    shareToApp(app) {
      // 这里是模拟分享，实际应用中需要使用相应的分享API
      uni.showToast({
        title: this.$t('agent.shareSuccess') + ' ' + app,
        icon: 'success'
      })
    }
  }
}
</script>

<style lang="scss">
.invite-container {
  min-height: 100vh;
  padding: 20px;
  background-color: var(--bg-color);
}

.invite-header {
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

.invite-title {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  font-size: 18px;
  font-weight: bold;
  color: var(--text-color);
}

.invite-card {
  background-color: var(--card-bg-color);
  border-radius: 10px;
  padding: 20px;
  margin-bottom: 15px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.invite-code-section {
  text-align: center;
  margin-bottom: 20px;
}

.invite-label {
  font-size: 14px;
  color: var(--text-color-secondary);
  margin-bottom: 10px;
}

.invite-code {
  font-size: 28px;
  font-weight: bold;
  color: var(--primary-color);
  margin-bottom: 15px;
  letter-spacing: 2px;
}

.invite-tip-section {
  margin-top: 20px;
}

.invite-tip-title {
  font-size: 16px;
  font-weight: bold;
  color: var(--text-color);
  margin-bottom: 15px;
  text-align: center;
}

.invite-steps {
  margin-top: 15px;
}

.invite-step {
  display: flex;
  align-items: flex-start;
  margin-bottom: 15px;
}

.step-number {
  width: 24px;
  height: 24px;
  border-radius: 12px;
  background-color: var(--primary-color);
  color: white;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 14px;
  margin-right: 10px;
  flex-shrink: 0;
}

.step-text {
  font-size: 14px;
  color: var(--text-color);
  flex: 1;
}

.invite-benefits-card {
  background-color: var(--card-bg-color);
  border-radius: 10px;
  padding: 20px;
  margin-bottom: 15px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.benefits-title {
  font-size: 16px;
  font-weight: bold;
  color: var(--text-color);
  margin-bottom: 15px;
  text-align: center;
}

.benefits-list {
  margin-top: 15px;
}

.benefit-item {
  display: flex;
  align-items: center;
  margin-bottom: 15px;
}

.benefit-icon {
  font-size: 20px;
  margin-right: 10px;
}

.benefit-text {
  font-size: 14px;
  color: var(--text-color);
  flex: 1;
}

.share-section {
  display: flex;
  justify-content: space-between;
  margin-top: 20px;
}

.share-btn {
  flex: 1;
  margin: 0 5px;
  height: 40px;
  font-size: 14px;
}

.share-icon {
  font-size: 14px;
}
</style> 