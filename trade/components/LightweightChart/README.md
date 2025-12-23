# LightweightChart 组件使用说明

## 简介

基于 TradingView Lightweight Charts 的 uni-app 图表组件。

## ✨ 特性

- ✅ **完全免费开源** (MIT 许可证)
- ✅ **轻量级** (约 200KB，无需下载大文件)
- ✅ **仅 H5 支持** (浏览器环境)
- ✅ **实时数据** (通过 Binance WebSocket)
- ✅ **历史数据** (通过 Binance API)
- ✅ **自动重连** (WebSocket 断线自动重连)
- ✅ **响应式** (自动适配容器大小)

## 基础用法

```vue
<template>
  <view class="page">
    <LightweightChart 
      symbol="BTCUSDT"
      interval="1m"
      theme="dark"
      :height="500"
      @ready="onChartReady"
      @price-update="onPriceUpdate"
    />
  </view>
</template>

<script>
import LightweightChart from '@/components/LightweightChart/LightweightChart.vue';

export default {
  components: {
    LightweightChart
  },
  methods: {
    onChartReady(chart) {
      console.log('图表已准备就绪', chart);
    },
    onPriceUpdate(price) {
      console.log('当前价格:', price);
    }
  }
}
</script>
```

## Props

| 参数 | 类型 | 默认值 | 说明 |
|------|------|--------|------|
| symbol | String | 'BTCUSDT' | 交易对符号 (Binance 格式) |
| interval | String | '1m' | 时间周期 |
| theme | String | 'dark' | 主题 (dark/light) |
| height | Number | 500 | 图表高度（像素） |
| limit | Number | 500 | 历史数据条数 |
| autoConnect | Boolean | true | 自动连接 WebSocket |
| loadingText | String | '' | 自定义加载文本 |

### symbol - 交易对符号

Binance 交易对格式：
- `BTCUSDT` - 比特币/USDT
- `ETHUSDT` - 以太坊/USDT
- `BNBUSDT` - BNB/USDT
- `SOLUSDT` - Solana/USDT

### interval - 时间周期

支持的周期：
- `1m` - 1 分钟
- `3m` - 3 分钟
- `5m` - 5 分钟
- `15m` - 15 分钟
- `30m` - 30 分钟
- `1h` - 1 小时
- `2h` - 2 小时
- `4h` - 4 小时
- `6h` - 6 小时
- `8h` - 8 小时
- `12h` - 12 小时
- `1d` - 1 天
- `3d` - 3 天
- `1w` - 1 周
- `1M` - 1 月

## Events

| 事件名 | 参数 | 说明 |
|--------|------|------|
| ready | chart: Object | 图表初始化完成 |
| error | error: Error | 图表加载错误 |
| price-update | price: Number | 价格更新 |
| data-loaded | data: Array | 历史数据加载完成 |
| ws-connected | - | WebSocket 连接成功 |
| ws-error | error: Error | WebSocket 错误 |
| ws-closed | - | WebSocket 连接关闭 |

## 方法

通过 ref 调用组件方法：

```vue
<template>
  <LightweightChart ref="chart" symbol="BTCUSDT" />
</template>

<script>
export default {
  methods: {
    // 添加价格线
    addLine() {
      const priceLine = this.$refs.chart.addPriceLine(50000, {
        color: '#2962ff',
        title: '目标价格'
      });
    },
    
    // 添加标记
    addMarker() {
      const time = Math.floor(Date.now() / 1000);
      this.$refs.chart.addMarker(time, {
        position: 'belowBar',
        color: '#26a69a',
        shape: 'arrowUp',
        text: 'B'
      });
    },
    
    // 获取当前价格
    getPrice() {
      const price = this.$refs.chart.getCurrentPrice();
      console.log('当前价格:', price);
    },
    
    // 获取历史数据
    getData() {
      const data = this.$refs.chart.getHistoricalData();
      console.log('历史数据:', data);
    }
  }
}
</script>
```

### addPriceLine(price, options)

添加价格线。

**参数**:
- `price` (Number): 价格
- `options` (Object): 配置选项
  - `color` (String): 线条颜色
  - `lineWidth` (Number): 线条宽度
  - `title` (String): 标题

**返回**: PriceLine 对象

### addMarker(time, options)

添加标记。

**参数**:
- `time` (Number): 时间戳（秒）
- `options` (Object): 配置选项
  - `position` (String): 位置 ('belowBar' | 'aboveBar' | 'inBar')
  - `color` (String): 颜色
  - `shape` (String): 形状 ('circle' | 'square' | 'arrowUp' | 'arrowDown')
  - `text` (String): 文本

### clearMarkers()

清除所有标记。

### getCurrentPrice()

获取当前价格。

**返回**: Number

### getHistoricalData()

获取历史数据。

**返回**: Array

## 完整示例

### 示例 1: 基础图表

```vue
<template>
  <view class="page">
    <LightweightChart 
      symbol="BTCUSDT"
      interval="1m"
      theme="dark"
      :height="500"
    />
  </view>
</template>

<script>
import LightweightChart from '@/components/LightweightChart/LightweightChart.vue';

export default {
  components: {
    LightweightChart
  }
}
</script>
```

### 示例 2: 带价格显示

```vue
<template>
  <view class="page">
    <view class="price-display">
      <text class="symbol">{{ symbol }}</text>
      <text class="price">${{ currentPrice.toFixed(2) }}</text>
    </view>
    
    <LightweightChart 
      :symbol="symbol"
      interval="1m"
      @price-update="onPriceUpdate"
    />
  </view>
</template>

<script>
import LightweightChart from '@/components/LightweightChart/LightweightChart.vue';

export default {
  components: {
    LightweightChart
  },
  data() {
    return {
      symbol: 'BTCUSDT',
      currentPrice: 0
    }
  },
  methods: {
    onPriceUpdate(price) {
      this.currentPrice = price;
    }
  }
}
</script>

<style>
.price-display {
  padding: 20rpx;
  background: #2C2C2C;
}

.symbol {
  font-size: 28rpx;
  color: #999;
}

.price {
  font-size: 40rpx;
  color: #26a69a;
  font-weight: bold;
  display: block;
  margin-top: 10rpx;
}
</style>
```

### 示例 3: 带买入标记

```vue
<template>
  <view class="page">
    <view class="controls">
      <input v-model.number="buyPrice" type="number" placeholder="买入价格" />
      <button @click="setBuyPrice">设置买入价格</button>
    </view>
    
    <LightweightChart 
      ref="chart"
      symbol="BTCUSDT"
      interval="1m"
      @ready="onChartReady"
    />
  </view>
</template>

<script>
import LightweightChart from '@/components/LightweightChart/LightweightChart.vue';

export default {
  components: {
    LightweightChart
  },
  data() {
    return {
      buyPrice: 50000,
      buyPriceLine: null
    }
  },
  methods: {
    onChartReady(chart) {
      console.log('图表已加载');
    },
    
    setBuyPrice() {
      // 移除旧的价格线
      if (this.buyPriceLine) {
        // 注意：Lightweight Charts 不支持直接移除价格线
        // 需要重新创建图表或使用其他方法
      }
      
      // 添加新的价格线
      this.buyPriceLine = this.$refs.chart.addPriceLine(this.buyPrice, {
        color: '#2962ff',
        title: '买入价格'
      });
      
      // 添加买入标记
      const time = Math.floor(Date.now() / 1000);
      this.$refs.chart.addMarker(time, {
        position: 'belowBar',
        color: '#2962ff',
        shape: 'arrowUp',
        text: 'B'
      });
    }
  }
}
</script>
```

### 示例 4: 多交易对切换

```vue
<template>
  <view class="page">
    <view class="symbol-tabs">
      <view 
        v-for="item in symbols" 
        :key="item"
        class="tab"
        :class="{ active: symbol === item }"
        @click="changeSymbol(item)"
      >
        {{ item }}
      </view>
    </view>
    
    <LightweightChart 
      :symbol="symbol"
      interval="1m"
      @price-update="onPriceUpdate"
    />
    
    <view class="info">
      <text>当前价格: ${{ currentPrice.toFixed(2) }}</text>
    </view>
  </view>
</template>

<script>
import LightweightChart from '@/components/LightweightChart/LightweightChart.vue';

export default {
  components: {
    LightweightChart
  },
  data() {
    return {
      symbol: 'BTCUSDT',
      symbols: ['BTCUSDT', 'ETHUSDT', 'BNBUSDT', 'SOLUSDT'],
      currentPrice: 0
    }
  },
  methods: {
    changeSymbol(newSymbol) {
      this.symbol = newSymbol;
    },
    onPriceUpdate(price) {
      this.currentPrice = price;
    }
  }
}
</script>

<style>
.symbol-tabs {
  display: flex;
  gap: 10rpx;
  padding: 20rpx;
  background: #1A1A1A;
}

.tab {
  flex: 1;
  padding: 20rpx;
  text-align: center;
  background: #2C2C2C;
  color: #999;
  border-radius: 8rpx;
}

.tab.active {
  background: #2962ff;
  color: #FFF;
}

.info {
  padding: 20rpx;
  background: #2C2C2C;
  color: #FFF;
  text-align: center;
}
</style>
```

## 数据源

### Binance API

- **历史数据**: `https://api.binance.com/api/v3/klines`
- **WebSocket**: `wss://stream.binance.com:9443/ws`

### 自定义数据源

如果需要使用其他数据源，需要修改组件代码：

1. 修改 `fetchHistoricalData()` 方法
2. 修改 `connectWebSocket()` 方法
3. 确保数据格式符合要求

## 注意事项

1. **仅 H5 可用**: 组件使用了 `#ifdef H5` 条件编译
2. **需要网络**: 需要访问 Binance API 和 WebSocket
3. **CORS**: 确保服务器允许跨域请求
4. **CDN**: 使用 unpkg CDN 加载库文件

## 故障排除

### 图表不显示
- 确认在 H5 环境运行
- 检查浏览器控制台错误
- 确认 CDN 加载成功

### 数据不加载
- 检查网络连接
- 确认 Binance API 可访问
- 查看浏览器 Network 标签

### WebSocket 断开
- 组件会自动重连
- 检查网络稳定性
- 查看控制台日志

## 相关链接

- [Lightweight Charts 文档](https://tradingview.github.io/lightweight-charts/)
- [Binance API 文档](https://binance-docs.github.io/apidocs/)
- [uni-app 文档](https://uniapp.dcloud.net.cn/)
