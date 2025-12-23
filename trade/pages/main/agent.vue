<template>
  <view class="agent-container">
    <!-- 代理统计信息 -->
    <view class="stats-card">
      <view class="stats-header">
        <text class="stats-title">{{ t('agent.statistics') }}</text>
        <view class="refresh-btn" @click="refreshStats">
          <uni-icons type="reload" size="18" color="#1890ff"></uni-icons>
        </view>
      </view>
      
      <view class="stats-content">
        <view class="stats-item">
          <view class="stats-value">{{ agentStats.totalCount || 0 }}</view>
          <view class="stats-label">{{ t('agent.totalAgents') }}</view>
        </view>
        
        <view class="stats-item">
          <view class="stats-value">{{ agentStats.directCount || 0 }}</view>
          <view class="stats-label">{{ t('agent.directAgents') }}</view>
        </view>
        
        <view class="stats-item">
          <view class="stats-value">{{ agentStats.indirectCount || 0 }}</view>
          <view class="stats-label">{{ t('agent.indirectAgents') }}</view>
        </view>
        
        <view class="stats-item">
          <view class="stats-value">${{ formatCurrency(agentStats.totalProfit) }}</view>
          <view class="stats-label">{{ t('agent.totalProfit') }}</view>
        </view>
      </view>
    </view>
    
    <!-- 代理等级和升级信息 -->
    <view class="level-card">
      <view class="level-header">
        <text class="level-title">{{ t('agent.level') }}</text>
        <text class="current-level">{{ t('agent.levelLabel', { level: agentLevel }) }}</text>
      </view>
      
      <view class="level-content">
        <view class="level-info">
          <text>{{ t('agent.invitedCount') }}: {{ agentStats.invitedCount || 0 }}</text>
          <text v-if="canUpgrade" class="upgrade-hint">{{ t('agent.canUpgrade') }}</text>
        </view>
        
        <view class="progress-container" v-if="nextLevelCriteria">
          <view class="progress-bar">
            <view class="progress-fill" :style="{ width: `${upgradeProgress}%` }"></view>
          </view>
          <view class="progress-info">
            <text>{{ agentStats.invitedCount || 0 }}/{{ nextLevelCriteria.invitedCount }}</text>
            <text>{{ upgradeProgress }}%</text>
          </view>
        </view>
        
        <view class="level-description" v-if="nextLevelCriteria">
          <text>{{ t('agent.nextLevelDesc', { level: nextLevel, count: nextLevelCriteria.invitedCount }) }}</text>
        </view>
        
        <view class="upgrade-button-container" v-if="canUpgrade">
          <button class="upgrade-button" @click="upgradeLevel">{{ t('agent.upgradeNow') }}</button>
        </view>
      </view>
    </view>
    
    <!-- 邀请码 -->
    <view class="invite-card">
      <view class="invite-content">
        <text class="invite-label">{{ t('agent.yourInviteCode') }}</text>
        <view class="invite-code-container">
          <text class="invite-code">{{ inviteCode }}</text>
          <view class="copy-btn" @click="copyInviteCode">
            <uni-icons type="paperclip" size="18" color="#1890ff"></uni-icons>
          </view>
        </view>
        <button class="share-button" @click="shareInviteCode">
          <uni-icons type="redo" size="16" color="#fff"></uni-icons>
          {{ t('agent.shareCode') }}
        </button>
      </view>
    </view>
    
    <!-- 代理列表 -->
    <view class="agents-card">
      <view class="tabs">
        <view 
          class="tab-item" 
          :class="{ active: currentTab === 'direct' }" 
          @click="changeTab('direct')"
        >
          {{ t('agent.directAgents') }}
        </view>
        <view 
          class="tab-item" 
          :class="{ active: currentTab === 'indirect' }" 
          @click="changeTab('indirect')"
        >
          {{ t('agent.indirectAgents') }}
        </view>
      </view>
      
      <view class="filter-bar">
        <view 
          class="filter-item" 
          :class="{ active: currentFilter === 'all' }" 
          @click="changeFilter('all')"
        >
          {{ t('agent.allTime') }}
        </view>
        <view 
          class="filter-item" 
          :class="{ active: currentFilter === 'week' }" 
          @click="changeFilter('week')"
        >
          {{ t('agent.thisWeek') }}
        </view>
        <view 
          class="filter-item" 
          :class="{ active: currentFilter === 'month' }" 
          @click="changeFilter('month')"
        >
          {{ t('agent.thisMonth') }}
        </view>
      </view>
      
      <view class="agents-list">
        <view class="empty-state" v-if="displayedAgents.length === 0">
          <uni-icons type="personadd" size="40" color="#ccc"></uni-icons>
          <text>{{ t('agent.noAgentsYet') }}</text>
        </view>
        
        <view 
          class="agent-item" 
          v-for="(agent, index) in displayedAgents" 
          :key="index"
          @click="navigateToAgentDetail(agent.id)"
        >
          <view class="agent-avatar">
            <text class="avatar-text">{{ agent.username.charAt(0).toUpperCase() }}</text>
          </view>
          
          <view class="agent-info">
            <view class="agent-name">{{ agent.username }}</view>
            <view class="agent-date">{{ formatDate(agent.registerTime) }}</view>
          </view>
          
          <view class="agent-stats">
            <view class="agent-level">{{ t('agent.levelLabel', { level: agent.level }) }}</view>
            <view class="agent-profit">${{ formatCurrency(agent.profit) }}</view>
          </view>
        </view>
      </view>
      
      <view class="load-more" v-if="displayedAgents.length > 0">
        <text>{{ t('common.noMoreData') }}</text>
      </view>
    </view>
  </view>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAgentStore } from '@/stores/agent'
import { useUserStore } from '@/stores/user'
import { useI18n } from 'vue-i18n'

const agentStore = useAgentStore()
const userStore = useUserStore()
const { t } = useI18n()

// 当前选中的标签页
const currentTab = ref('direct')

// 当前选中的过滤器
const currentFilter = ref('all')

// 获取代理统计信息
const agentStats = computed(() => agentStore.getAgentStats)

// 获取用户代理等级
const agentLevel = computed(() => userStore.getAgentLevel)

// 获取邀请码
const inviteCode = computed(() => userStore.getInviteCode)

// 是否可以升级
const canUpgrade = computed(() => agentStore.getCanUpgrade)

// 升级进度
const upgradeProgress = computed(() => agentStore.getUpgradeProgress)

// 获取升级标准
const levelUpgradeCriteria = computed(() => agentStore.getLevelUpgradeCriteria)

// 计算下一个等级
const nextLevel = computed(() => agentLevel.value + 1)

// 获取下一个等级的升级标准
const nextLevelCriteria = computed(() => {
  if (agentLevel.value >= 3) return null // 最高等级
  return levelUpgradeCriteria.value[`level${nextLevel.value}`]
})

// 计算当前应该显示的代理列表
const displayedAgents = computed(() => {
  if (currentTab.value === 'direct') {
    return agentStore.getDirectAgents
  } else {
    return agentStore.getIndirectAgents
  }
})

// 组件挂载时加载数据
onMounted(async () => {
  await refreshData()
})

// 刷新数据
async function refreshData() {
  try {
    // 获取代理统计数据
    await agentStore.getAgentStats()
    
    // 根据当前标签页和过滤器加载代理列表
    loadAgentList()
  } catch (error) {
    uni.showToast({
      title: error.message || t('common.error'),
      icon: 'none'
    })
  }
}

// 刷新统计信息
async function refreshStats() {
  try {
    uni.showLoading({
      title: t('common.loading')
    })
    
    await agentStore.getAgentStats()
    
    uni.hideLoading()
    uni.showToast({
      title: t('common.refreshSuccess'),
      icon: 'success'
    })
  } catch (error) {
    uni.hideLoading()
    uni.showToast({
      title: error.message || t('common.error'),
      icon: 'none'
    })
  }
}

// 切换标签页
function changeTab(tab) {
  if (currentTab.value === tab) return
  currentTab.value = tab
  loadAgentList()
}

// 切换过滤器
function changeFilter(filter) {
  if (currentFilter.value === filter) return
  currentFilter.value = filter
  loadAgentList()
}

// 加载代理列表
async function loadAgentList() {
  try {
    let params = {}
    
    // 根据过滤器设置日期范围
    if (currentFilter.value !== 'all') {
      params.timeRange = currentFilter.value
    }
    
    if (currentTab.value === 'direct') {
      await agentStore.getDirectAgents(params)
    } else {
      await agentStore.getIndirectAgents(params)
    }
  } catch (error) {
    uni.showToast({
      title: error.message || t('common.error'),
      icon: 'none'
    })
  }
}

// 复制邀请码
function copyInviteCode() {
  if (!inviteCode.value) {
    uni.showToast({
      title: t('agent.noInviteCode'),
      icon: 'none'
    })
    return
  }
  
  uni.setClipboardData({
    data: inviteCode.value,
    success: () => {
      uni.showToast({
        title: t('agent.codeCopied'),
        icon: 'success'
      })
    }
  })
}

// 分享邀请码
function shareInviteCode() {
  if (!inviteCode.value) {
    uni.showToast({
      title: t('agent.noInviteCode'),
      icon: 'none'
    })
    return
  }
  
  // 使用uni-app的分享功能
  uni.share({
    provider: 'weixin',
    scene: 'WXSceneSession',
    type: 0,
    title: t('agent.shareTitle'),
    summary: t('agent.shareContent', { code: inviteCode.value }),
    success: (res) => {
      console.log('分享成功')
    },
    fail: (err) => {
      console.log('分享失败', err)
    }
  })
}

// 升级代理等级
function upgradeLevel() {
  if (!canUpgrade.value) {
    uni.showToast({
      title: t('agent.cannotUpgradeYet'),
      icon: 'none'
    })
    return
  }
  
  uni.showModal({
    title: t('agent.confirmUpgrade'),
    content: t('agent.upgradeConfirmContent', { level: nextLevel.value }),
    success: (res) => {
      if (res.confirm) {
        // 模拟升级代理等级的API调用
        setTimeout(() => {
          // 这里只是为了演示，实际中应该调用API并处理响应
          userStore.setUserInfo({
            ...userStore.getUserInfo,
            agentLevel: nextLevel.value
          })
          
          uni.showToast({
            title: t('agent.upgradeSuccess'),
            icon: 'success'
          })
          
          // 刷新代理统计数据
          agentStore.getAgentStats()
        }, 500)
      }
    }
  })
}

// 导航到代理详情
function navigateToAgentDetail(agentId) {
  uni.navigateTo({
    url: `/pages/detail/agent?id=${agentId}`
  })
}

// 格式化日期
function formatDate(timestamp) {
  if (!timestamp) return '-'
  const date = new Date(timestamp)
  return `${date.getFullYear()}-${padZero(date.getMonth() + 1)}-${padZero(date.getDate())}`
}

// 格式化货币
function formatCurrency(value) {
  if (value === undefined || value === null) return '0.00'
  return parseFloat(value).toFixed(2)
}

// 补零
function padZero(num) {
  return num < 10 ? `0${num}` : num
}
</script>

<style lang="scss">
.agent-container {
  padding: 30rpx;
  background-color: #f8f9fa;
  min-height: 100vh;
}

.stats-card, .level-card, .invite-card, .agents-card {
  background-color: #fff;
  border-radius: 12rpx;
  margin-bottom: 30rpx;
  overflow: hidden;
}

.stats-header, .level-header {
  padding: 20rpx 30rpx;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #f0f0f0;
}

.stats-title, .level-title {
  font-size: 30rpx;
  font-weight: bold;
  color: #333;
}

.current-level {
  font-size: 28rpx;
  color: #1890ff;
  font-weight: 500;
}

.refresh-btn {
  padding: 10rpx;
}

.stats-content {
  display: flex;
  padding: 30rpx;
  flex-wrap: wrap;
}

.stats-item {
  width: 50%;
  text-align: center;
  margin-bottom: 20rpx;
  
  .stats-value {
    font-size: 36rpx;
    font-weight: bold;
    color: #1890ff;
    margin-bottom: 10rpx;
  }
  
  .stats-label {
    font-size: 24rpx;
    color: #999;
  }
}

.level-content {
  padding: 30rpx;
}

.level-info {
  display: flex;
  justify-content: space-between;
  margin-bottom: 20rpx;
  font-size: 28rpx;
  color: #666;
  
  .upgrade-hint {
    color: #52c41a;
  }
}

.progress-container {
  margin-bottom: 20rpx;
  
  .progress-bar {
    height: 10rpx;
    background-color: #f5f5f5;
    border-radius: 5rpx;
    overflow: hidden;
    margin-bottom: 10rpx;
    
    .progress-fill {
      height: 100%;
      background: linear-gradient(to right, #1890ff, #52c41a);
      border-radius: 5rpx;
    }
  }
  
  .progress-info {
    display: flex;
    justify-content: space-between;
    font-size: 24rpx;
    color: #999;
  }
}

.level-description {
  font-size: 24rpx;
  color: #999;
  margin-bottom: 20rpx;
}

.upgrade-button-container {
  text-align: center;
  
  .upgrade-button {
    background: linear-gradient(to right, #1890ff, #52c41a);
    color: #fff;
    border-radius: 30rpx;
    padding: 0 40rpx;
    height: 80rpx;
    line-height: 80rpx;
    display: inline-block;
    font-size: 28rpx;
  }
}

.invite-card {
  padding: 30rpx;
  
  .invite-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    
    .invite-label {
      font-size: 28rpx;
      color: #666;
      margin-bottom: 20rpx;
    }
    
    .invite-code-container {
      display: flex;
      align-items: center;
      margin-bottom: 30rpx;
      
      .invite-code {
        font-size: 42rpx;
        font-weight: bold;
        color: #1890ff;
        margin-right: 20rpx;
        letter-spacing: 5rpx;
      }
      
      .copy-btn {
        padding: 10rpx;
      }
    }
    
    .share-button {
      background: linear-gradient(to right, #1890ff, #36cfc9);
      color: #fff;
      border-radius: 30rpx;
      padding: 0 40rpx;
      height: 80rpx;
      line-height: 80rpx;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28rpx;
      
      uni-icons {
        margin-right: 10rpx;
      }
    }
  }
}

.tabs {
  display: flex;
  border-bottom: 1px solid #f0f0f0;
  
  .tab-item {
    flex: 1;
    text-align: center;
    padding: 20rpx 0;
    font-size: 28rpx;
    color: #666;
    position: relative;
    
    &.active {
      color: #1890ff;
      font-weight: bold;
      
      &::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 40%;
        height: 4rpx;
        background-color: #1890ff;
      }
    }
  }
}

.filter-bar {
  display: flex;
  padding: 20rpx 30rpx;
  border-bottom: 1px solid #f0f0f0;
  
  .filter-item {
    margin-right: 20rpx;
    padding: 6rpx 20rpx;
    font-size: 24rpx;
    border-radius: 20rpx;
    color: #666;
    
    &.active {
      background-color: #e6f7ff;
      color: #1890ff;
    }
  }
}

.agents-list {
  padding: 20rpx 30rpx;
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

.agent-item {
  display: flex;
  padding: 20rpx 0;
  border-bottom: 1px solid #f5f5f5;
  
  &:last-child {
    border-bottom: none;
  }
  
  .agent-avatar {
    width: 80rpx;
    height: 80rpx;
    border-radius: 50%;
    background-color: #1890ff;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-right: 20rpx;
    
    .avatar-text {
      color: #fff;
      font-size: 36rpx;
      font-weight: bold;
    }
  }
  
  .agent-info {
    flex: 1;
    
    .agent-name {
      font-size: 28rpx;
      font-weight: bold;
      color: #333;
      margin-bottom: 10rpx;
    }
    
    .agent-date {
      font-size: 24rpx;
      color: #999;
    }
  }
  
  .agent-stats {
    text-align: right;
    
    .agent-level {
      font-size: 24rpx;
      color: #1890ff;
      margin-bottom: 10rpx;
    }
    
    .agent-profit {
      font-size: 30rpx;
      font-weight: bold;
      color: #52c41a;
    }
  }
}

.load-more {
  text-align: center;
  padding: 20rpx 0;
  color: #999;
  font-size: 24rpx;
}
</style> 