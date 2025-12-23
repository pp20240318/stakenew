<template>
  <view class="password-container">
    <view class="form-card">
      <view class="form-group">
        <text class="form-label">{{ t('password.current') }}</text>
        <input 
          class="form-input" 
          type="password" 
          v-model="form.currentPassword" 
          :placeholder="t('password.enterCurrent')" 
          password="true"
        />
      </view>
      
      <view class="form-group">
        <text class="form-label">{{ t('password.new') }}</text>
        <input 
          class="form-input" 
          type="password" 
          v-model="form.newPassword" 
          :placeholder="t('password.enterNew')" 
          password="true"
        />
      </view>
      
      <view class="form-group">
        <text class="form-label">{{ t('password.confirm') }}</text>
        <input 
          class="form-input" 
          type="password" 
          v-model="form.confirmPassword" 
          :placeholder="t('password.enterConfirm')" 
          password="true"
        />
      </view>
      
      <view class="form-tips">
        <text>{{ t('password.tips') }}</text>
      </view>
    </view>
    
    <view class="action-buttons">
      <button class="submit-button" @click="changePassword">
        {{ t('password.change') }}
      </button>
    </view>
  </view>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useI18n } from 'vue-i18n'
import { useUserStore } from '@/stores/user'

const { t } = useI18n()
const userStore = useUserStore()

// 表单数据
const form = reactive({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
})

// 提交中状态
const isSubmitting = ref(false)

// 修改密码
async function changePassword() {
  // 验证表单
  if (!form.currentPassword) {
    showError(t('password.errorCurrentEmpty'))
    return
  }
  
  if (!form.newPassword) {
    showError(t('password.errorNewEmpty'))
    return
  }
  
  if (form.newPassword.length < 6) {
    showError(t('password.errorNewTooShort'))
    return
  }
  
  if (form.newPassword !== form.confirmPassword) {
    showError(t('password.errorConfirmMismatch'))
    return
  }
  
  try {
    isSubmitting.value = true
    
    // 这里只是模拟，实际应该调用API
    await simulatePasswordChange()
    
    // 显示成功消息
    uni.showToast({
      title: t('password.changeSuccess'),
      icon: 'success'
    })
    
    // 重置表单
    form.currentPassword = ''
    form.newPassword = ''
    form.confirmPassword = ''
    
    // 延迟返回上一页
    setTimeout(() => {
      uni.navigateBack()
    }, 1500)
  } catch (error) {
    showError(error.message || t('common.error'))
  } finally {
    isSubmitting.value = false
  }
}

// 显示错误信息
function showError(message) {
  uni.showToast({
    title: message,
    icon: 'none'
  })
}

// 模拟密码修改API调用
function simulatePasswordChange() {
  return new Promise((resolve, reject) => {
    setTimeout(() => {
      // 模拟校验当前密码
      if (form.currentPassword !== '123456') {
        reject(new Error(t('password.errorCurrentIncorrect')))
        return
      }
      
      resolve(true)
    }, 1000)
  })
}
</script>

<style lang="scss">
.password-container {
  padding: 30rpx;
  background-color: #f8f9fa;
  min-height: 100vh;
}

.form-card {
  background-color: #fff;
  border-radius: 12rpx;
  padding: 30rpx;
  margin-bottom: 30rpx;
}

.form-group {
  margin-bottom: 30rpx;
  
  &:last-child {
    margin-bottom: 0;
  }
  
  .form-label {
    display: block;
    font-size: 28rpx;
    color: #333;
    margin-bottom: 15rpx;
  }
  
  .form-input {
    width: 100%;
    height: 90rpx;
    background-color: #f8f8f8;
    border-radius: 8rpx;
    padding: 0 20rpx;
    font-size: 28rpx;
    color: #333;
  }
}

.form-tips {
  margin-top: 30rpx;
  padding: 20rpx;
  background-color: #f6ffed;
  border-radius: 8rpx;
  border: 1px solid #b7eb8f;
  
  text {
    font-size: 24rpx;
    color: #52c41a;
    line-height: 1.5;
  }
}

.action-buttons {
  .submit-button {
    width: 100%;
    height: 90rpx;
    line-height: 90rpx;
    background: linear-gradient(to right, #1890ff, #36cfc9);
    color: #fff;
    font-size: 32rpx;
    border-radius: 45rpx;
  }
}
</style> 