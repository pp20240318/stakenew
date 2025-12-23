# uni-app H5 集成 TradingView Charting Library 指南

## 项目信息
- 框架：uni-app (Vue 3)
- 目标平台：H5 (Web)
- TradingView 库：Charting Library

## 已完成的集成步骤

### 1. 创建 TradingViewChart 组件
**位置**: `components/TradingViewChart/TradingViewChart.vue`

**特性**:
- ✅ 使用 uni-app 条件编译 (`#ifdef H5`)，仅在 H5 端启用
- ✅ 非 H5 端显示友好提示信息
- ✅ 支持动态切换交易对和时间周期
- ✅ 暗色主题，自定义颜色（绿涨红跌）
- ✅ 完整的生命周期管理（创建/销毁）
- ✅ 错误处理和加载状态

### 2. 更新 index.html
**修改**: 动态加载 TradingView 脚本

```javascript
// 确保脚本按顺序加载
1. charting_library.standalone.js
2. datafeeds/udf/dist/bundle.js (在第一个加载完成后)
```

### 3. 更新 market/detail.vue
- 移除 iframe 实现
- 引入 TradingViewChart 组件
- 简化图表逻辑

### 4. 更新 .gitignore
- 排除大型库文件（charting_library 和 datafeeds）

## 完整集成步骤

### 步骤 1: 获取 TradingView Charting Library

#### 方法 A: 官方申请（推荐用于生产环境）
1. 访问 https://www.tradingview.com/HTML5-stock-forex-bitcoin-charting-library/
2. 填写申请表单
3. 获得访问权限后下载库文件

#### 方法 B: 从 react-typescript 项目复制（开发测试）
```bash
# Windows PowerShell
cd d:\code\stakenew\trade
.\copy_charting_library.bat

# 或者手动复制
xcopy /E /I /Y ..\react-typescript\public\charting_library .\static\charting_library
xcopy /E /I /Y ..\react-typescript\public\datafeeds .\static\datafeeds
```

### 步骤 2: 验证文件结构

确保以下文件存在：
```
trade/
  static/
    charting_library/
      charting_library.standalone.js  ← 必需
      charting_library.d.ts
      ...其他文件
    datafeeds/
      udf/
        dist/
          bundle.js  ← 必需
        ...其他文件
```

### 步骤 3: 配置数据源（重要！）

当前使用的是 TradingView 演示数据源，**生产环境必须更换**。

#### 选项 1: 使用 Binance API
编辑 `components/TradingViewChart/TradingViewChart.vue`:

```javascript
// 找到这一行
datafeed: new window.Datafeeds.UDFCompatibleDatafeed('https://demo_feed.tradingview.com')

// 替换为
datafeed: new window.Datafeeds.UDFCompatibleDatafeed('https://api.binance.com')
```

#### 选项 2: 自定义数据源
实现 UDF 兼容的后端 API，参考：
https://www.tradingview.com/charting-library-docs/latest/connecting_data/UDF

### 步骤 4: 运行项目

```bash
# 开发模式
npm run dev:h5

# 或
yarn dev:h5

# 构建生产版本
npm run build:h5
```

### 步骤 5: 访问测试

浏览器打开: `http://localhost:8080` (或您的开发服务器地址)

导航到: **市场 → 详情页** 查看 TradingView 图表

## 组件使用方法

### 基础用法

```vue
<template>
  <view>
    <TradingViewChart 
      :symbol="tradingSymbol"
      interval="D"
      theme="dark"
      :autosize="true"
      :height="500"
      @ready="onChartReady"
      @error="onChartError"
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
      tradingSymbol: 'BTCUSDT'
    }
  },
  methods: {
    onChartReady(widget) {
      console.log('图表加载完成', widget);
    },
    onChartError(error) {
      console.error('图表加载错误', error);
      uni.showToast({
        title: '图表加载失败',
        icon: 'none'
      });
    }
  }
}
</script>
```

### Props 参数

| 参数 | 类型 | 默认值 | 说明 |
|------|------|--------|------|
| symbol | String | 'BTCUSDT' | 交易对符号（如 BTCUSDT, ETHUSDT） |
| interval | String | 'D' | 时间周期：1, 5, 15, 30, 60, D, W, M |
| theme | String | 'dark' | 主题：dark 或 light |
| autosize | Boolean | true | 自动调整大小 |
| height | Number | 500 | 图表高度（像素） |

### Events 事件

| 事件名 | 参数 | 说明 |
|--------|------|------|
| ready | widget | 图表初始化完成，返回 widget 实例 |
| error | error | 图表加载错误 |

### 动态更新交易对

```javascript
// 在 data 中定义
data() {
  return {
    tradingSymbol: 'BTCUSDT'
  }
}

// 直接修改即可自动更新图表
this.tradingSymbol = 'ETHUSDT';
```

## 自定义配置

### 修改颜色方案

编辑 `components/TradingViewChart/TradingViewChart.vue` 中的 `overrides` 对象：

```javascript
overrides: {
  // 蜡烛图颜色
  'mainSeriesProperties.candleStyle.upColor': '#34C759',      // 上涨颜色
  'mainSeriesProperties.candleStyle.downColor': '#FF3B30',    // 下跌颜色
  
  // 背景颜色
  'paneProperties.background': '#1A1A1A',
  
  // 网格颜色
  'paneProperties.vertGridProperties.color': '#2C2C2C',
  'paneProperties.horzGridProperties.color': '#2C2C2C'
}
```

### 禁用/启用功能

```javascript
// 禁用的功能
disabled_features: [
  'use_localstorage_for_settings',  // 不使用本地存储
  'header_symbol_search',            // 禁用符号搜索
  'header_compare'                   // 禁用对比功能
]

// 启用的功能
enabled_features: [
  'study_templates'  // 启用指标模板
]
```

更多功能列表：https://www.tradingview.com/charting-library-docs/latest/customization/Featuresets

### 修改语言

```javascript
locale: 'zh',  // 中文
// 其他选项: 'en', 'ja', 'ko', 'ru', 'de', 'fr', 'es', 'it', 'pl', 'th', 'vi', 'tr', 'ar', 'he'
```

## 平台兼容性

### ✅ 支持的平台
- **H5 (Web)**: 完全支持

### ❌ 不支持的平台
- **微信小程序**: 不支持（会显示提示信息）
- **App (iOS/Android)**: 不支持（会显示提示信息）
- **其他小程序**: 不支持（会显示提示信息）

**原因**: TradingView Charting Library 是基于 DOM 的 JavaScript 库，只能在浏览器环境运行。

### 多端适配建议

如果需要在小程序或 App 端显示图表，可以：

1. **使用 web-view 组件**（推荐）
```vue
<!-- #ifndef H5 -->
<web-view :src="h5ChartUrl"></web-view>
<!-- #endif -->
```

2. **使用其他图表库**
   - uCharts (uni-app 原生图表)
   - ECharts (需要适配)

3. **使用图片快照**
   - 后端生成图表图片
   - 前端显示静态图片

## 故障排除

### 问题 1: 图表不显示

**检查清单**:
- [ ] 确认在 H5 端运行（浏览器）
- [ ] 检查浏览器控制台是否有错误
- [ ] 确认 `static/charting_library/` 文件夹存在
- [ ] 确认 `charting_library.standalone.js` 文件存在
- [ ] 检查网络请求是否成功加载脚本

**解决方法**:
```bash
# 重新复制库文件
.\copy_charting_library.bat

# 清除缓存重新运行
npm run dev:h5
```

### 问题 2: 提示 "TradingView library not loaded"

**原因**: 脚本未加载或加载失败

**解决方法**:
1. 打开浏览器开发者工具 (F12)
2. 查看 Network 标签
3. 确认以下文件加载成功：
   - `/static/charting_library/charting_library.standalone.js`
   - `/static/datafeeds/udf/dist/bundle.js`

### 问题 3: 数据不显示或一直加载

**原因**: 数据源配置问题

**解决方法**:
1. 检查 datafeed URL 是否正确
2. 确认数据源 API 可访问
3. 查看浏览器控制台的网络请求
4. 尝试使用演示数据源测试

### 问题 4: 图表样式异常

**原因**: CSS 冲突或容器尺寸问题

**解决方法**:
```css
/* 确保容器有明确的高度 */
.trading-view-wrapper {
  height: 500px;
  min-height: 500px;
}
```

## 性能优化建议

### 1. 懒加载组件
```javascript
// 使用异步组件
components: {
  TradingViewChart: () => import('@/components/TradingViewChart/TradingViewChart.vue')
}
```

### 2. 缓存配置
```javascript
// 在组件中添加缓存
export default {
  name: 'TradingViewChart',
  // 启用 keep-alive 缓存
  keepAlive: true
}
```

### 3. 按需加载
只在需要显示图表的页面引入组件，避免全局注册。

## 生产环境部署

### 1. 构建项目
```bash
npm run build:h5
```

### 2. 部署文件
将 `dist/build/h5/` 目录下的所有文件部署到 Web 服务器。

### 3. 确保静态资源可访问
```
your-domain.com/
  static/
    charting_library/  ← 必须可访问
    datafeeds/         ← 必须可访问
```

### 4. 配置 CORS（如果需要）
如果数据源在不同域名，需要配置 CORS 头。

## 许可证说明

⚠️ **重要**: TradingView Charting Library 需要商业许可证

- **开发/测试**: 可以免费使用
- **生产环境**: 需要购买许可证
- **详情**: https://www.tradingview.com/HTML5-stock-forex-bitcoin-charting-library/

## 参考资源

- [TradingView Charting Library 官方文档](https://www.tradingview.com/charting-library-docs/)
- [UDF 数据格式文档](https://www.tradingview.com/charting-library-docs/latest/connecting_data/UDF)
- [uni-app 条件编译文档](https://uniapp.dcloud.net.cn/tutorial/platform.html)
- [uni-app H5 开发文档](https://uniapp.dcloud.net.cn/tutorial/h5.html)

## 技术支持

如遇到问题：
1. 查看浏览器控制台错误信息
2. 检查本文档的故障排除部分
3. 参考 TradingView 官方文档
4. 检查 GitHub Issues: https://github.com/tradingview/charting_library/issues

---

**最后更新**: 2025-11-10
**适用版本**: uni-app Vue 3 + TradingView Charting Library
