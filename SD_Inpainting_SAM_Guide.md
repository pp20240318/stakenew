# SD Inpainting + Segment Anything 使用指南

## 一、环境准备

### 1. 安装 Stable Diffusion WebUI

```bash
# 克隆仓库
git clone https://github.com/AUTOMATIC1111/stable-diffusion-webui
cd stable-diffusion-webui

# Windows 启动
webui-user.bat
```

### 2. 安装 Segment Anything 扩展

**方法一：通过 WebUI 界面安装**
1. 启动 WebUI 后，进入 `Extensions` 标签
2. 点击 `Available` 子标签
3. 点击 `Load from` 按钮加载扩展列表
4. 搜索 `segment anything` 或 `SAM`
5. 找到 `sd-webui-segment-anything` 并点击 `Install`
6. 安装完成后，点击 `Installed` 标签，点击 `Apply and restart UI`

**方法二：手动安装**
```bash
cd stable-diffusion-webui/extensions
git clone https://github.com/continue-revolution/sd-webui-segment-anything
```

### 3. 下载 SAM 模型

扩展会自动下载模型，或手动下载：
- 访问: https://github.com/facebookresearch/segment-anything
- 下载模型文件 (推荐 `sam_vit_h_4b8939.pth`)
- 放到 `stable-diffusion-webui/models/sam/` 目录

---

## 二、使用步骤

### **步骤 1: 进入 Inpainting 模式**

1. 启动 WebUI
2. 选择 `img2img` 标签
3. 选择 `Inpaint` 子标签

### **步骤 2: 上传图片**

- 将要编辑的图片拖入上传区域
- 或点击上传按钮选择图片

### **步骤 3: 使用 Segment Anything 创建精确 Mask**

1. 在图片下方找到 `Segment Anything` 区域
2. 点击 `Enable SAM` 启用
3. 选择分割模式：
   - **Point Mode (点击模式)**: 点击物体，自动识别边界
   - **Box Mode (框选模式)**: 拖拽框选物体
   - **Everything Mode (全部模式)**: 自动分割所有物体

4. **操作方式**：
   ```
   - 左键点击: 添加正向点 (包含区域)
   - 右键点击: 添加负向点 (排除区域)
   - 多次点击可精确调整选区
   ```

5. SAM 会自动生成精确的 mask (蒙版)
6. 点击 `Send to Inpaint` 将 mask 发送到 inpainting

### **步骤 4: 配置 Inpainting 参数**

```
Prompt: 描述你想要生成的内容
Negative Prompt: 不想要的内容

关键参数设置:
├─ Denoising strength: 0.75-0.95 (控制修改程度)
├─ Inpaint area: Only masked (只修改蒙版区域)
├─ Masked content: Original (保持原始内容作为参考)
└─ Mask blur: 4-8 (边缘羽化，使过渡自然)
```

### **步骤 5: 生成**

- 点击 `Generate` 按钮
- 等待生成完成
- 未被 mask 覆盖的区域会保持像素级不变

---

## 三、实际应用示例

### **示例 1: 删除物体**

```
1. 上传图片
2. 用 SAM 点击要删除的物体 (如：路人)
3. SAM 自动识别物体边界
4. Prompt: "empty space, natural background"
5. Generate
```

### **示例 2: 替换物体**

```
1. 上传图片
2. 用 SAM 选中要替换的物体 (如：苹果)
3. Prompt: "a red rose on the table"
4. Denoising: 0.85
5. Generate
```

### **示例 3: 添加物体**

```
1. 上传图片
2. 用 SAM Box Mode 框选空白区域
3. Prompt: "a cute cat sitting, realistic"
4. Generate
```

---

## 四、高级技巧

### **1. 多物体精确选择**

```
- 使用 Point Mode 多次点击
- 正向点 (左键): 包含想要的区域
- 负向点 (右键): 排除不想要的区域
- 逐步细化选区边界
```

### **2. 组合 ControlNet 提高一致性**

```
1. 启用 ControlNet 扩展
2. 使用原图作为 ControlNet 输入
3. 选择 Canny 或 Depth 模型
4. Control Weight: 0.8-1.0
5. 双重保证背景不变
```

### **3. 批量处理**

```
1. 使用 Batch 标签
2. 上传多张图片
3. 对每张图使用相同的 SAM 设置
4. 批量生成
```

---

## 五、常见问题

### **Q1: SAM 识别不准确怎么办？**
- 多次点击添加正负向点
- 切换到 Box Mode 手动框选
- 调整 SAM 模型 (尝试不同版本)

### **Q2: 边缘过渡不自然？**
- 增加 `Mask blur` 值 (8-16)
- 降低 `Denoising strength` (0.7-0.8)
- 使用 `Inpaint at full resolution` 选项

### **Q3: 背景还是有变化？**
- 确保选择 `Only masked`
- 使用 `Original` masked content
- 降低 Denoising strength
- 配合 ControlNet 使用

### **Q4: 生成结果不满意？**
- 优化 Prompt 描述
- 调整 CFG Scale (7-12)
- 多生成几次选最好的
- 使用 Hires fix 提高质量

---

## 六、推荐配置

### **最佳一致性配置**
```
Denoising strength: 0.85
Inpaint area: Only masked
Masked content: Original
Mask blur: 8
CFG Scale: 7
Steps: 30-50
Sampler: DPM++ 2M Karras
```

### **快速测试配置**
```
Denoising strength: 0.75
Steps: 20
Sampler: Euler a
其他保持默认
```

---

## 七、相关资源

- **WebUI 官方**: https://github.com/AUTOMATIC1111/stable-diffusion-webui
- **SAM 扩展**: https://github.com/continue-revolution/sd-webui-segment-anything
- **SAM 原项目**: https://github.com/facebookresearch/segment-anything
- **教程视频**: 搜索 "Stable Diffusion Segment Anything tutorial"

---

这个组合是目前**最精确、最可控**的图像局部编辑方案，完全免费且可以无限使用。
