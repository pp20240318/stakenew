<template>
  <view class="profile-container">
    <view class="profile-header">
      <view class="avatar-container">
        <image class="avatar" src="/static/img/avatar.png" mode="aspectFill"></image>
      </view>
      
      <view class="user-info">
        <view class="username">{{ userInfo.username }}</view>
        <view class="agent-level">{{ $t(`agent.level${userInfo.agentLevel}`) }}</view>
      </view>
      
      <view class="invite-code-container">
        <view class="invite-code-label">{{ $t('agent.yourInviteCode') }}</view>
        <view class="invite-code-value">
          <text class="invite-code">{{ userInfo.inviteCode }}</text>
          <text class="copy-btn" @click="copyInviteCode">{{ $t('common.copy') }}</text>
        </view>
      </view>
    </view>
    
    <!-- 菜单列表 -->
    <view class="menu-list-card">
      <view class="menu-group">
        <view class="menu-item" @click="navigateToSetting('personalInfo')">
          <text class="menu-icon">👤</text>
          <text class="menu-title">{{ $t('profile.personalInfo') }}</text>
          <text class="menu-arrow">›</text>
        </view>
        
        <view class="menu-item" @click="navigateToSetting('security')">
          <text class="menu-icon">🔒</text>
          <text class="menu-title">{{ $t('profile.security') }}</text>
          <text class="menu-arrow">›</text>
        </view>
      </view>
      
      <view class="menu-group">
        <view class="menu-item" @click="navigateToSetting('language')">
          <text class="menu-icon">🌐</text>
          <text class="menu-title">{{ $t('profile.language') }}</text>
          <text class="menu-value">{{ getCurrentLanguage() }}</text>
          <text class="menu-arrow">›</text>
        </view>
        
        <view class="menu-item" @click="navigateToSetting('notifications')">
          <text class="menu-icon">🔔</text>
          <text class="menu-title">{{ $t('profile.notifications') }}</text>
          <text class="menu-arrow">›</text>
        </view>
      </view>
      
      <view class="menu-group">
        <view class="menu-item" @click="navigateToSetting('help')">
          <text class="menu-icon">❓</text>
          <text class="menu-title">{{ $t('profile.help') }}</text>
          <text class="menu-arrow">›</text>
        </view>
        
        <view class="menu-item" @click="navigateToSetting('about')">
          <text class="menu-icon">ℹ️</text>
          <text class="menu-title">{{ $t('profile.about') }}</text>
          <text class="menu-arrow">›</text>
        </view>
      </view>
      
      <view class="menu-group">
        <view class="menu-item logout-item" @click="handleLogout">
          <text class="menu-icon">🚪</text>
          <text class="menu-title logout-text">{{ $t('profile.logout') }}</text>
        </view>
      </view>
    </view>
    
    <!-- 应用版本信息 -->
    <view class="version-info">
      <text>{{ $t('profile.currentVersion') }}: 1.0.0</text>
    </view>
  </view>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'

export default {
  computed: {
    ...mapGetters('user', ['userInfo'])
  },
  methods: {
    ...mapActions('user', ['logout']),
    
    // 复制邀请码
    copyInviteCode() {
      uni.setClipboardData({
        data: this.userInfo.inviteCode,
        success: () => {
          uni.showToast({
            title: this.$t('common.copySuccess'),
            icon: 'success'
          })
        }
      })
    },
    
    // 导航到设置页面
    navigateToSetting(type) {
      if (type === 'language') {
        uni.navigateTo({
          url: '/pages/settings/language'
        })
      } else if (type === 'security') {
        uni.navigateTo({
          url: '/pages/settings/security'
        })
      } else if (type === 'about') {
        uni.navigateTo({
          url: '/pages/settings/about'
        })
      } else {
        uni.showToast({
          title: this.$t('common.success'),
          icon: 'success'
        })
      }
    },
    
    // 处理退出登录
    handleLogout() {
      uni.showModal({
        title: this.$t('common.tip'),
        content: this.$t('profile.logoutConfirm'),
        confirmText: this.$t('common.confirm'),
        cancelText: this.$t('common.cancel'),
        success: (res) => {
          if (res.confirm) {
            this.logout()
          }
        }
      })
    },
    
    // 获取当前语言
    getCurrentLanguage() {
      return this.$i18n.locale === 'zh-hant' ? '繁體中文' : 'English'
    }
  }
}
</script>

<style lang="scss">
.profile-container {
  min-height: 100vh;
  padding: 20px;
  background-color: var(--bg-color);
}

.profile-header {
  background-color: var(--card-bg-color);
  border-radius: 10px;
  padding: 20px;
  margin-bottom: 15px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.avatar-container {
  display: flex;
  justify-content: center;
  margin-bottom: 15px;
}

.avatar {
  width: 80px;
  height: 80px;
  border-radius: 40px;
  border: 2px solid var(--primary-color);
}

.user-info {
  text-align: center;
  margin-bottom: 15px;
}

.username {
  font-size: 18px;
  font-weight: bold;
  color: var(--text-color);
  margin-bottom: 5px;
}

.agent-level {
  display: inline-block;
  font-size: 12px;
  padding: 2px 8px;
  background-color: rgba(0, 122, 255, 0.1);
  color: var(--primary-color);
  border-radius: 10px;
}

.invite-code-container {
  background-color: rgba(0, 0, 0, 0.03);
  border-radius: 8px;
  padding: 10px;
}

.invite-code-label {
  font-size: 12px;
  color: var(--text-color-secondary);
  margin-bottom: 5px;
}

.invite-code-value {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.invite-code {
  font-size: 16px;
  font-weight: bold;
  color: var(--text-color);
}

.copy-btn {
  font-size: 12px;
  padding: 4px 8px;
  background-color: var(--primary-color);
  color: white;
  border-radius: 4px;
}

.menu-list-card {
  background-color: var(--card-bg-color);
  border-radius: 10px;
  margin-bottom: 15px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
  overflow: hidden;
}

.menu-group {
  margin-bottom: 10px;
  border-bottom: 8px solid var(--bg-color);
}

.menu-group:last-child {
  margin-bottom: 0;
  border-bottom: none;
}

.menu-item {
  display: flex;
  align-items: center;
  padding: 15px;
  background-color: var(--card-bg-color);
  position: relative;
}

.menu-item::after {
  content: '';
  position: absolute;
  left: 15px;
  right: 15px;
  bottom: 0;
  height: 1px;
  background-color: var(--border-color);
}

.menu-item:last-child::after {
  display: none;
}

.menu-icon {
  margin-right: 10px;
  font-size: 18px;
}

.menu-title {
  flex: 1;
  font-size: 16px;
  color: var(--text-color);
}

.menu-value {
  font-size: 14px;
  color: var(--text-color-secondary);
  margin-right: 5px;
}

.menu-arrow {
  font-size: 18px;
  color: var(--text-color-secondary);
}

.logout-item {
  justify-content: center;
}

.logout-text {
  flex: 0;
  color: var(--danger-color);
}

.version-info {
  text-align: center;
  font-size: 12px;
  color: var(--text-color-secondary);
  margin-top: 20px;
}
</style> 