# 首页所需图片资源清单

## 更新时间
2025年10月25日

## 图片资源列表

### 状态栏图标
- ✅ `signal.png` - 信号图标 (17x11px)
- ✅ `wifi.png` - WiFi图标 (17x11px)
- ✅ `battery.png` - 电池图标 (24x11px)

### 头部图标
- ✅ `logo.png` - 品牌Logo (25x34px)
- ✅ `notification.png` - 通知图标 (24x24px)

### 轮播横幅
- ✅ `banner-placeholder.png` - 横幅占位图 (670x320px)

### 快捷操作图标
- ✅ `promotion-icon.png` - 推广图标 (40x40px)
- ✅ `download-icon.png` - 下载图标 (32x32px)
- ✅ `intro-icon.png` - 介绍图标 (32x32px)
- ✅ `event-icon.png` - 活动图标 (75x75px)

### 主内容图标
- ✅ `speaker.png` - 喇叭图标 (24x24px)
- 🆕 `expand-icon.png` - 展开图标 (24x24px) **需要添加**

### 图表图标
- ✅ `chart-up.png` - 上涨图表 (60x30px)
- 🆕 `chart-down.png` - 下跌图表 (60x30px) **需要添加**

### 底部导航图标
- ✅ `home-active.png` - 首页激活 (48rpx)
- ✅ `trade.png` - 交易图标 (48rpx)
- ✅ `wallet.png` - 钱包图标 (48rpx)
- ✅ `home-indicator.png` - 底部指示器 (268x10rpx)

## 需要新增的图标

### 1. expand-icon.png (展开图标)
**用途**: Hot 标题旁边的展开/收起按钮
**尺寸**: 24x24px
**设计要求**:
- 向下箭头（展开状态）
- 可旋转180度变为向上箭头（收起状态）
- 颜色: rgba(237, 242, 247, 1)
- 背景: 透明

**SVG 代码示例**:
```svg
<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
  <path d="M7 10L12 15L17 10" stroke="#EDF2F7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
```

### 2. chart-down.png (下跌图表)
**用途**: 币种价格下跌时显示的图表图标
**尺寸**: 60x30px
**设计要求**:
- 红色下降曲线
- 颜色: rgba(255, 59, 48, 1)
- 背景: 透明
- 与 chart-up.png 风格一致

## 图标规范

### 尺寸标准
- **小图标**: 24x24px (状态栏、功能图标)
- **中图标**: 32-48px (快捷操作)
- **大图标**: 75x75px (活动卡片)
- **币种图标**: 40x40px (圆形)

### 颜色规范
- **主色调**: 白色 rgba(255, 255, 255, 1)
- **次要色**: 灰色 rgba(134, 134, 134, 1)
- **上涨色**: 绿色 rgba(0, 205, 136, 1)
- **下跌色**: 红色 rgba(255, 59, 48, 1)

### 格式要求
- **格式**: PNG (支持透明背景)
- **分辨率**: @2x 或 @3x (高清屏)
- **压缩**: 使用 TinyPNG 等工具压缩
- **命名**: 小写字母 + 连字符

## 币种图标（CSS 渐变实现）

以下币种图标使用 CSS 渐变，无需图片：

### BTC (比特币)
```css
background: linear-gradient(135deg, #f7931a 0%, #f7931a 100%);
```

### ETH (以太坊)
```css
background: linear-gradient(135deg, #627eea 0%, #627eea 100%);
```

### SUI (SuiCoin)
```css
background: linear-gradient(135deg, #4da2ff 0%, #4da2ff 100%);
```

### XRP (Ripple)
```css
background: linear-gradient(135deg, #23292f 0%, #23292f 100%);
```

### SOL (Solana)
```css
background: linear-gradient(135deg, #9945ff 0%, #14f195 100%);
```

### ADA (Cardano)
```css
background: linear-gradient(135deg, #0033ad 0%, #0033ad 100%);
```

## 图片优化建议

### 1. 压缩优化
- 使用 TinyPNG 压缩 PNG 图片
- 使用 ImageOptim 批量优化
- 目标: 减少 50-70% 文件大小

### 2. 格式选择
- **PNG**: 需要透明背景的图标
- **JPG**: 照片、横幅等
- **WebP**: 支持的平台优先使用
- **SVG**: 简单图标可使用矢量

### 3. 响应式图片
```
icon.png       (1x)
icon@2x.png    (2x)
icon@3x.png    (3x)
```

### 4. 懒加载
```vue
<image 
  :src="imageSrc" 
  lazy-load 
  mode="aspectFill"
/>
```

## 图片路径结构

```
static/
└── img/
    ├── signal.png
    ├── wifi.png
    ├── battery.png
    ├── logo.png
    ├── notification.png
    ├── banner-placeholder.png
    ├── promotion-icon.png
    ├── download-icon.png
    ├── intro-icon.png
    ├── event-icon.png
    ├── speaker.png
    ├── expand-icon.png          ← 需要添加
    ├── chart-up.png
    ├── chart-down.png            ← 需要添加
    ├── home-active.png
    ├── trade.png
    ├── wallet.png
    └── home-indicator.png
```

## 临时解决方案

在添加新图标之前，可以使用以下临时方案：

### 1. expand-icon.png
可以使用 Unicode 字符或 CSS 实现：
```vue
<text class="expand-icon">{{ isExpanded ? '▲' : '▼' }}</text>
```

### 2. chart-down.png
可以复用 chart-up.png 并添加 CSS 翻转：
```css
.chart-down {
  transform: scaleY(-1);
  filter: hue-rotate(180deg);
}
```

## 从 Lanhu 项目复制图标

如果需要从 LanhuProject 复制图标：

```bash
# 复制展开图标
Copy-Item "d:\code\stakenew\LanhuProject (1)\src\static\lanhu_2homedarkzhankai\SketchPng34d6bb9f915ea0f57d3b9019c7e6261e3510239d46e910e80809a200c39bba3e.png" "d:\code\stakenew\trade\static\img\expand-icon.png"

# 复制下跌图表
Copy-Item "d:\code\stakenew\LanhuProject (1)\src\static\lanhu_2homedarkzhankai\SketchPngc750d9e750354d07aab11cd14e5f678180de3e758e313ad25db47a83674b2bfc.png" "d:\code\stakenew\trade\static\img\chart-down.png"
```

## 检查清单

- [x] 状态栏图标完整
- [x] 头部图标完整
- [x] 快捷操作图标完整
- [ ] 展开图标 (expand-icon.png)
- [x] 上涨图表 (chart-up.png)
- [ ] 下跌图表 (chart-down.png)
- [x] 底部导航图标完整
- [x] 币种图标 (CSS 渐变)

## 总结

当前缺少的图标：
1. ❌ `expand-icon.png` - 展开/收起图标
2. ❌ `chart-down.png` - 下跌图表图标

建议从 LanhuProject 复制或使用临时 CSS 方案。
