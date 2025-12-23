<template>
  <view class="language-container">
    <view class="language-header">
      <view class="back-btn" @click="navigateBack">
        <text class="back-icon">&#8592;</text>
        <text>{{ $t('common.back') }}</text>
      </view>
      <view class="language-title">{{ $t('profile.language') }}</view>
    </view>
    
    <view class="language-list-card">
      <view 
        class="language-item" 
        :class="{ active: currentLanguage === 'zh-hant' }"
        @click="changeLanguage('zh-hant')"
      >
        <text class="language-name">繁體中文</text>
        <text class="check-icon" v-if="currentLanguage === 'zh-hant'">✓</text>
      </view>
      
      <view 
        class="language-item" 
        :class="{ active: currentLanguage === 'en' }"
        @click="changeLanguage('en')"
      >
        <text class="language-name">English</text>
        <text class="check-icon" v-if="currentLanguage === 'en'">✓</text>
      </view>
    </view>
    
    <view class="language-tip">
      {{ $t('profile.languageTip') }}
    </view>
  </view>
</template>

<script>
export default {
  data() {
    return {
      currentLanguage: this.$i18n.locale
    }
  },
  methods: {
    // 返回上一页
    navigateBack() {
      uni.navigateBack()
    },
    
    // 切换语言
    changeLanguage(lang) {
      if (this.currentLanguage === lang) {
        return
      }
      
      this.currentLanguage = lang
      this.$i18n.locale = lang
      uni.setStorageSync('locale', lang)
      
      uni.showToast({
        title: this.$t('common.success'),
        icon: 'success'
      })
    }
  }
}
</script>

<style lang="scss">
.language-container {
  min-height: 100vh;
  padding: 20px;
  background-color: var(--bg-color);
}

.language-header {
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

.language-title {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  font-size: 18px;
  font-weight: bold;
  color: var(--text-color);
}

.language-list-card {
  background-color: var(--card-bg-color);
  border-radius: 10px;
  overflow: hidden;
  margin-bottom: 15px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.language-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  border-bottom: 1px solid var(--border-color);
}

.language-item:last-child {
  border-bottom: none;
}

.language-item.active {
  background-color: rgba(0, 122, 255, 0.05);
}

.language-name {
  font-size: 16px;
  color: var(--text-color);
}

.check-icon {
  font-size: 18px;
  color: var(--primary-color);
  font-weight: bold;
}

.language-tip {
  font-size: 14px;
  color: var(--text-color-secondary);
  text-align: center;
  padding: 0 20px;
  margin-top: 20px;
}
</style> 