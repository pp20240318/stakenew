@echo off
REM Script to copy TradingView Charting Library files from react-typescript project
REM 从 react-typescript 项目复制 TradingView Charting Library 文件

echo Copying TradingView Charting Library files...
echo 正在复制 TradingView Charting Library 文件...
echo.

REM Source directory (react-typescript project)
set SOURCE_DIR=..\react-typescript\public

REM Target directory (trade project)
set TARGET_DIR=.\static

REM Check if source directory exists
if not exist "%SOURCE_DIR%" (
    echo Error: Source directory not found: %SOURCE_DIR%
    echo 错误：源目录不存在：%SOURCE_DIR%
    exit /b 1
)

REM Create target directory if it doesn't exist
if not exist "%TARGET_DIR%" mkdir "%TARGET_DIR%"

REM Copy charting_library folder
if exist "%SOURCE_DIR%\charting_library" (
    echo Copying charting_library...
    echo 正在复制 charting_library...
    xcopy /E /I /Y "%SOURCE_DIR%\charting_library" "%TARGET_DIR%\charting_library"
    echo ✓ charting_library copied successfully
    echo ✓ charting_library 复制成功
) else (
    echo Warning: charting_library folder not found in source
    echo 警告：源目录中未找到 charting_library 文件夹
)

echo.

REM Copy datafeeds folder
if exist "%SOURCE_DIR%\datafeeds" (
    echo Copying datafeeds...
    echo 正在复制 datafeeds...
    xcopy /E /I /Y "%SOURCE_DIR%\datafeeds" "%TARGET_DIR%\datafeeds"
    echo ✓ datafeeds copied successfully
    echo ✓ datafeeds 复制成功
) else (
    echo Warning: datafeeds folder not found in source
    echo 警告：源目录中未找到 datafeeds 文件夹
)

echo.
echo Done! Please check the following directories:
echo 完成！请检查以下目录：
echo   - %TARGET_DIR%\charting_library
echo   - %TARGET_DIR%\datafeeds
echo.
echo Note: These files are large and should be added to .gitignore
echo 注意：这些文件较大，应该添加到 .gitignore
echo.
pause
