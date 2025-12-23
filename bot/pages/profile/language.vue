<template>
  <view class="language-container">
    <view class="language-list">
      <view 
        class="language-item" 
        :class="{ active: currentLocale === 'en' }"
        @click="changeLanguage('en')"
      >
        <view class="language-content">
          <text class="language-name">English</text>
          <text class="language-native">English</text>
        </view>
        <view class="check-icon" v-if="currentLocale === 'en'">
          <uni-icons type="checkmarkempty" size="20" color="#1890ff"></uni-icons>
        </view>
      </view>
      
      <view 
        class="language-item" 
        :class="{ active: currentLocale === 'zh-tw' }"
        @click="changeLanguage('zh-tw')"
      >
        <view class="language-content">
          <text class="language-name">Chinese (Traditional)</text>
          <text class="language-native">繁體中文</text>
        </view>
        <view class="check-icon" v-if="currentLocale === 'zh-tw'">
          <uni-icons type="checkmarkempty" size="20" color="#1890ff"></uni-icons>
        </view>
      </view>
    </view>
    
    <view class="note-container">
      <text class="note-text">{{ t('language.changeNote') }}</text>
    </view>
  </view>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const { t, locale } = useI18n()

// 获取当前语言
const currentLocale = computed(() => locale.value)

// 切换语言
function changeLanguage(lang) {
  if (currentLocale.value === lang) return
  
  locale.value = lang
  uni.setStorageSync('locale', lang)
  
  // 显示切换成功提示
  uni.showToast({
    title: t('language.changeSuccess'),
    icon: 'success'
  })
}
</script>

<style lang="scss">
.language-container {
  padding: 30rpx;
  background-color: #f8f9fa;
  min-height: 100vh;
}

.language-list {
  background-color: #fff;
  border-radius: 12rpx;
  overflow: hidden;
}

.language-item {
  display: flex;
  justify-content: space-between;
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
  
  &.active {
    background-color: #f0f9ff;
  }
  
  .language-content {
    display: flex;
    flex-direction: column;
    
    .language-name {
      font-size: 28rpx;
      color: #333;
      margin-bottom: 8rpx;
    }
    
    .language-native {
      font-size: 24rpx;
      color: #999;
    }
  }
  
  .check-icon {
    width: 40rpx;
    height: 40rpx;
    display: flex;
    justify-content: center;
    align-items: center;
  }
}

.note-container {
  margin-top: 30rpx;
  padding: 20rpx;
  
  .note-text {
    font-size: 24rpx;
    color: #999;
    line-height: 1.5;
  }
}
</style> 