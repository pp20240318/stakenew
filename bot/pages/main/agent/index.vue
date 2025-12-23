<template>
  <view class="agent-container">
    <view class="agent-header">
      <view class="agent-title">{{ $t('agent.title') }}</view>
      <view class="invite-btn" @click="navigateToInvite">
        <text class="invite-icon">+</text>
        {{ $t('agent.invite') }}
      </view>
    </view>
    
    <!-- 代理等级卡片 -->
    <view class="agent-level-card">
      <view class="level-header">
        <view class="level-title">
          {{ $t(`agent.level${agentLevel}`) }}
        </view>
        
        <view class="level-badge" v-if="canUpgrade" @click="handleUpgrade">
          {{ $t('agent.upgrade') }}
        </view>
      </view>
      
      <view class="level-description">
        {{ $t(`agent.level${agentLevel}Desc`) }}
      </view>
      
      <!-- 升级进度条 -->
      <view v-if="agentLevel < 3" class="upgrade-progress-container">
        <view class="progress-header">
          <text class="progress-title">{{ $t('agent.levelUpProgress') }}</text>
          <text class="progress-value">{{ agentStats.invitedCount }}/{{ getRequiredInvites() }}</text>
        </view>
        
        <view class="progress-bar-container">
          <view 
            class="progress-bar"
            :style="{ width: `${upgradeProgress}%` }"
          ></view>
        </view>
        
        <view class="upgrade-requirement">
          {{ $t(`agent.level${agentLevel + 1}Requirement`) }}
        </view>
      </view>
    </view>
    
    <!-- 代理统计卡片 -->
    <view class="agent-stats-card">
      <view class="stats-item">
        <text class="stats-value">{{ agentStats.totalCount }}</text>
        <text class="stats-label">{{ $t('agent.totalAgents') }}</text>
      </view>
      
      <view class="stats-item">
        <text class="stats-value">{{ agentStats.directCount }}</text>
        <text class="stats-label">{{ $t('agent.directAgents') }}</text>
      </view>
      
      <view class="stats-item">
        <text class="stats-value">{{ agentStats.indirectCount }}</text>
        <text class="stats-label">{{ $t('agent.indirectAgents') }}</text>
      </view>
      
      <view class="stats-item">
        <text 
          class="stats-value" 
          :class="{ 
            'text-success': agentStats.totalProfit > 0, 
            'text-danger': agentStats.totalProfit < 0 
          }"
        >
          {{ agentStats.totalProfit > 0 ? '+' : '' }}{{ agentStats.totalProfit.toFixed(2) }}
        </text>
        <text class="stats-label">{{ $t('bot.totalProfit') }}</text>
      </view>
    </view>
    
    <!-- 代理列表 -->
    <view class="agent-list-card">
      <view class="list-header">
        <view class="tab-nav">
          <view 
            class="tab-item" 
            :class="{ active: activeTab === 'direct' }"
            @click="changeTab('direct')"
          >
            {{ $t('agent.directAgents') }}
          </view>
          <view 
            class="tab-item" 
            :class="{ active: activeTab === 'indirect' }"
            @click="changeTab('indirect')"
          >
            {{ $t('agent.indirectAgents') }}
          </view>
        </view>
        
        <view class="filter-container">
          <picker 
            :range="timeRangeOptions" 
            @change="handleTimeRangeChange" 
            :value="timeRangeIndex"
          >
            <view class="time-picker">
              <text>{{ timeRangeOptions[timeRangeIndex] }}</text>
              <text class="picker-arrow">▼</text>
            </view>
          </picker>
        </view>
      </view>
      
      <!-- 代理列表 -->
      <view class="agent-list">
        <view 
          class="agent-item" 
          v-for="(item, index) in currentAgentList" 
          :key="index"
          @click="viewAgentDetail(item)"
        >
          <view class="agent-info">
            <view class="agent-name">{{ item.username }}</view>
            <view class="agent-level">{{ $t(`agent.level${item.agentLevel}`) }}</view>
          </view>
          
          <view class="agent-profit">
            <text 
              :class="{ 
                'text-success': item.profit > 0, 
                'text-danger': item.profit < 0 
              }"
            >
              {{ item.profit > 0 ? '+' : '' }}{{ item.profit.toFixed(2) }} USDT
            </text>
          </view>
          
          <view class="agent-details">
            <text class="register-time">
              {{ formatDate(item.registerTime) }}
            </text>
            <text 
              class="agent-status" 
              :class="{ 'status-active': item.status === 'active' }"
            >
              {{ item.status === 'active' ? $t('bot.running') : $t('bot.stopped') }}
            </text>
          </view>
        </view>
        
        <!-- 无数据提示 -->
        <view class="no-data" v-if="currentAgentList.length === 0">
          {{ $t('common.noData') }}
        </view>
      </view>
      
      <!-- 导出按钮 -->
      <view class="export-btn-container">
        <button class="btn btn-outline btn-block" @click="exportData">
          {{ $t('agent.exportData') }}
        </button>
      </view>
    </view>
  </view>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'

export default {
  data() {
    return {
      activeTab: 'direct', // direct, indirect
      timeRangeIndex: 0,
      timeRangeOptions: ['全部', '本周', '本月'],
      timeRangeValues: ['all', 'week', 'month']
    }
  },
  computed: {
    ...mapGetters('user', ['agentLevel']),
    ...mapGetters('agent', [
      'agentStats',
      'directAgents',
      'indirectAgents',
      'upgradeProgress',
      'canUpgrade',
      'levelUpgradeCriteria'
    ]),
    
    // 当前显示的代理列表
    currentAgentList() {
      return this.activeTab === 'direct' ? this.directAgents : this.indirectAgents
    }
  },
  onLoad() {
    // 获取代理统计信息
    this.getAgentStats()
    
    // 获取直接代理列表
    this.getDirectAgents()
    
    // 获取间接代理列表
    this.getIndirectAgents()
  },
  methods: {
    ...mapActions('agent', [
      'getAgentStats',
      'getDirectAgents',
      'getIndirectAgents'
    ]),
    
    // 切换标签
    changeTab(tab) {
      this.activeTab = tab
    },
    
    // 处理时间范围变化
    handleTimeRangeChange(e) {
      this.timeRangeIndex = e.detail.value
      const timeRange = this.timeRangeValues[this.timeRangeIndex]
      
      // 根据选择的时间范围筛选代理
      if (this.activeTab === 'direct') {
        this.getDirectAgents({ timeRange: timeRange === 'all' ? '' : timeRange })
      } else {
        this.getIndirectAgents({ timeRange: timeRange === 'all' ? '' : timeRange })
      }
    },
    
    // 导航到邀请页面
    navigateToInvite() {
      uni.navigateTo({
        url: '/pages/details/invite'
      })
    },
    
    // 查看代理详情
    viewAgentDetail(agent) {
      uni.navigateTo({
        url: `/pages/details/agent-details?id=${agent.id}`
      })
    },
    
    // 处理升级
    handleUpgrade() {
      if (!this.canUpgrade) {
        uni.showToast({
          title: this.$t(`agent.level${this.agentLevel + 1}Requirement`),
          icon: 'none'
        })
        return
      }
      
      // 模拟升级
      uni.showLoading({
        title: this.$t('common.loading')
      })
      
      setTimeout(() => {
        uni.hideLoading()
        
        uni.showToast({
          title: this.$t('common.success'),
          icon: 'success'
        })
        
        // 刷新代理统计信息
        this.getAgentStats()
      }, 1000)
    },
    
    // 导出数据
    exportData() {
      uni.showToast({
        title: this.$t('agent.exportData') + ' ' + this.$t('common.success'),
        icon: 'success'
      })
    },
    
    // 获取所需邀请人数
    getRequiredInvites() {
      const nextLevel = this.agentLevel + 1
      if (nextLevel > 3) return 0
      
      const criteria = this.levelUpgradeCriteria[nextLevel]
      return criteria ? criteria.min : 0
    },
    
    // 格式化日期
    formatDate(timestamp) {
      const date = new Date(timestamp)
      const year = date.getFullYear()
      const month = (date.getMonth() + 1).toString().padStart(2, '0')
      const day = date.getDate().toString().padStart(2, '0')
      
      return `${year}-${month}-${day}`
    }
  }
}
</script>

<style lang="scss">
.agent-container {
  min-height: 100vh;
  padding: 20px;
  background-color: var(--bg-color);
}

.agent-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.agent-title {
  font-size: 20px;
  font-weight: bold;
  color: var(--text-color);
}

.invite-btn {
  display: flex;
  align-items: center;
  padding: 6px 12px;
  background-color: var(--primary-color);
  border-radius: 20px;
  color: white;
  font-size: 14px;
}

.invite-icon {
  margin-right: 5px;
  font-size: 14px;
}

.agent-level-card {
  background-color: var(--card-bg-color);
  border-radius: 10px;
  padding: 20px;
  margin-bottom: 15px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.level-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.level-title {
  font-size: 18px;
  font-weight: bold;
  color: var(--text-color);
}

.level-badge {
  font-size: 12px;
  padding: 4px 8px;
  background-color: var(--primary-color);
  color: white;
  border-radius: 12px;
}

.level-description {
  font-size: 14px;
  color: var(--text-color-secondary);
  margin-bottom: 15px;
}

.upgrade-progress-container {
  margin-top: 15px;
}

.progress-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 5px;
}

.progress-title {
  font-size: 14px;
  color: var(--text-color);
}

.progress-value {
  font-size: 14px;
  color: var(--primary-color);
  font-weight: bold;
}

.progress-bar-container {
  width: 100%;
  height: 6px;
  background-color: #eee;
  border-radius: 3px;
  overflow: hidden;
  margin-bottom: 5px;
}

.progress-bar {
  height: 100%;
  background-color: var(--primary-color);
  border-radius: 3px;
}

.upgrade-requirement {
  font-size: 12px;
  color: var(--text-color-secondary);
}

.agent-stats-card {
  display: flex;
  justify-content: space-between;
  background-color: var(--card-bg-color);
  border-radius: 10px;
  padding: 15px;
  margin-bottom: 15px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.stats-item {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.stats-value {
  font-size: 18px;
  font-weight: bold;
  color: var(--text-color);
  margin-bottom: 5px;
}

.stats-label {
  font-size: 12px;
  color: var(--text-color-secondary);
}

.agent-list-card {
  background-color: var(--card-bg-color);
  border-radius: 10px;
  padding: 20px;
  margin-bottom: 15px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.list-header {
  margin-bottom: 15px;
}

.filter-container {
  margin-top: 15px;
  display: flex;
  justify-content: flex-end;
}

.time-picker {
  display: flex;
  align-items: center;
  background-color: rgba(0, 0, 0, 0.03);
  padding: 6px 12px;
  border-radius: 16px;
  font-size: 12px;
  color: var(--text-color);
}

.picker-arrow {
  margin-left: 5px;
  font-size: 10px;
}

.agent-list {
  max-height: 400px;
  overflow-y: auto;
}

.agent-item {
  padding: 15px 0;
  border-bottom: 1px solid var(--border-color);
}

.agent-item:last-child {
  border-bottom: none;
}

.agent-info {
  display: flex;
  align-items: center;
  margin-bottom: 5px;
}

.agent-name {
  font-size: 16px;
  font-weight: bold;
  color: var(--text-color);
  margin-right: 10px;
}

.agent-level {
  font-size: 12px;
  background-color: rgba(0, 0, 0, 0.05);
  padding: 2px 6px;
  border-radius: 10px;
  color: var(--text-color-secondary);
}

.agent-profit {
  font-size: 14px;
  font-weight: bold;
  margin-bottom: 5px;
}

.agent-details {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.register-time {
  font-size: 12px;
  color: var(--text-color-secondary);
}

.agent-status {
  font-size: 12px;
  color: var(--text-color-secondary);
}

.status-active {
  color: var(--success-color);
}

.no-data {
  text-align: center;
  padding: 30px 0;
  color: var(--text-color-secondary);
  font-size: 14px;
}

.export-btn-container {
  margin-top: 20px;
}

.tab-nav {
  display: flex;
  background-color: rgba(0, 0, 0, 0.03);
  border-radius: 10px;
  overflow: hidden;
}

.tab-item {
  flex: 1;
  text-align: center;
  padding: 10px 0;
  font-size: 14px;
  color: var(--text-color-secondary);
  position: relative;
}

.tab-item.active {
  color: var(--primary-color);
  background-color: rgba(0, 122, 255, 0.1);
}

.tab-item.active::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 20px;
  height: 3px;
  background-color: var(--primary-color);
  border-radius: 3px;
}
</style> 