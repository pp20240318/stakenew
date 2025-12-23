<template>
  <view class="profile-container">
    <!-- 用户信息卡片 -->
    <view class="user-card">
      <view class="user-info">
        <view class="avatar">
          <text>{{ userInitial }}</text>
        </view>
        <view class="user-details">
          <text class="username">{{ userInfo.username || t('profile.guest') }}</text>
          <view class="user-level">
            <text>{{ t('agent.levelLabel', { level: userInfo.agentLevel || 1 }) }}</text>
          </view>
        </view>
      </view>
      
      <view class="user-stats">
        <view class="stat-item">
          <text class="stat-value">${{ formatCurrency(userInfo.totalProfit) }}</text>
          <text class="stat-label">{{ t('profile.totalProfit') }}</text>
        </view>
        <view class="stat-item">
          <text class="stat-value">{{ userInfo.tradeCount || 0 }}</text>
          <text class="stat-label">{{ t('profile.totalTrades') }}</text>
        </view>
        <view class="stat-item">
          <text class="stat-value">{{ userInfo.invitedCount || 0 }}</text>
          <text class="stat-label">{{ t('profile.invitedUsers') }}</text>
        </view>
      </view>
    </view>
    
    <!-- 菜单项 -->
    <view class="menu-card">
      <view class="menu-group">
        <view class="menu-title">{{ t('profile.accountSettings') }}</view>
        
        <view class="menu-item" @click="navigateTo('/pages/profile/language')">
          <view class="menu-icon language-icon">
            <uni-icons type="language" size="24" color="#1890ff"></uni-icons>
          </view>
          <view class="menu-content">
            <text class="menu-label">{{ t('profile.language') }}</text>
            <view class="menu-right">
              <text class="menu-value">{{ currentLanguageLabel }}</text>
              <uni-icons type="right" size="16" color="#ccc"></uni-icons>
            </view>
          </view>
        </view>
        
        <view class="menu-item" @click="navigateTo('/pages/profile/password')">
          <view class="menu-icon password-icon">
            <uni-icons type="locked" size="24" color="#52c41a"></uni-icons>
          </view>
          <view class="menu-content">
            <text class="menu-label">{{ t('profile.changePassword') }}</text>
            <view class="menu-right">
              <uni-icons type="right" size="16" color="#ccc"></uni-icons>
            </view>
          </view>
        </view>
      </view>
      
      <view class="menu-group">
        <view class="menu-title">{{ t('profile.aboutApp') }}</view>
        
        <view class="menu-item" @click="navigateTo('/pages/profile/about')">
          <view class="menu-icon about-icon">
            <uni-icons type="info" size="24" color="#722ed1"></uni-icons>
          </view>
          <view class="menu-content">
            <text class="menu-label">{{ t('profile.about') }}</text>
            <view class="menu-right">
              <uni-icons type="right" size="16" color="#ccc"></uni-icons>
            </view>
          </view>
        </view>
        
        <view class="menu-item" @click="checkUpdate">
          <view class="menu-icon update-icon">
            <uni-icons type="refresh" size="24" color="#fa8c16"></uni-icons>
          </view>
          <view class="menu-content">
            <text class="menu-label">{{ t('profile.checkUpdate') }}</text>
            <view class="menu-right">
              <text class="menu-value">v{{ appVersion }}</text>
              <uni-icons type="right" size="16" color="#ccc"></uni-icons>
            </view>
          </view>
        </view>
        
        <view class="menu-item" @click="navigateTo('/pages/profile/terms')">
          <view class="menu-icon terms-icon">
            <uni-icons type="paperplane" size="24" color="#13c2c2"></uni-icons>
          </view>
          <view class="menu-content">
            <text class="menu-label">{{ t('profile.termsOfService') }}</text>
            <view class="menu-right">
              <uni-icons type="right" size="16" color="#ccc"></uni-icons>
            </view>
          </view>
        </view>
      </view>
      
      <view class="menu-group">
        <view class="menu-item logout" @click="handleLogout">
          <view class="menu-icon logout-icon">
            <uni-icons type="logout" size="24" color="#f5222d"></uni-icons>
          </view>
          <view class="menu-content">
            <text class="menu-label">{{ t('profile.logout') }}</text>
            <view class="menu-right">
              <uni-icons type="right" size="16" color="#ccc"></uni-icons>
            </view>
          </view>
        </view>
      </view>
    </view>
  </view>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useUserStore } from '@/stores/user'
import { useI18n } from 'vue-i18n'

const userStore = useUserStore()
const { t, locale } = useI18n()

// App版本
const appVersion = ref('1.0.0')

// 获取用户信息
const userInfo = computed(() => userStore.getUserInfo)

// 计算用户名首字母
const userInitial = computed(() => {
  if (!userInfo.value || !userInfo.value.username) return '?'
  return userInfo.value.username.charAt(0).toUpperCase()
})

// 当前语言标签
const currentLanguageLabel = computed(() => {
  return locale.value === 'en' ? 'English' : '繁體中文'
})

// 组件挂载时获取用户信息
onMounted(async () => {
  // 如果用户已登录，获取用户信息
  if (userStore.getIsLogin) {
    await userStore.getUserInfo()
  } else {
    // 未登录则跳转到登录页
    uni.redirectTo({
      url: '/pages/login/index'
    })
  }
})

// 页面导航
function navigateTo(url) {
  uni.navigateTo({ url })
}

// 检查更新
function checkUpdate() {
  uni.showLoading({
    title: t('common.loading')
  })
  
  // 模拟检查更新
  setTimeout(() => {
    uni.hideLoading()
    uni.showModal({
      title: t('profile.updateInfo'),
      content: t('profile.alreadyLatestVersion'),
      showCancel: false
    })
  }, 1000)
}

// 处理退出登录
function handleLogout() {
  uni.showModal({
    title: t('profile.confirmLogout'),
    content: t('profile.logoutConfirmContent'),
    success: (res) => {
      if (res.confirm) {
        userStore.logout()
      }
    }
  })
}

// 格式化货币
function formatCurrency(value) {
  if (value === undefined || value === null) return '0.00'
  return parseFloat(value).toFixed(2)
}
</script>

<style lang="scss">
.profile-container {
  padding: 30rpx;
  background-color: #f8f9fa;
  min-height: 100vh;
}

.user-card {
  background-color: #fff;
  border-radius: 12rpx;
  padding: 30rpx;
  margin-bottom: 30rpx;
  box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.05);
}

.user-info {
  display: flex;
  align-items: center;
  margin-bottom: 30rpx;
  
  .avatar {
    width: 120rpx;
    height: 120rpx;
    border-radius: 50%;
    background: linear-gradient(135deg, #1890ff, #722ed1);
    display: flex;
    justify-content: center;
    align-items: center;
    margin-right: 30rpx;
    
    text {
      color: #fff;
      font-size: 60rpx;
      font-weight: bold;
    }
  }
  
  .user-details {
    flex: 1;
    
    .username {
      font-size: 36rpx;
      font-weight: bold;
      color: #333;
      margin-bottom: 10rpx;
      display: block;
    }
    
    .user-level {
      display: inline-block;
      background-color: #e6f7ff;
      padding: 6rpx 16rpx;
      border-radius: 20rpx;
      
      text {
        color: #1890ff;
        font-size: 24rpx;
      }
    }
  }
}

.user-stats {
  display: flex;
  border-top: 1px solid #f0f0f0;
  padding-top: 30rpx;
  
  .stat-item {
    flex: 1;
    text-align: center;
    
    .stat-value {
      font-size: 32rpx;
      font-weight: bold;
      color: #333;
      margin-bottom: 10rpx;
      display: block;
    }
    
    .stat-label {
      font-size: 24rpx;
      color: #999;
    }
  }
}

.menu-card {
  background-color: #fff;
  border-radius: 12rpx;
  overflow: hidden;
}

.menu-group {
  margin-bottom: 20rpx;
  
  &:last-child {
    margin-bottom: 0;
  }
  
  .menu-title {
    font-size: 28rpx;
    color: #999;
    padding: 20rpx 30rpx;
  }
}

.menu-item {
  display: flex;
  align-items: center;
  padding: 30rpx;
  position: relative;
  
  &::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 30rpx;
    right: 30rpx;
    height: 1px;
    background-color: #f5f5f5;
  }
  
  &:last-child::after {
    display: none;
  }
  
  .menu-icon {
    width: 80rpx;
    height: 80rpx;
    border-radius: 50%;
    background-color: rgba(24, 144, 255, 0.1);
    display: flex;
    justify-content: center;
    align-items: center;
    margin-right: 20rpx;
    
    &.language-icon {
      background-color: rgba(24, 144, 255, 0.1);
    }
    
    &.password-icon {
      background-color: rgba(82, 196, 26, 0.1);
    }
    
    &.about-icon {
      background-color: rgba(114, 46, 209, 0.1);
    }
    
    &.update-icon {
      background-color: rgba(250, 140, 22, 0.1);
    }
    
    &.terms-icon {
      background-color: rgba(19, 194, 194, 0.1);
    }
    
    &.logout-icon {
      background-color: rgba(245, 34, 45, 0.1);
    }
  }
  
  .menu-content {
    flex: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
    
    .menu-label {
      font-size: 28rpx;
      color: #333;
    }
    
    .menu-right {
      display: flex;
      align-items: center;
      
      .menu-value {
        font-size: 24rpx;
        color: #999;
        margin-right: 10rpx;
      }
    }
  }
  
  &.logout {
    .menu-label {
      color: #f5222d;
    }
  }
}
</style> 