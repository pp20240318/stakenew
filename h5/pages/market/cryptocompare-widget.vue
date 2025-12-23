<template>
  <view class="coin-detail-container">
    <!-- 头部导航 -->
    <view class="header">
      <view class="back-btn" @tap="goBack">
        <text class="ri-arrow-left-line"></text>
      </view>
      <text class="title">{{ symbol }}{{ $t('走势图') }}</text>
    </view>
    
    <!-- CryptoCompare 嵌入式图表 -->
    <web-view :src="widgetUrl" class="chart-widget"></web-view>
  </view>
</template>

<script>
export default {
  data() {
    return {
      symbol: '',
      widgetUrl: ''
    }
  },
  onLoad(options) {
    if (options.symbol) {
      this.symbol = options.symbol.toUpperCase();
      
      // 构建 CryptoCompare 小部件 URL
      this.widgetUrl = `https://widget.cryptocompare.com/index.html?fsym=${this.symbol}&tsyms=USD,CNY&theme=light`;
    } else {
      uni.showToast({
        title: this.$t('缺少币种代码参数'),
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

.title {
  flex: 1;
  text-align: center;
  font-size: 32rpx;
  font-weight: bold;
  margin-right: 60rpx;
}

.chart-widget {
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