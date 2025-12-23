<template>
  <view class="page">
    <!-- 顶部状态栏 -->
    <view class="header-section">
      <!-- 标题栏 -->
      <view class="title-bar">
        <view class="logo-section">
          <image class="logo-img" src="/static/img/logo.png" />
          <text class="logo-text">Second Futures</text>
          
        </view>
        <view class="logo-section">
          <!-- 公告图标 -->
          <image class="notice-bell" src="/static/img/icon-bell.png" @tap="goToNotice" />
          <!-- 语言切换 -->
          <view class="lang-switch" @tap="showLanguagePopup = true">
            <text class="lang-text">{{ currentLanguageText }}</text>
            <text class="lang-arrow">></text>
          </view>
        </view>
      </view>

      <!-- 语言选择弹窗 -->
      <view class="language-popup" v-if="showLanguagePopup" @tap="showLanguagePopup = false">
        <view class="language-popup-content" @tap.stop>
          <view class="language-popup-title">{{ $t('语言') }}</view>
          <view
            class="language-item"
            v-for="(lang, index) in languageList"
            :key="index"
            :class="{ active: currentLanguageIndex === index }"
            @tap="selectLanguage(index)"
          >
            <text>{{ lang.name }}</text>
            <text v-if="currentLanguageIndex === index" class="check-icon">✓</text>
          </view>
        </view>
      </view>

      <!-- Banner轮播区域 -->
      <view class="hero-banner">
        <image class="banner-image" src="/static/img/lunbo.png" mode="aspectFill" />
        <view class="carousel-indicators">
          <view class="carousel-indicator active"></view>
        </view>
      </view>

      <!-- 四宫格导航菜单 -->
      <view class="nav-menu">
        <view class="nav-grid">
          <view class="nav-item nav-item-1" @tap="goToPage('/pages/home/activity')">
            <text class="nav-text">{{ $t('活动福利') }}</text>
          </view>
          <view class="nav-item nav-item-2" @tap="goToPage('/pages/home/download')">
            <text class="nav-text">{{ $t('下载中心') }}</text>
          </view>
          <view class="nav-item nav-item-3" @tap="goToPage('/pages/home/promote')">
            <text class="nav-text">{{ $t('推广中心') }}</text>
          </view>
          <view class="nav-item nav-item-4" @tap="goToPage('/pages/home/guide')">
            <text class="nav-text">{{ $t('玩法介绍') }}</text>
          </view>
          <view class="nav-center-logo" @tap="goToTradePage">
            <image class="center-logo-img" src="/static/img/logo.png" />
          </view>
        </view>
      </view>
    </view>

    <!-- 市场行情区 -->
    <view class="market-section">
      <!-- 盈利提示走马灯 -->
      <view class="profit-notice">
        <image class="notice-icon" src="/static/img/icon-speaker.png" />
        <swiper class="notice-swiper" vertical circular :autoplay="true" :interval="3000" :duration="500">
          <swiper-item v-for="(notice, index) in profitNotices" :key="index">
            <text class="notice-text">{{ notice }}</text>
          </swiper-item>
        </swiper>
      </view>

      <!-- 大盘行情卡片 -->
      <view class="market-cards">
        <!-- BTC卡片 -->
        <view class="market-card card-red" @tap="goToTradeByCode('BTCUSDT')">
          <view class="card-header">
            <image class="coin-icon" src="/static/img/coins/BTCUSDT.png" />
            <view class="coin-info">
              <text class="coin-name">BTCUSDT</text>
              <text class="coin-fullname">Bitcoin</text>
            </view>
          </view>
          <view class="card-body">
            <view class="price-info">
              <text class="price-value">${{ formatPrice(btcData.price) }}</text>
              <text :class="['price-change', btcData.change >= 0 ? 'positive' : 'negative']">
                {{ btcData.change >= 0 ? '+' : '' }}{{ btcData.change.toFixed(2) }}%
              </text>
            </view>
          </view>
        </view>

        <!-- ETH卡片 -->
        <view class="market-card card-green" @tap="goToTradeByCode('ETHUSDT')">
          <view class="card-header">
            <image class="coin-icon" src="/static/img/coins/ETHUSDT.png" />
            <view class="coin-info">
              <text class="coin-name">ETHUSDT</text>
              <text class="coin-fullname">Ethereum</text>
            </view>
          </view>
          <view class="card-body">
            <view class="price-info">
              <text class="price-value">${{ formatPrice(ethData.price) }}</text>
              <text :class="['price-change', ethData.change >= 0 ? 'positive' : 'negative']">
                {{ ethData.change >= 0 ? '+' : '' }}{{ ethData.change.toFixed(2) }}%
              </text>
            </view>
          </view>
        </view>
      </view>

      <!-- Hot标题 -->
      <view class="hot-title">
        <text class="hot-text">Hot</text>
        <image class="hot-icon" src="/static/img/icon-fire.png" />
      </view>

      <!-- 热门币种列表 -->
      <scroll-view class="coin-list" scroll-y>
        <view
          v-for="(coin, index) in hotCoins"
          :key="index"
          class="coin-item"
          @tap="goToTrade(coin)"
        >
          <view class="coin-left">
            <image class="coin-avatar" :src="coin.icon || getDefaultIcon(coin.code)" />
            <view class="coin-names">
              <text class="coin-symbol">{{ coin.code }}{{coin.icon}}</text>
              <text class="coin-realname">{{ coin.realname }}</text>
            </view>
          </view>
          <view class="coin-right">
            <text class="coin-price">${{ formatPrice(coin.price) }}</text>
            <text :class="['coin-change', coin.change >= 0 ? 'positive' : 'negative']">
              {{ coin.change >= 0 ? '+' : '' }}{{ coin.change.toFixed(2) }}%
            </text>
          </view>
        </view>
      </scroll-view>
    </view>
  </view>
</template>

<script>
export default {
  data() {
    return {
      // 大盘行情数据
      btcData: {
        price: 0,
        change: 0
      },
      ethData: {
        price: 0,
        change: 0
      },
      // 热门币种列表
      hotCoins: [],
      // WebSocket连接
      wsConnection: null,
      // 订阅的交易对
      subscribedSymbols: [],
      // 盈利提示走马灯数据
      profitNotices: [],
      // 语言选择相关
      showLanguagePopup: false,
      currentLanguageIndex: 0,
      languageList: [
        { name: 'English', value: 'en' },
        { name: '中文', value: 'zhHans' },
        { name: 'Português', value: 'ptyy' },
        { name: '日本語', value: 'ry' }
      ]
    };
  },
  computed: {
    currentLanguageText() {
      return this.languageList[this.currentLanguageIndex]?.name || 'English';
    }
  },
  onLoad() {
    this.generateProfitNotices();
    this.initLanguageIndex();
  },
  onShow() {
    // 每次显示页面时重新生成盈利提示
    this.generateProfitNotices();
    // 页面显示时重新连接WebSocket
    if (this.subscribedSymbols.length > 0) {
      this.connectWebSocket();
    }
    this.loadTradePairs();

    // 更新 tabBar 语言
    this.updateTabBarLanguage();
    // 初始化语言索引
    this.initLanguageIndex();
  },
  onHide() {
    // 页面隐藏时断开WebSocket
    this.disconnectWebSocket();
  },
  onUnload() {
    this.disconnectWebSocket();
  },
  methods: {
    // 更新 tabBar 语言
    updateTabBarLanguage() {
      console.log('首页: 开始更新 tabBar 语言');

      try {
        // 从存储获取当前语言
        let currentLanguage = uni.getStorageSync('userlanguage') || 'en';
      

        // 确保 i18n 语言已设置
        if (this.$i18n) {
          if (this.$i18n.global) {
            this.$i18n.global.locale.value = currentLanguage;
          } else {
            this.$i18n.locale = currentLanguage;
          }
        }

        // 延迟一下确保语言已切换
        setTimeout(() => {
          // 更新三个 tabBar 项
          uni.setTabBarItem({
            index: 0,
            text: this.$t('tabBar_home'),
            success: () => { 
            },
            fail: (err) => { 
            }
          });

          uni.setTabBarItem({
            index: 1,
            text: this.$t('tabBar_trade'),
            success: () => { 
            },
            fail: (err) => { 
            }
          });

          uni.setTabBarItem({
            index: 2,
            text: this.$t('tabBar_wallet'),
            success: () => { 
            },
            fail: (err) => { 
            }
          });
 
        }, 100);
      } catch (e) {
        console.error('首页: 更新 tabBar 时出错:', e);
      }
    },

    // 生成随机盈利提示数据
    generateProfitNotices() {
      const notices = [];
      const coins = ['BTC', 'ETH', 'USDT', 'BNB', 'SOL', 'XRP', 'ADA', 'DOGE'];
      // 常见英文名字前缀
      const namePrefix = ['john', 'mike', 'david', 'james', 'alex', 'chris', 'tom', 'jack', 'peter', 'mark', 'sarah', 'emma', 'lisa', 'kate', 'anna', 'lucy', 'kevin', 'brian', 'steve', 'paul'];

      for (let i = 0; i < 20; i++) {
        // 生成随机邮箱格式用户名（如：joh***23@gmail.com）
        const name = namePrefix[Math.floor(Math.random() * namePrefix.length)];
        const suffix = Math.floor(10 + Math.random() * 90);
        const email = `${name.substring(0, 3)}***${suffix}@gmail.com`;

        // 生成随机盈利金额（10-9999 USDT）
        const profit = Math.floor(10 + Math.random() * 9990);

        // 随机选择币种
        const coin = coins[Math.floor(Math.random() * coins.length)];

        // 使用多语言翻译生成提示文本
        const notice = this.$t('profit_notice', {
          phone: email,
          profit: profit,
          coin: coin
        });

        notices.push(notice);
      }

      this.profitNotices = notices;
    },

    // 加载交易对列表
    loadTradePairs() {
      const t = this;
      t.req({
        url: "tradePair/list",
        data: {},
        success: function(res) {
          if (res.code == 1 && res.data && res.data.list) {
            // 格式化数据
            t.hotCoins = res.data.list.map(item => ({
              id: item.id,
              code: item.code,
              name: item.name,
              symbol: item.symbol,
              realname: item.realname,
              icon: item.icon ? t.H5BaseUrl + item.icon : '',
              price: 0,
              change: 0
            }));

            // 收集需要订阅的交易对符号
            t.subscribedSymbols = t.hotCoins.map(coin => coin.code.toLowerCase());

            // 添加BTC和ETH（如果不在列表中）
            if (!t.subscribedSymbols.includes('btcusdt')) {
              t.subscribedSymbols.unshift('btcusdt');
            }
            if (!t.subscribedSymbols.includes('ethusdt')) {
              t.subscribedSymbols.push('ethusdt');
            }

            // 连接WebSocket获取实时数据
            t.connectWebSocket();
          }
        },
        fail: function(err) {
          console.error('获取交易对列表失败:', err);
        }
      });
    },

    // 连接币安WebSocket
    connectWebSocket() {
      const t = this;

      // 先断开旧连接
      t.disconnectWebSocket();

      // 构建订阅流 - 使用永续合约数据
      const streams = t.subscribedSymbols.map(s => `${s}@ticker`).join('/');
      const wsUrl = `wss://fstream.binance.com/stream?streams=${streams}`;

      try {
        t.wsConnection = uni.connectSocket({
          url: wsUrl,
          success: () => {
            console.log('WebSocket连接成功');
          }
        });

        // 监听WebSocket消息
        uni.onSocketMessage(function(res) {
          try {
            const data = JSON.parse(res.data);
            if (data.data) {
              t.handleTickerData(data.data);
            }
          } catch (e) {
            console.error('解析WebSocket数据失败:', e);
          }
        });

        // 监听WebSocket错误
        uni.onSocketError(function(err) {
          console.error('WebSocket错误:', err);
        });

        // 监听WebSocket关闭
        uni.onSocketClose(function() {
          console.log('WebSocket已关闭');
        });

      } catch (e) {
        console.error('WebSocket连接失败:', e);
      }
    },

    // 断开WebSocket
    disconnectWebSocket() {
      if (this.wsConnection) {
        uni.closeSocket();
        this.wsConnection = null;
      }
    },

    // 处理Ticker数据
    handleTickerData(ticker) {
      const symbol = ticker.s.toLowerCase();
      const price = parseFloat(ticker.c);
      const change = parseFloat(ticker.P);

      // 更新BTC数据
      if (symbol === 'btcusdt') {
        this.btcData.price = price;
        this.btcData.change = change;
      }

      // 更新ETH数据
      if (symbol === 'ethusdt') {
        this.ethData.price = price;
        this.ethData.change = change;
      }

      // 更新热门币种列表
      const coinIndex = this.hotCoins.findIndex(c => c.code.toLowerCase() === symbol);
      if (coinIndex !== -1) {
        this.hotCoins[coinIndex].price = price;
        this.hotCoins[coinIndex].change = change;
      }
    },

    // 格式化价格
    formatPrice(price) {
      if (!price) return '0.00';
      if (price < 0.01) {
        return price.toFixed(6);
      } else if (price < 1) {
        return price.toFixed(4);
      } else if (price < 10000) {
        return price.toFixed(2);
      } else {
        return price.toLocaleString('en-US', { maximumFractionDigits: 2 });
      }
    },

    // 获取默认图标
    getDefaultIcon(symbol) {
      if (!symbol) return '/static/img/coins/btc.png';
      // 从symbol中提取币种名称（如 BTCUSDT -> btc）
      const coinName = symbol; 
      return `/static/img/coins/${coinName}.png`;
    },

    // 跳转到交易页面（通过币种对象）
    goToTrade(coin) {
      // 存储要跳转的交易对code
      uni.setStorageSync('selectedTradeCode', coin.code);
      uni.switchTab({
        url: '/pages/market/detail'
      });
    },

    // 跳转到交易页面（通过交易对代码）
    goToTradeByCode(code) {
      // 存储要跳转的交易对code
      uni.setStorageSync('selectedTradeCode', code);
      uni.switchTab({
        url: '/pages/market/detail'
      });
    },

    // 跳转到指定页面
    goToPage(url) {
      uni.navigateTo({
        url: url
      });
    },

    // 跳转到交易页面
    goToTradePage() {
      uni.switchTab({
        url: '/pages/market/detail'
      });
    },

    // 跳转到公告页面
    goToNotice() {
      uni.navigateTo({
        url: '/pages/notice/list'
      });
    },

    // 初始化语言索引
    initLanguageIndex() {
      const savedLanguage = uni.getStorageSync('userlanguage') || 'en';
      const index = this.languageList.findIndex(lang => lang.value === savedLanguage);
      this.currentLanguageIndex = index >= 0 ? index : 0;
    },

    // 选择语言
    selectLanguage(index) {
      this.currentLanguageIndex = index;
      const selectedLang = this.languageList[index];

      // 设置语言
      if (this.$i18n) {
        if (this.$i18n.global) {
          this.$i18n.global.locale.value = selectedLang.value;
        } else {
          this.$i18n.locale = selectedLang.value;
        }
      }

      // 保存到本地存储
      uni.setStorageSync('userlanguage', selectedLang.value);
      uni.setStorageSync('userlanguagetxt', selectedLang.name);
      uni.setStorageSync('userlanguageindex', index);

      // 关闭弹窗
      this.showLanguagePopup = false;

      // 触发语言变化事件
      uni.$emit('languageChanged');

      // 更新 tabBar
      this.updateTabBarLanguage();

      // 重新生成盈利提示（使用新语言）
      this.generateProfitNotices();

      uni.showToast({
        title: selectedLang.name,
        icon: 'none'
      });
    }
  }
};
</script>

<style>
.page {
  background-color: #1A2026;
  min-height: 100vh;
  width: 100%;
  display: flex;
  flex-direction: column;
}

/* 顶部区域 */
.header-section {
  padding: 20rpx 30rpx;
}

/* 标题栏 */
.title-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30rpx;
}

.logo-section {
  display: flex;
  align-items: center;
  gap: 16rpx;
}

.logo-img {
  width: 60rpx;
  height: 60rpx;
}

.logo-text {
  color: #FFFFFF;
  font-size: 36rpx;
  font-weight: 500;
}

/* 公告图标 */
.notice-bell {
  width: 40rpx;
  height: 40rpx;  
}

/* 语言切换 */
.lang-switch {
  display: flex;
  align-items: center;
  gap: 8rpx;
  padding: 10rpx 16rpx;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 20rpx;
}

.lang-text {
  color: #40DFBF;
  font-size: 24rpx;
}

.lang-arrow {
  color: #40DFBF;
  font-size: 24rpx;
}

/* 语言选择弹窗 */
.language-popup {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 999;
  display: flex;
  align-items: center;
  justify-content: center;
}

.language-popup-content {
  width: 500rpx;
  background: #2C2C2C;
  border-radius: 20rpx;
  padding: 30rpx;
}

.language-popup-title {
  color: #FFFFFF;
  font-size: 32rpx;
  font-weight: 500;
  text-align: center;
  margin-bottom: 30rpx;
  padding-bottom: 20rpx;
  border-bottom: 1rpx solid rgba(255, 255, 255, 0.1);
}

.language-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24rpx 20rpx;
  color: #FFFFFF;
  font-size: 28rpx;
  border-radius: 12rpx;
}

.language-item.active {
  background: rgba(64, 223, 191, 0.1);
  color: #40DFBF;
}

.check-icon {
  color: #40DFBF;
  font-size: 32rpx;
}

/* Banner */
.hero-banner {
  position: relative;
  width: 100%;
  height: 280rpx;
  border-radius: 24rpx;
  overflow: hidden;
  margin-bottom: 30rpx;
}

.banner-image {
  width: 100%;
  height: 100%;
}

.carousel-indicators {
  position: absolute;
  bottom: 20rpx;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 12rpx;
}

.carousel-indicator {
  width: 16rpx;
  height: 16rpx;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.5);
}

.carousel-indicator.active {
  background: #fff;
  width: 40rpx;
  border-radius: 8rpx;
}

/* 导航菜单 */
.nav-menu {
  width: 100%;
  border-radius: 24rpx;
  overflow: hidden;
  margin-bottom: 30rpx;
}

.nav-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  position: relative;
}

.nav-item {
  padding: 50rpx 30rpx;
  display: flex;
  align-items: center;
  justify-content: center;
}

.nav-text {
  color: #fff;
  font-size: 28rpx;
  font-weight: 500;
}

.nav-item-1 {
  background: linear-gradient(165deg, #201C10 0%, #b09649 100%);
  border-top-left-radius: 24rpx;
}

.nav-item-2 {
  background: linear-gradient(-165deg, #0c4663 0%, #2f6d82 100%);
  border-top-right-radius: 24rpx;
}

.nav-item-3 {
  background: linear-gradient(35deg, #211612 0%, #86381b 100%);
  border-bottom-left-radius: 24rpx;
}

.nav-item-4 {
  background: linear-gradient(-35deg, #22151F 0%, #791f64 100%);
  border-bottom-right-radius: 24rpx;
}

.nav-center-logo {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 100rpx;
  height: 100rpx;
  background: rgba(51, 20, 20, 0.9);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8rpx 30rpx rgba(177, 100, 15, 0.5);
  border: 4rpx solid rgba(0, 0, 0, 0.8);
  padding: 16rpx;
}

.center-logo-img {
  width: 100%;
  height: 100%;
}

/* 市场行情区 */
.market-section {
  flex: 1;
  background: linear-gradient(180deg, #252E3C 0%, #1A2026 100%);
  border-radius: 40rpx 40rpx 0 0;
  padding: 30rpx;
  display: flex;
  flex-direction: column;
}

/* 盈利提示 */
.profit-notice {
  display: flex;
  align-items: center;
  gap: 16rpx;
  margin-bottom: 24rpx;
  height: 60rpx;
}

.notice-icon {
  width: 40rpx;
  height: 40rpx;
  flex-shrink: 0;
}

.notice-swiper {
  flex: 1;
  height: 60rpx;
}

.notice-text {
  color: #FFFFFF;
  font-size: 26rpx;
  line-height: 60rpx;
}

/* 大盘行情卡片 */
.market-cards {
  display: flex;
  gap: 20rpx;
  margin-bottom: 30rpx;
}

.market-card {
  flex: 1;
  border-radius: 20rpx;
  padding: 24rpx;
}

.card-red {
  background: linear-gradient(135deg, #160505 0%, #612828 100%);
}

.card-green {
  background: linear-gradient(135deg, #102a20 0%, #2e6242 100%);
}

.card-header {
  display: flex;
  align-items: center;
  gap: 16rpx;
  margin-bottom: 20rpx;
}

.coin-icon {
  width: 60rpx;
  height: 60rpx;
  border-radius: 50%;
}

.coin-info {
  display: flex;
  flex-direction: column;
}

.coin-name {
  color: #EDF2F7;
  font-size: 26rpx;
  font-weight: 500;
}

.coin-fullname {
  color: #A0AEC0;
  font-size: 20rpx;
}

.card-body {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
}

.price-info {
  display: flex;
  flex-direction: column;
}

.price-value {
  color: #EDF2F7;
  font-size: 28rpx;
  font-weight: 600;
}

.price-change {
  font-size: 24rpx;
  margin-top: 8rpx;
}

.price-change.positive {
  color: #33D49D;
}

.price-change.negative {
  color: #FF6B6B;
}

/* Hot标题 */
.hot-title {
  display: flex;
  align-items: center;
  gap: 12rpx;
  margin-bottom: 24rpx;
}

.hot-text {
  color: #EDF2F7;
  font-size: 32rpx;
  font-weight: 600;
}

.hot-icon {
  width: 36rpx;
  height: 36rpx;
}

/* 热门币种列表 */
.coin-list {
  flex: 1;
}

.coin-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24rpx 0;
  border-bottom: 1rpx solid rgba(255, 255, 255, 0.1);
}

.coin-item:last-child {
  border-bottom: none;
}

.coin-left {
  display: flex;
  align-items: center;
  gap: 20rpx;
}

.coin-avatar {
  width: 64rpx;
  height: 64rpx;
  border-radius: 50%;
  background-color: #1A2026;
}

.coin-names {
  display: flex;
  flex-direction: column;
}

.coin-symbol {
  color: #EDF2F7;
  font-size: 28rpx;
  font-weight: 500;
}

.coin-realname {
  color: #A0AEC0;
  font-size: 22rpx;
  margin-top: 4rpx;
}

.coin-right {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.coin-price {
  color: #EDF2F7;
  font-size: 28rpx;
  font-weight: 500;
}

.coin-change {
  font-size: 24rpx;
  margin-top: 4rpx;
}

.coin-change.positive {
  color: #33D49D;
}

.coin-change.negative {
  color: #FF6B6B;
}
</style>
