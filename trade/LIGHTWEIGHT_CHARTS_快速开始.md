# Lightweight Charts 快速开始指南

## 🎉 好消息！

我们已经将项目从 TradingView Charting Library 切换到 **Lightweight Charts**！

### 为什么更好？

| 特性 | Lightweight Charts | Charting Library |
|------|-------------------|------------------|
| 许可证 | ✅ MIT (完全免费) | ❌ 需要商业许可证 |
| 文件大小 | ✅ ~200KB | ❌ ~50MB |
| 下载安装 | ✅ CDN 直接加载 | ❌ 需要申请下载 |
| 平台支持 | ✅ H5 | ✅ H5 |
| 功能 | 基础图表 | 完整功能 |

## 🚀 立即开始

### 已完成的工作

✅ 已创建 `LightweightChart` 组件  
✅ 已更新 `index.html` 加载 CDN  
✅ 已更新 `market/detail.vue` 使用新组件  
✅ **无需下载任何文件！**

### 第 1 步: 启动项目

```bash
# 启动 H5 开发服务器
npm run dev:h5
```

### 第 2 步: 查看效果

1. 浏览器打开 `http://localhost:8080`
2. 导航到 **市场 → 详情页**
3. 查看实时 K 线图表

就这么简单！✨

## ✨ 特性

### 实时数据
- 通过 Binance WebSocket 获取实时价格
- 自动更新 K 线图
- 断线自动重连

### 历史数据
- 自动加载历史 K 线数据
- 支持多种时间周期
- 可配置数据条数

### 响应式设计
- 自动适配容器大小
- 支持暗色/亮色主题
- 流畅的交互体验

## 📝 基础用法

```vue
<template>
  <LightweightChart 
    symbol="BTCUSDT"
    interval="1m"
    theme="dark"
    :height="500"
    @price-update="onPriceUpdate"
  />
</template>

<script>
import LightweightChart from '@/components/LightweightChart/LightweightChart.vue';

export default {
  components: {
    LightweightChart
  },
  methods: {
    onPriceUpdate(price) {
      console.log('当前价格:', price);
    }
  }
}
</script>
```

## 🎯 常用配置

### 切换交易对

```vue
<LightweightChart symbol="ETHUSDT" />
```

支持的交易对：
- `BTCUSDT` - 比特币
- `ETHUSDT` - 以太坊
- `BNBUSDT` - BNB
- `SOLUSDT` - Solana
- 等等...

### 切换时间周期

```vue
<LightweightChart interval="5m" />
```

支持的周期：
- `1m` - 1 分钟
- `5m` - 5 分钟
- `15m` - 15 分钟
- `1h` - 1 小时
- `4h` - 4 小时
- `1d` - 1 天

### 切换主题

```vue
<LightweightChart theme="light" />
```

## 🔧 高级功能

### 添加价格线

```vue
<template>
  <LightweightChart ref="chart" />
</template>

<script>
export default {
  mounted() {
    this.$refs.chart.addPriceLine(50000, {
      color: '#2962ff',
      title: '目标价格'
    });
  }
}
</script>
```

### 添加买入标记

```vue
<script>
export default {
  methods: {
    addBuyMarker() {
      const time = Math.floor(Date.now() / 1000);
      this.$refs.chart.addMarker(time, {
        position: 'belowBar',
        color: '#26a69a',
        shape: 'arrowUp',
        text: 'B'
      });
    }
  }
}
</script>
```

### 获取实时价格

```vue
<script>
export default {
  methods: {
    getPrice() {
      const price = this.$refs.chart.getCurrentPrice();
      console.log('当前价格:', price);
    }
  }
}
</script>
```

## 📱 平台支持

### ✅ H5 (浏览器)
完全支持，所有功能可用

### ❌ 小程序/App
不支持（会显示提示信息）

**原因**: Lightweight Charts 基于 Canvas 和 DOM，只能在浏览器环境运行。

## 🆚 与旧方案对比

### 旧方案 (TradingView Charting Library)
- ❌ 需要商业许可证
- ❌ 需要下载 50MB 文件
- ❌ 需要申请才能获取
- ✅ 功能强大完整

### 新方案 (Lightweight Charts)
- ✅ 完全免费开源
- ✅ CDN 直接加载
- ✅ 无需申请
- ✅ 满足基本需求

## 📚 更多文档

- **组件文档**: `components/LightweightChart/README.md`
- **官方文档**: https://tradingview.github.io/lightweight-charts/
- **Binance API**: https://binance-docs.github.io/apidocs/

## ❓ 常见问题

### Q: 需要下载文件吗？
**A**: 不需要！通过 CDN 自动加载。

### Q: 需要许可证吗？
**A**: 不需要！完全免费开源 (MIT 许可证)。

### Q: 在小程序能用吗？
**A**: 不能。只支持 H5 (浏览器) 环境。

### Q: 数据从哪里来？
**A**: Binance API 和 WebSocket，实时更新。

### Q: 可以自定义样式吗？
**A**: 可以！支持主题、颜色等配置。

## 🎉 开始使用

现在就启动项目，查看效果吧！

```bash
npm run dev:h5
```

---

**更新时间**: 2025-11-10  
**版本**: Lightweight Charts 4.1.1
