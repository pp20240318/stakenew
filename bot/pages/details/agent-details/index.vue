<template>
  <view class="agent-details-container">
    <view class="agent-details-header">
      <view class="back-btn" @click="navigateBack">
        <text class="back-icon">&#8592;</text>
        <text>{{ $t('common.back') }}</text>
      </view>
      <view class="agent-details-title">{{ $t('agent.agentDetail') }}</view>
    </view>
    
    <view class="agent-details-card" v-if="agentDetail">
      <view class="agent-basic-info">
        <view class="agent-name">{{ agentDetail.username }}</view>
        <view class="agent-level">{{ $t(`agent.level${agentDetail.agentLevel}`) }}</view>
      </view>
      
      <view class="divider"></view>
      
      <view class="agent-info-list">
        <view class="agent-info-item">
          <text class="info-label">{{ $t('agent.registerTime') }}</text>
          <text class="info-value">{{ formatDate(agentDetail.registerTime) }}</text>
        </view>
        
        <view class="agent-info-item">
          <text class="info-label">{{ $t('agent.status') }}</text>
          <text 
            class="info-value status-tag" 
            :class="{ 'status-active': agentDetail.status === 'active' }"
          >
            {{ agentDetail.status === 'active' ? $t('bot.running') : $t('bot.stopped') }}
          </text>
        </view>
        
        <view class="agent-info-item">
          <text class="info-label">{{ $t('bot.totalProfit') }}</text>
          <text 
            class="info-value" 
            :class="{ 
              'text-success': agentDetail.profit > 0, 
              'text-danger': agentDetail.profit < 0 
            }"
          >
            {{ agentDetail.profit > 0 ? '+' : '' }}{{ agentDetail.profit.toFixed(2) }} USDT
          </text>
        </view>
        
        <view class="agent-info-item" v-if="agentDetail.directCount !== undefined">
          <text class="info-label">{{ $t('agent.directAgents') }}</text>
          <text class="info-value">{{ agentDetail.directCount }}</text>
        </view>
        
        <view class="agent-info-item" v-if="agentDetail.indirectCount !== undefined">
          <text class="info-label">{{ $t('agent.indirectAgents') }}</text>
          <text class="info-value">{{ agentDetail.indirectCount }}</text>
        </view>
        
        <view class="agent-info-item" v-if="agentDetail.parentId !== undefined">
          <text class="info-label">{{ $t('agent.parentAgent') }}</text>
          <text class="info-value">ID: {{ agentDetail.parentId }}</text>
        </view>
      </view>
    </view>
    
    <view class="agent-performance-card">
      <view class="performance-title">{{ $t('agent.performance') }}</view>
      
      <view class="performance-tabs">
        <view 
          class="tab-item" 
          :class="{ active: activeTab === 'week' }"
          @click="changeTab('week')"
        >
          {{ $t('bot.week') }}
        </view>
        <view 
          class="tab-item" 
          :class="{ active: activeTab === 'month' }"
          @click="changeTab('month')"
        >
          {{ $t('bot.month') }}
        </view>
        <view 
          class="tab-item" 
          :class="{ active: activeTab === 'all' }"
          @click="changeTab('all')"
        >
          {{ $t('agent.all') }}
        </view>
      </view>
      
      <view class="chart-placeholder">
        <!-- 这里应该放置业绩图表，使用echarts等图表库 -->
        <view class="chart-mock">
          <text>{{ $t('agent.performanceChart') }}</text>
        </view>
      </view>
      
      <view class="performance-summary">
        <view class="summary-item">
          <text class="summary-label">{{ $t('agent.tradeCount') }}</text>
          <text class="summary-value">{{ performanceData.tradeCount }}</text>
        </view>
        
        <view class="summary-item">
          <text class="summary-label">{{ $t('agent.profitRate') }}</text>
          <text 
            class="summary-value" 
            :class="{ 
              'text-success': performanceData.profitRate > 0, 
              'text-danger': performanceData.profitRate < 0 
            }"
          >
            {{ performanceData.profitRate > 0 ? '+' : '' }}{{ performanceData.profitRate }}%
          </text>
        </view>
        
        <view class="summary-item">
          <text class="summary-label">{{ $t('agent.activeTime') }}</text>
          <text class="summary-value">{{ performanceData.activeTime }}h</text>
        </view>
      </view>
    </view>
    
    <view class="downline-agents-card">
      <view class="card-header">
        <text class="card-title">{{ $t('agent.downlineEarnings') || '下级代理收益' }}</text>
      </view>
      
      <view class="downline-tabs">
        <view 
          class="tab-item" 
          :class="{ active: downlineTab === 'direct' }"
          @click="changeDownlineTab('direct')"
        >
          {{ $t('agent.directAgents') || '直接下级' }}
        </view>
        <view 
          class="tab-item" 
          :class="{ active: downlineTab === 'indirect' }"
          @click="changeDownlineTab('indirect')"
        >
          {{ $t('agent.indirectAgents') || '间接下级' }}
        </view>
      </view>
      
      <view class="empty-state" v-if="!downlineAgents || downlineAgents.length === 0">
        <text class="empty-text">{{ $t('agent.noDownline') || '暂无下级代理数据' }}</text>
      </view>
      
      <view class="downline-list" v-else>
        <view class="downline-item" v-for="agent in filteredDownlineAgents" :key="agent.id" @click="viewAgentDetail(agent.id)">
          <view class="agent-main-info">
            <text class="agent-username">{{ agent.username }}</text>
            <text class="agent-level">{{ $t(`agent.level${agent.agentLevel}`) || `等级 ${agent.agentLevel}` }}</text>
          </view>
          
          <view class="agent-earnings">
            <view class="earnings-item">
              <text class="earnings-label">{{ $t('agent.tradingVolume') || '交易量' }}</text>
              <text class="earnings-value">{{ agent.tradingVolume }} USDT</text>
            </view>
            
            <view class="earnings-item">
              <text class="earnings-label">{{ $t('agent.commission') || '佣金' }}</text>
              <text class="earnings-value" :class="{'text-success': agent.commission > 0}">
                {{ agent.commission }} USDT
              </text>
            </view>
            
            <view class="earnings-item">
              <text class="earnings-label">{{ $t('agent.profitRate') || '收益率' }}</text>
              <text class="earnings-value" :class="{
                'text-success': agent.profitRate > 0,
                'text-danger': agent.profitRate < 0
              }">
                {{ agent.profitRate > 0 ? '+' : '' }}{{ agent.profitRate }}%
              </text>
            </view>
          </view>
          
          <view class="view-details">
            <text class="details-text">{{ $t('agent.viewDetails') || '查看详情' }}</text>
            <text class="arrow-icon">→</text>
          </view>
        </view>
      </view>
    </view>
    
    <view class="agent-actions">
      <button class="btn btn-outline btn-block" @click="exportAgentData">
        {{ $t('agent.exportData') }}
      </button>
    </view>
  </view>
</template>

<script>
import { mapActions } from 'vuex'

export default {
  data() {
    return {
      agentId: null,
      agentDetail: null,
      activeTab: 'week',
      downlineTab: 'direct',
      performanceData: {
        tradeCount: 0,
        profitRate: 0,
        activeTime: 0
      },
      downlineAgents: []
    }
  },
  computed: {
    filteredDownlineAgents() {
      if (!this.downlineAgents || this.downlineAgents.length === 0) {
        return [];
      }
      
      return this.downlineAgents.filter(agent => {
        return this.downlineTab === 'direct' ? agent.isDirectDownline : !agent.isDirectDownline;
      });
    }
  },
  onLoad(options) {
    if (options.id) {
      this.agentId = options.id
      this.getAgentDetail()
      this.getDownlineAgents()
    }
  },
  methods: {
    ...mapActions('agent', ['getAgentDetail']),
    
    // 返回上一页
    navigateBack() {
      uni.navigateBack()
    },
    
    // 获取代理详情
    async getAgentDetail() {
      try {
        const agentDetail = await this.getAgentDetail(this.agentId)
        this.agentDetail = agentDetail
        this.updatePerformanceData()
        this.getDownlineAgents()
      } catch (error) {
        // 模拟数据
        setTimeout(() => {
          this.agentDetail = {
            id: this.agentId,
            username: 'Agent' + this.agentId,
            agentLevel: Math.floor(Math.random() * 3) + 1,
            registerTime: new Date().getTime() - Math.random() * 30 * 24 * 60 * 60 * 1000,
            status: Math.random() > 0.3 ? 'active' : 'inactive',
            profit: (Math.random() * 1000 - 300).toFixed(2),
            directCount: Math.floor(Math.random() * 10),
            indirectCount: Math.floor(Math.random() * 20)
          }
          this.updatePerformanceData()
          this.getDownlineAgents()
        }, 500)
      }
    },
    
    // 更新业绩数据
    updatePerformanceData() {
      // 根据选择的时间范围生成模拟数据
      if (this.activeTab === 'week') {
        this.performanceData = {
          tradeCount: Math.floor(Math.random() * 50) + 10,
          profitRate: (Math.random() * 20 - 5).toFixed(2),
          activeTime: Math.floor(Math.random() * 40) + 10
        }
      } else if (this.activeTab === 'month') {
        this.performanceData = {
          tradeCount: Math.floor(Math.random() * 200) + 50,
          profitRate: (Math.random() * 30 - 10).toFixed(2),
          activeTime: Math.floor(Math.random() * 100) + 50
        }
      } else {
        this.performanceData = {
          tradeCount: Math.floor(Math.random() * 500) + 100,
          profitRate: (Math.random() * 50 - 15).toFixed(2),
          activeTime: Math.floor(Math.random() * 300) + 100
        }
      }
    },
    
    // 切换标签
    changeTab(tab) {
      this.activeTab = tab
      this.updatePerformanceData()
    },
    
    // 格式化日期
    formatDate(timestamp) {
      const date = new Date(timestamp)
      const year = date.getFullYear()
      const month = (date.getMonth() + 1).toString().padStart(2, '0')
      const day = date.getDate().toString().padStart(2, '0')
      
      return `${year}-${month}-${day}`
    },
    
    // 导出代理数据
    exportAgentData() {
      uni.showToast({
        title: this.$t('agent.exportSuccess'),
        icon: 'success'
      })
    },
    
    // 获取下级代理数据
    async getDownlineAgents() {
      try {
        // 这里应该是真实的API调用
        // const response = await this.$api.agent.getDownlineAgents(this.agentId);
        // this.downlineAgents = response.data;
        
        // 模拟数据
        setTimeout(() => {
          this.downlineAgents = this.generateMockDownlineAgents();
        }, 800);
      } catch (error) {
        console.error('Failed to get downline agents:', error);
        uni.showToast({
          title: this.$t('common.loadFailed') || '加载失败',
          icon: 'none'
        });
      }
    },
    
    // 生成模拟下级代理数据
    generateMockDownlineAgents() {
      const count = Math.floor(Math.random() * 10) + 5;
      const agents = [];
      
      for (let i = 0; i < count; i++) {
        agents.push({
          id: Math.floor(Math.random() * 10000),
          username: 'Agent' + Math.floor(Math.random() * 10000),
          agentLevel: Math.floor(Math.random() * 3) + 1,
          isDirectDownline: Math.random() > 0.5,
          tradingVolume: (Math.random() * 10000 + 1000).toFixed(2),
          commission: (Math.random() * 500 + 50).toFixed(2),
          profitRate: (Math.random() * 30 - 5).toFixed(2)
        });
      }
      
      return agents;
    },
    
    // 切换下级代理标签
    changeDownlineTab(tab) {
      this.downlineTab = tab;
    },
    
    // 查看特定代理详情
    viewAgentDetail(agentId) {
      uni.navigateTo({
        url: `/pages/details/agent-details/index?id=${agentId}`
      });
    }
  }
}
</script>

<style lang="scss">
.agent-details-container {
  min-height: 100vh;
  padding: 20px;
  background-color: var(--bg-color);
}

.agent-details-header {
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

.agent-details-title {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  font-size: 18px;
  font-weight: bold;
  color: var(--text-color);
}

.agent-details-card {
  background-color: var(--card-bg-color);
  border-radius: 10px;
  padding: 20px;
  margin-bottom: 15px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.agent-basic-info {
  display: flex;
  align-items: center;
  margin-bottom: 15px;
}

.agent-name {
  font-size: 20px;
  font-weight: bold;
  color: var(--text-color);
  margin-right: 10px;
}

.agent-level {
  font-size: 12px;
  padding: 2px 8px;
  background-color: rgba(0, 122, 255, 0.1);
  color: var(--primary-color);
  border-radius: 10px;
}

.agent-info-list {
  margin-top: 15px;
}

.agent-info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 0;
  border-bottom: 1px solid var(--border-color);
}

.agent-info-item:last-child {
  border-bottom: none;
}

.info-label {
  font-size: 14px;
  color: var(--text-color-secondary);
}

.info-value {
  font-size: 14px;
  font-weight: bold;
  color: var(--text-color);
}

.status-tag {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
}

.status-active {
  color: var(--success-color);
}

.agent-performance-card {
  background-color: var(--card-bg-color);
  border-radius: 10px;
  padding: 20px;
  margin-bottom: 15px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.performance-title {
  font-size: 16px;
  font-weight: bold;
  color: var(--text-color);
  margin-bottom: 15px;
}

.performance-tabs {
  display: flex;
  background-color: rgba(0, 0, 0, 0.03);
  border-radius: 8px;
  overflow: hidden;
  margin-bottom: 15px;
}

.tab-item {
  flex: 1;
  text-align: center;
  padding: 8px 0;
  font-size: 14px;
  color: var(--text-color-secondary);
}

.tab-item.active {
  background-color: var(--primary-color);
  color: white;
}

.chart-placeholder {
  height: 200px;
  margin-bottom: 15px;
}

.chart-mock {
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.03);
  display: flex;
  justify-content: center;
  align-items: center;
  border-radius: 8px;
}

.performance-summary {
  display: flex;
  justify-content: space-between;
}

.summary-item {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.summary-label {
  font-size: 12px;
  color: var(--text-color-secondary);
  margin-bottom: 5px;
}

.summary-value {
  font-size: 16px;
  font-weight: bold;
  color: var(--text-color);
}

.downline-agents-card {
  background-color: #fff;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.card-header {
  margin-bottom: 15px;
}

.card-title {
  font-size: 18px;
  font-weight: 600;
  color: #333;
}

.downline-tabs {
  display: flex;
  margin-bottom: 20px;
  border-bottom: 1px solid #eee;
}

.downline-tabs .tab-item {
  padding: 10px 15px;
  margin-right: 15px;
  font-size: 14px;
  color: #666;
  position: relative;
  cursor: pointer;
}

.downline-tabs .tab-item.active {
  color: var(--primary-color, #007AFF);
  font-weight: 500;
}

.downline-tabs .tab-item.active:after {
  content: "";
  position: absolute;
  bottom: -1px;
  left: 0;
  width: 100%;
  height: 2px;
  background-color: var(--primary-color, #007AFF);
}

.empty-state {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 40px 0;
}

.empty-text {
  color: #999;
  font-size: 14px;
}

.downline-list {
  max-height: 400px;
  overflow-y: auto;
}

.downline-item {
  padding: 15px;
  border-radius: 8px;
  background-color: #f8f9fa;
  margin-bottom: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.downline-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.agent-main-info {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 10px;
}

.agent-username {
  font-size: 16px;
  font-weight: 500;
  color: #333;
}

.agent-level {
  font-size: 12px;
  padding: 2px 8px;
  border-radius: 12px;
  background-color: #f0f8ff;
  color: #007AFF;
}

.agent-earnings {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
}

.earnings-item {
  display: flex;
  flex-direction: column;
}

.earnings-label {
  font-size: 12px;
  color: #999;
  margin-bottom: 4px;
}

.earnings-value {
  font-size: 14px;
  font-weight: 500;
  color: #333;
}

.text-success {
  color: #34C759;
}

.text-danger {
  color: #FF3B30;
}

.view-details {
  display: flex;
  justify-content: flex-end;
  align-items: center;
}

.details-text {
  font-size: 13px;
  color: #007AFF;
}

.arrow-icon {
  margin-left: 5px;
  color: #007AFF;
}

.agent-actions {
  margin-top: 20px;
}
</style> 