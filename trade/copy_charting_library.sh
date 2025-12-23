#!/bin/bash

# Script to copy TradingView Charting Library files from react-typescript project
# 从 react-typescript 项目复制 TradingView Charting Library 文件

echo "Copying TradingView Charting Library files..."
echo "正在复制 TradingView Charting Library 文件..."

# Source directory (react-typescript project)
SOURCE_DIR="../react-typescript/public"

# Target directory (trade project)
TARGET_DIR="./static"

# Check if source directory exists
if [ ! -d "$SOURCE_DIR" ]; then
    echo "Error: Source directory not found: $SOURCE_DIR"
    echo "错误：源目录不存在：$SOURCE_DIR"
    exit 1
fi

# Create target directory if it doesn't exist
mkdir -p "$TARGET_DIR"

# Copy charting_library folder
if [ -d "$SOURCE_DIR/charting_library" ]; then
    echo "Copying charting_library..."
    echo "正在复制 charting_library..."
    cp -r "$SOURCE_DIR/charting_library" "$TARGET_DIR/"
    echo "✓ charting_library copied successfully"
    echo "✓ charting_library 复制成功"
else
    echo "Warning: charting_library folder not found in source"
    echo "警告：源目录中未找到 charting_library 文件夹"
fi

# Copy datafeeds folder
if [ -d "$SOURCE_DIR/datafeeds" ]; then
    echo "Copying datafeeds..."
    echo "正在复制 datafeeds..."
    cp -r "$SOURCE_DIR/datafeeds" "$TARGET_DIR/"
    echo "✓ datafeeds copied successfully"
    echo "✓ datafeeds 复制成功"
else
    echo "Warning: datafeeds folder not found in source"
    echo "警告：源目录中未找到 datafeeds 文件夹"
fi

echo ""
echo "Done! Please check the following directories:"
echo "完成！请检查以下目录："
echo "  - $TARGET_DIR/charting_library"
echo "  - $TARGET_DIR/datafeeds"
echo ""
echo "Note: These files are large and should be added to .gitignore"
echo "注意：这些文件较大，应该添加到 .gitignore"
