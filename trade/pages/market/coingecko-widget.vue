<template>
  <view class="coin-detail-container">
    <!-- 头部导航 -->
    <view class="header">
      <view class="back-btn" @tap="goBack">
        <image src="/static/images/icons/back-arrow.svg" class="back-icon"></image>
      </view>
      <text class="title">{{ coinName }}</text>
    </view>
    
    <!-- CoinGecko 嵌入式小部件 -->
    <web-view :src="widgetUrl" class="coingecko-widget"></web-view>
  </view>
</template>

<script>
export default {
  data() {
    return {
      coinId: '',
      coinName: '',
      widgetUrl: ''
    }
  },
  onLoad(options) {
    if (options.id) {
      this.coinId = options.id;
      this.coinName = options.name || this.coinId;
      
      // 构建 CoinGecko 小部件 URL
      this.widgetUrl = `https://www.coingecko.com/en/coins/${this.coinId}/embed`;
    } else {
      uni.showToast({
        title: '缺少币种ID参数',
        icon: 'none'
      });
      setTimeout(() => {
        uni.navigateBack();
      }, 1500);
    }
  },
  methods: {
    goBack() {
      uni.navigateBack();
    }
  }
}
</script>

<style>
.coin-detail-container {
  display: flex;
  flex-direction: column;
  height: 100vh;
}

.header {
  display: flex;
  align-items: center;
  height: 90rpx;
  padding: 0 30rpx;
  background-color: #FFFFFF;
  border-bottom: 1px solid #EEEEEE;
}

.back-btn {
  width: 60rpx;
  height: 60rpx;
  display: flex;
  align-items: center;
  justify-content: center;
}

.back-icon {
  width: 40rpx;
  height: 40rpx;
}

.title {
  flex: 1;
  text-align: center;
  font-size: 32rpx;
  font-weight: bold;
  margin-right: 60rpx;
}

.coingecko-widget {
  flex: 1;
  width: 100%;
}

/* 添加箭头图标样式 */
.ri-arrow-left-line:before {
	content: "←";
	font-size: 36rpx;
	color: #FFFFFF;
}
</style> 