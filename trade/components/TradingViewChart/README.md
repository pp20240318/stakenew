# TradingViewChart 组件使用说明

## 简介

这是一个封装了 TradingView Charting Library 的 uni-app 组件，专为 H5 端设计。

## 平台支持

- ✅ **H5**: 完全支持
- ❌ **小程序/App**: 不支持（会显示提示信息）

## 基础用法

```vue
<template>
  <view class="page">
    <TradingViewChart 
      :symbol="symbol"
      interval="D"
      @ready="onChartReady"
    />
  </view>
</template>

<script>
import TradingViewChart from '@/components/TradingViewChart/TradingViewChart.vue';

export default {
  components: {
    TradingViewChart
  },
  data() {
    return {
      symbol: 'BTCUSDT'
    }
  },
  methods: {
    onChartReady(widget) {
      console.log('图表已准备就绪', widget);
    }
  }
}
</script>
```

## Props

| 参数 | 类型 | 默认值 | 必填 | 说明 |
|------|------|--------|------|------|
| symbol | String | 'BTCUSDT' | 否 | 交易对符号 |
| interval | String | 'D' | 否 | 时间周期 |
| theme | String | 'dark' | 否 | 主题 (dark/light) |
| autosize | Boolean | true | 否 | 自动调整大小 |
| height | Number | 500 | 否 | 图表高度（像素） |

### symbol - 交易对符号

支持的格式：
- `BTCUSDT` - 比特币/USDT
- `ETHUSDT` - 以太坊/USDT
- `BNBUSDT` - BNB/USDT
- 等等...

### interval - 时间周期

支持的值：
- `1` - 1 分钟
- `5` - 5 分钟
- `15` - 15 分钟
- `30` - 30 分钟
- `60` - 1 小时
- `D` - 日线
- `W` - 周线
- `M` - 月线

## Events

| 事件名 | 参数 | 说明 |
|--------|------|------|
| ready | widget: Object | 图表初始化完成 |
| error | error: Error | 图表加载错误 |

### ready 事件

当图表初始化完成时触发，返回 TradingView widget 实例。

```javascript
onChartReady(widget) {
  // 可以使用 widget 实例进行更多操作
  console.log('图表准备就绪', widget);
  
  // 例如：获取图表对象
  const chart = widget.chart();
  
  // 例如：添加自定义按钮
  widget.headerReady().then(() => {
    const button = widget.createButton();
    button.textContent = '自定义按钮';
    button.addEventListener('click', () => {
      console.log('按钮被点击');
    });
  });
}
```

### error 事件

当图表加载失败时触发。

```javascript
onChartError(error) {
  console.error('图表错误', error);
  uni.showToast({
    title: '图表加载失败',
    icon: 'none'
  });
}
```

## 完整示例

### 示例 1: 基础图表

```vue
<template>
  <view class="chart-page">
    <TradingViewChart 
      symbol="BTCUSDT"
      interval="D"
      theme="dark"
    />
  </view>
</template>

<script>
import TradingViewChart from '@/components/TradingViewChart/TradingViewChart.vue';

export default {
  components: {
    TradingViewChart
  }
}
</script>

<style>
.chart-page {
  padding: 20rpx;
}
</style>
```

### 示例 2: 动态切换交易对

```vue
<template>
  <view class="chart-page">
    <!-- 交易对选择器 -->
    <view class="symbol-selector">
      <button 
        v-for="item in symbols" 
        :key="item"
        @click="changeSymbol(item)"
        :class="{ active: symbol === item }"
      >
        {{ item }}
      </button>
    </view>
    
    <!-- 图表 -->
    <TradingViewChart 
      :symbol="symbol"
      interval="D"
      @ready="onChartReady"
    />
  </view>
</template>

<script>
import TradingViewChart from '@/components/TradingViewChart/TradingViewChart.vue';

export default {
  components: {
    TradingViewChart
  },
  data() {
    return {
      symbol: 'BTCUSDT',
      symbols: ['BTCUSDT', 'ETHUSDT', 'BNBUSDT']
    }
  },
  methods: {
    changeSymbol(newSymbol) {
      this.symbol = newSymbol;
    },
    onChartReady(widget) {
      console.log('图表已加载:', this.symbol);
    }
  }
}
</script>

<style>
.symbol-selector {
  display: flex;
  gap: 10rpx;
  margin-bottom: 20rpx;
}

.symbol-selector button {
  padding: 10rpx 20rpx;
  background: #2C2C2C;
  color: #FFF;
  border: none;
  border-radius: 8rpx;
}

.symbol-selector button.active {
  background: #34C759;
}
</style>
```

### 示例 3: 时间周期切换

```vue
<template>
  <view class="chart-page">
    <!-- 时间周期选择器 -->
    <view class="interval-selector">
      <button 
        v-for="item in intervals" 
        :key="item.value"
        @click="changeInterval(item.value)"
        :class="{ active: interval === item.value }"
      >
        {{ item.label }}
      </button>
    </view>
    
    <!-- 图表 -->
    <TradingViewChart 
      symbol="BTCUSDT"
      :interval="interval"
      @ready="onChartReady"
    />
  </view>
</template>

<script>
import TradingViewChart from '@/components/TradingViewChart/TradingViewChart.vue';

export default {
  components: {
    TradingViewChart
  },
  data() {
    return {
      interval: 'D',
      intervals: [
        { label: '1分钟', value: '1' },
        { label: '5分钟', value: '5' },
        { label: '15分钟', value: '15' },
        { label: '1小时', value: '60' },
        { label: '日线', value: 'D' },
        { label: '周线', value: 'W' }
      ]
    }
  },
  methods: {
    changeInterval(newInterval) {
      this.interval = newInterval;
    },
    onChartReady(widget) {
      console.log('图表已加载，周期:', this.interval);
    }
  }
}
</script>
```

### 示例 4: 完整的交易页面

```vue
<template>
  <view class="trading-page">
    <!-- 头部信息 -->
    <view class="header">
      <text class="symbol">{{ symbol }}</text>
      <text class="price">${{ currentPrice }}</text>
    </view>
    
    <!-- 图表 -->
    <view class="chart-container">
      <TradingViewChart 
        :symbol="symbol"
        :interval="interval"
        theme="dark"
        :height="400"
        @ready="onChartReady"
        @error="onChartError"
      />
    </view>
    
    <!-- 控制面板 -->
    <view class="controls">
      <view class="control-group">
        <text class="label">交易对:</text>
        <picker 
          :value="symbolIndex" 
          :range="symbolList"
          @change="onSymbolChange"
        >
          <view class="picker">{{ symbol }}</view>
        </picker>
      </view>
      
      <view class="control-group">
        <text class="label">周期:</text>
        <picker 
          :value="intervalIndex" 
          :range="intervalList"
          range-key="label"
          @change="onIntervalChange"
        >
          <view class="picker">{{ currentIntervalLabel }}</view>
        </picker>
      </view>
    </view>
  </view>
</template>

<script>
import TradingViewChart from '@/components/TradingViewChart/TradingViewChart.vue';

export default {
  components: {
    TradingViewChart
  },
  data() {
    return {
      symbol: 'BTCUSDT',
      interval: 'D',
      currentPrice: 0,
      symbolIndex: 0,
      symbolList: ['BTCUSDT', 'ETHUSDT', 'BNBUSDT', 'SOLUSDT'],
      intervalIndex: 4,
      intervalList: [
        { label: '1分钟', value: '1' },
        { label: '5分钟', value: '5' },
        { label: '15分钟', value: '15' },
        { label: '1小时', value: '60' },
        { label: '日线', value: 'D' },
        { label: '周线', value: 'W' }
      ]
    }
  },
  computed: {
    currentIntervalLabel() {
      return this.intervalList[this.intervalIndex].label;
    }
  },
  methods: {
    onSymbolChange(e) {
      this.symbolIndex = e.detail.value;
      this.symbol = this.symbolList[this.symbolIndex];
    },
    onIntervalChange(e) {
      this.intervalIndex = e.detail.value;
      this.interval = this.intervalList[this.intervalIndex].value;
    },
    onChartReady(widget) {
      console.log('图表准备就绪');
      // 可以在这里获取当前价格等信息
    },
    onChartError(error) {
      uni.showToast({
        title: '图表加载失败',
        icon: 'none'
      });
    }
  }
}
</script>

<style scoped>
.trading-page {
  background: #1A1A1A;
  min-height: 100vh;
}

.header {
  padding: 30rpx;
  background: #2C2C2C;
}

.symbol {
  font-size: 32rpx;
  color: #FFF;
  font-weight: bold;
}

.price {
  font-size: 40rpx;
  color: #34C759;
  margin-top: 10rpx;
  display: block;
}

.chart-container {
  margin: 20rpx;
}

.controls {
  padding: 30rpx;
}

.control-group {
  display: flex;
  align-items: center;
  margin-bottom: 20rpx;
}

.label {
  color: #999;
  font-size: 28rpx;
  width: 150rpx;
}

.picker {
  flex: 1;
  padding: 20rpx;
  background: #2C2C2C;
  color: #FFF;
  border-radius: 8rpx;
}
</style>
```

## 注意事项

1. **仅 H5 可用**: 组件使用了 `#ifdef H5` 条件编译，只在 H5 端生效
2. **库文件必需**: 确保 `static/charting_library/` 和 `static/datafeeds/` 文件夹存在
3. **数据源配置**: 默认使用演示数据源，生产环境需要配置真实数据源
4. **性能考虑**: 图表组件较重，建议按需加载
5. **生命周期**: 组件会自动处理创建和销毁，无需手动管理

## 高级用法

### 访问 Widget API

```javascript
onChartReady(widget) {
  // 获取图表对象
  const chart = widget.chart();
  
  // 设置交易对
  chart.setSymbol('ETHUSDT', () => {
    console.log('交易对已切换');
  });
  
  // 设置时间周期
  chart.setResolution('60', () => {
    console.log('周期已切换');
  });
  
  // 创建研究指标
  chart.createStudy('Moving Average', false, false, [14]);
  
  // 截图
  widget.takeScreenshot();
}
```

### 自定义主题

编辑组件文件 `TradingViewChart.vue`，修改 `overrides` 对象。

## 故障排除

### 图表不显示
- 确认在 H5 环境运行
- 检查库文件是否存在
- 查看浏览器控制台错误

### 数据不加载
- 检查数据源配置
- 确认网络连接正常
- 查看 API 请求是否成功

## 相关文档

- [完整集成指南](../../UNIAPP_TRADINGVIEW_集成指南.md)
- [快速开始](../../TRADINGVIEW_快速开始.md)
- [TradingView 官方文档](https://www.tradingview.com/charting-library-docs/)
