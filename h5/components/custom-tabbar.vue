<template>
  <view class="custom-tabbar">
    <view 
      v-for="(item, index) in tabs" 
      :key="index" 
      class="tab-item" 
      :class="{ active: currentPath === item.pagePath }"
      @click="switchTab(item.pagePath)">
      <image :src="currentPath === item.pagePath ? item.selectedIconPath : item.iconPath" class="tab-icon"></image>
      <text class="tab-text">{{ $t(item.textKey) }}</text>
    </view>
  </view>
</template>

<script>
export default {
  name: 'CustomTabbar',
  data() {
    return {
      currentPath: '',
      tabs: [
        {
          pagePath: 'pages/market/market',
          textKey: '热门市场',
          iconPath: '/static/images/tabbar/market-dark.svg',
          selectedIconPath: '/static/images/tabbar/market-active-dark.svg'
        },
        {
          pagePath: 'pages/market/allmarket',
          textKey: '所有市场',
          iconPath: '/static/images/tabbar/exchange-dark.svg',
          selectedIconPath: '/static/images/tabbar/exchange-active-dark.svg'
        },
        {
          pagePath: 'pages/profile/profile',
          textKey: '我的',
          iconPath: '/static/images/tabbar/profile-dark.svg',
          selectedIconPath: '/static/images/tabbar/profile-active-dark.svg'
        }
      ]
    };
  },
  created() {
    // Get current page path when component is created
    const pages = getCurrentPages();
    if (pages.length > 0) {
      const currentPage = pages[pages.length - 1];
      this.currentPath = currentPage.route;
    }
    // Listen for page changes
    uni.$on('page-change', (path) => {
      this.currentPath = path;
    });
  },
  methods: {
    switchTab(path) {
      uni.switchTab({
        url: '/' + path
      });
    }
  }
}
</script>

<style>
.custom-tabbar {
  display: flex;
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  height: 50px;
  background-color: #2C2C2C;
  border-top: 1px solid #1A1A1A;
}

.tab-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.tab-icon {
  width: 24px;
  height: 24px;
  margin-bottom: 2px;
}

.tab-text {
  font-size: 12px;
  color: #999999;
}

.tab-item.active .tab-text {
  color: #007AFF;
}
</style> 