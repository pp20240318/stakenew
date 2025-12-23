#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
JSON合并工具 - 修复版本
专门用于扁平JSON结构的键值对比和合并
"""

import json
import sys
import os

def compare_and_merge_flat_json(json1_file, json2_file, output1_file=None, output2_file=None, default_value=""):
    """比较两个扁平JSON文件，补全缺少的键值对"""
    try:
        # 读取两个JSON文件
        print(f"正在读取文件: {json1_file}")
        with open(json1_file, 'r', encoding='utf-8') as f:
            json1_data = json.load(f)
        
        print(f"正在读取文件: {json2_file}")
        with open(json2_file, 'r', encoding='utf-8') as f:
            json2_data = json.load(f)
        
        # 检查是否为字典类型
        if not isinstance(json1_data, dict) or not isinstance(json2_data, dict):
            print("❌ 错误: JSON文件必须是对象格式（字典）")
            return False
        
        # 获取所有键
        print("正在分析JSON结构...")
        keys1 = set(json1_data.keys())
        keys2 = set(json2_data.keys())
        
        # 找出差异
        missing_in_json1 = keys2 - keys1
        missing_in_json2 = keys1 - keys2
        
        print(f"\n🔍 键值对比分析结果:")
        print(f"   JSON1 ({json1_file}) 中的键数量: {len(keys1)}")
        print(f"   JSON2 ({json2_file}) 中的键数量: {len(keys2)}")
        print(f"   JSON1 中缺少的键: {len(missing_in_json1)}")
        print(f"   JSON2 中缺少的键: {len(missing_in_json2)}")
        
        # 创建副本用于修改
        modified_json1 = json1_data.copy()
        modified_json2 = json2_data.copy()
        
        # 向JSON1添加缺少的键
        if missing_in_json1:
            print(f"\n📝 向 {json1_file} 添加缺少的键:")
            for key in sorted(missing_in_json1)[:10]:  # 只显示前10个
                print(f"   + {key}")
                modified_json1[key] = default_value
            if len(missing_in_json1) > 10:
                print(f"   ... 还有 {len(missing_in_json1) - 10} 个键")
        
        # 向JSON2添加缺少的键
        if missing_in_json2:
            print(f"\n📝 向 {json2_file} 添加缺少的键:")
            for key in sorted(missing_in_json2)[:10]:  # 只显示前10个
                print(f"   + {key}")
                modified_json2[key] = default_value
            if len(missing_in_json2) > 10:
                print(f"   ... 还有 {len(missing_in_json2) - 10} 个键")
        
        # 确定输出文件名
        if output1_file is None:
            output1_file = json1_file
        if output2_file is None:
            output2_file = json2_file
        
        # 保存修改后的文件
        print(f"\n💾 保存修改后的文件...")
        
        if missing_in_json1:
            # 保持原有的格式，使用制表符缩进
            with open(output1_file, 'w', encoding='utf-8') as f:
                json.dump(modified_json1, f, ensure_ascii=False, indent='\t', separators=(',', ':'))
            print(f"   ✅ 已保存: {output1_file} (添加了 {len(missing_in_json1)} 个键)")
        else:
            print(f"   ℹ️  {json1_file} 无需修改")
        
        if missing_in_json2:
            # 保持原有的格式，使用制表符缩进
            with open(output2_file, 'w', encoding='utf-8') as f:
                json.dump(modified_json2, f, ensure_ascii=False, indent='\t', separators=(',', ':'))
            print(f"   ✅ 已保存: {output2_file} (添加了 {len(missing_in_json2)} 个键)")
        else:
            print(f"   ℹ️  {json2_file} 无需修改")
        
        print(f"\n🎉 JSON键值对比和合并完成！")
        if default_value:
            print(f"默认值设置为: \"{default_value}\"")
        else:
            print("默认值设置为: 空字符串")
        
        return True
        
    except FileNotFoundError as e:
        print(f"❌ 错误: 找不到文件 {e}")
        return False
    except json.JSONDecodeError as e:
        print(f"❌ JSON解析错误: {e}")
        return False
    except Exception as e:
        print(f"❌ 处理失败: {e}")
        return False

def main():
    print("🔧 JSON合并工具 - 修复版本")
    print("=" * 60)
    
    # 检查命令行参数
    if len(sys.argv) < 3:
        print("📖 用法:")
        print("  python json_merge_fixed.py <JSON文件1> <JSON文件2> [选项]")
        print("")
        print("选项:")
        print("  --output1 <文件>     指定第一个文件的输出路径")
        print("  --output2 <文件>     指定第二个文件的输出路径")
        print("  --default-value <值> 指定缺少键的默认值 (默认: 空字符串)")
        print("")
        print("💡 如果不指定输出文件，将直接修改原文件")
        print("")
        print("示例:")
        print("  python json_merge_fixed.py zh-Hans.json en.json")
        print("  python json_merge_fixed.py zh-Hans.json en.json --default-value 'TODO'")
        sys.exit(1)
    
    json1_file = sys.argv[1]
    json2_file = sys.argv[2]
    output1_file = None
    output2_file = None
    default_value = ""
    
    # 解析命令行参数
    i = 3
    while i < len(sys.argv):
        if sys.argv[i] == '--output1' and i + 1 < len(sys.argv):
            output1_file = sys.argv[i + 1]
            i += 1
        elif sys.argv[i] == '--output2' and i + 1 < len(sys.argv):
            output2_file = sys.argv[i + 1]
            i += 1
        elif sys.argv[i] == '--default-value' and i + 1 < len(sys.argv):
            default_value = sys.argv[i + 1]
            i += 1
        i += 1
    
    # 检查文件是否存在
    if not os.path.exists(json1_file):
        print(f"❌ 错误: 文件 {json1_file} 不存在")
        sys.exit(1)
    
    if not os.path.exists(json2_file):
        print(f"❌ 错误: 文件 {json2_file} 不存在")
        sys.exit(1)
    
    # 执行比较和合并
    success = compare_and_merge_flat_json(json1_file, json2_file, output1_file, output2_file, default_value)
    
    if not success:
        sys.exit(1)
    
    print("\n🎉 任务完成！")

if __name__ == "__main__":
    main() 