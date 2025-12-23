# TradingView Charting Library 集成说明

## 概述

本项目已将 market/detail 页面的 TradingView 图表从 iframe 方式升级为使用 TradingView Charting Library。

## 已完成的修改

### 1. 创建了 TradingViewChart 组件
- 位置: `components/TradingViewChart/TradingViewChart.vue`
- 功能: 封装 TradingView 图表库，提供可复用的 Vue 组件
- 特性:
  - 支持动态切换交易对 (symbol)
  - 支持动态切换时间周期 (interval)
  - 暗色主题配置
  - 自定义颜色方案（绿涨红跌）
  - 加载状态显示
  - 错误处理

### 2. 更新了 index.html
- 添加了 TradingView Charting Library 的脚本引用
- 添加了 UDF Datafeed 的脚本引用

### 3. 更新了 market/detail.vue
- 移除了 iframe 实现
- 引入并使用 TradingViewChart 组件
- 简化了图表相关的代码逻辑
- 移除了备用图表相关代码

## 需要完成的设置步骤

### 1. 获取 TradingView Charting Library

TradingView Charting Library 不是开源的，需要从 TradingView 获取：

1. 访问 https://www.tradingview.com/HTML5-stock-forex-bitcoin-charting-library/
2. 填写申请表单获取访问权限
3. 下载 charting_library 文件

### 2. 安装 Charting Library

将下载的文件放置到项目中：

```
trade/
  static/
    charting_library/          # 将 charting_library 文件夹放在这里
      charting_library.standalone.js
      ...其他文件
    datafeeds/                 # 将 datafeeds 文件夹放在这里
      udf/
        dist/
          bundle.js
        ...其他文件
```

### 3. 替代方案（用于开发测试）

如果暂时无法获取官方库，可以参考 react-typescript 项目中的设置：

```bash
# 从 react-typescript 项目复制文件
cp -r ../react-typescript/public/charting_library ./static/
cp -r ../react-typescript/public/datafeeds ./static/
```

### 4. 配置 Datafeed

当前使用的是 TradingView 的演示数据源：
```javascript
datafeed: new Datafeeds.UDFCompatibleDatafeed('https://demo_feed.tradingview.com')
```

**生产环境需要替换为实际的数据源：**

1. 实现自己的 UDF 兼容数据接口
2. 或者使用第三方数据提供商（如 Binance API）
3. 更新 TradingViewChart.vue 中的 datafeed 配置

示例（使用 Binance）:
```javascript
// 在 TradingViewChart.vue 中修改
datafeed: new Datafeeds.UDFCompatibleDatafeed('https://api.binance.com')
```

### 5. 自定义配置

可以在 `components/TradingViewChart/TradingViewChart.vue` 中调整以下配置：

- **主题颜色**: 修改 `overrides` 对象
- **禁用功能**: 修改 `disabled_features` 数组
- **启用功能**: 修改 `enabled_features` 数组
- **时区**: 修改 `timezone` 属性
- **语言**: 修改 `locale` 属性

## 组件使用方法

```vue
<template>
  <TradingViewChart 
    :symbol="tradingSymbol"
    interval="D"
    theme="dark"
    :autosize="true"
    :height="500"
    @ready="onChartReady"
    @error="onChartError"
  />
</template>

<script>
import TradingViewChart from '@/components/TradingViewChart/TradingViewChart.vue';

export default {
  components: {
    TradingViewChart
  },
  data() {
    return {
      tradingSymbol: 'BTCUSDT'
    }
  },
  methods: {
    onChartReady(widget) {
      console.log('Chart ready', widget);
    },
    onChartError(error) {
      console.error('Chart error', error);
    }
  }
}
</script>
```

## Props 说明

| Prop | 类型 | 默认值 | 说明 |
|------|------|--------|------|
| symbol | String | 'BTCUSDT' | 交易对符号 |
| interval | String | 'D' | 时间周期 (1, 5, 15, 30, 60, D, W, M) |
| theme | String | 'dark' | 主题 (dark/light) |
| autosize | Boolean | true | 自动调整大小 |
| height | Number | 500 | 图表高度（像素） |

## Events 说明

| Event | 参数 | 说明 |
|-------|------|------|
| ready | widget | 图表加载完成 |
| error | error | 图表加载错误 |

## 注意事项

1. **许可证**: TradingView Charting Library 需要商业许可证用于生产环境
2. **文件大小**: charting_library 文件夹较大（约 50MB），建议添加到 .gitignore
3. **浏览器兼容性**: 需要现代浏览器支持（Chrome, Firefox, Safari, Edge）
4. **数据源**: 演示数据源仅用于测试，生产环境必须使用真实数据源

## 故障排除

### 图表不显示
1. 检查浏览器控制台是否有错误
2. 确认 charting_library 文件已正确放置
3. 检查网络请求是否成功加载脚本

### 数据不更新
1. 检查 datafeed 配置是否正确
2. 确认数据源 API 可访问
3. 查看网络请求是否返回正确数据

### 样式问题
1. 检查 CSS 是否冲突
2. 调整容器高度和宽度
3. 检查主题配置

## 参考资源

- [TradingView Charting Library 文档](https://www.tradingview.com/charting-library-docs/)
- [UDF 数据格式说明](https://www.tradingview.com/charting-library-docs/latest/connecting_data/UDF)
- [React 示例项目](../react-typescript/)
