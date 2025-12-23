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
          pagePath: 'pages/home/index',
          textKey: 'tab_home',
          iconPath: '/static/img/SketchPngcac9cd1caf8ca53e858b5ecec959fcd8dcbc28b0e7982f9eeb62ef4f3e353695.png',
          selectedIconPath: '/static/img/SketchPngcac9cd1caf8ca53e858b5ecec959fcd8dcbc28b0e7982f9eeb62ef4f3e353695.png'
        },
        {
          pagePath: 'pages/market/detail',
          textKey: 'tab_trade',
          iconPath: '/static/img/SketchPng1291228781fb5ae0f5dda7da02c77c21b6073532326b150f8df394daba8ac003.png',
          selectedIconPath: '/static/img/SketchPng1291228781fb5ae0f5dda7da02c77c21b6073532326b150f8df394daba8ac003.png'
        },
        {
          pagePath: 'pages/profile/profile',
          textKey: 'tab_wallet',
          iconPath: '/static/img/SketchPng6f9ae955223f525c9ac01279ca4e9eaaf2da682f0545822ce15d73c07334d255.png',
          selectedIconPath: '/static/img/SketchPng6f9ae955223f525c9ac01279ca4e9eaaf2da682f0545822ce15d73c07334d255.png'
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
  width: 375px;
  background-color: rgba(22, 22, 22, 1);
  padding: 16px 0 25px 0;
  justify-content: space-around;
  z-index: 1000;
}

.tab-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.tab-icon {
  width: 100px;
  height: 100px;
}

.tab-text {
  font-size: 10px;
  color: rgba(255, 255, 255, 0.5);
  font-weight: normal;
  text-align: center;
  white-space: nowrap;
  line-height: 14px;
  margin-top: 5px;
}

.tab-item.active .tab-text {
  color: rgba(249, 213, 74, 1);
}
</style> 1002424