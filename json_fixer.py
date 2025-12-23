#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
JSON修复和处理工具
功能：
1. 修复JSON格式问题
2. 检测并删除重复的键值对
3. 将JSON文件中所有 : " 后面的第一个字母转换为大写
4. 比较两个JSON文件的键值差异，补全缺少的键值对
"""

import re
import sys
import os
import json
from collections import OrderedDict

def fix_json_format(content):
    """修复常见的JSON格式问题"""
    # 移除可能的BOM标记
    if content.startswith('\ufeff'):
        content = content[1:]
    
    # 修复可能的尾随逗号（在}和]前的逗号）
    content = re.sub(r',(\s*[}\]])', r'\1', content)
    
    # 确保所有的键都有双引号
    content = re.sub(r'(\s*)([a-zA-Z_][a-zA-Z0-9_]*)\s*:', r'\1"\2":', content)
    
    return content

def capitalize_json_values(content):
    """将JSON文件中所有 : " 后面的第一个字母转换为大写"""
    def replace_func(match):
        prefix = match.group(1)  # ": "
        first_char = match.group(2)  # 第一个字符
        rest = match.group(3)  # 剩余内容
        
        # 将第一个字符转为大写
        capitalized_char = first_char.upper()
        
        return prefix + capitalized_char + rest
    
    # 正则表达式匹配 : " 后面的内容
    pattern = r'(: ")([a-zA-ZÀ-ÿ])([^"]*")'
    result = re.sub(pattern, replace_func, content)
    
    return result

def remove_duplicate_keys_manual(content):
    """手动删除重复键（基于文本处理）"""
    lines = content.split('\n')
    seen_keys = set()
    duplicate_info = []
    result_lines = []
    
    for line_num, line in enumerate(lines, 1):
        # 匹配JSON键的模式
        key_match = re.search(r'\s*"([^"]+)"\s*:', line)
        
        if key_match:
            key = key_match.group(1)
            if key in seen_keys:
                # 发现重复键，记录信息但不添加到结果中
                duplicate_info.append({
                    'key': key,
                    'line': line_num,
                    'content': line.strip()
                })
                continue  # 跳过这一行
            else:
                seen_keys.add(key)
        
        result_lines.append(line)
    
    return '\n'.join(result_lines), duplicate_info

def count_capitalization_changes(original, modified):
    """统计首字母大写的修改次数"""
    pattern = r': "([a-zA-ZÀ-ÿ])'
    
    original_matches = re.findall(pattern, original)
    modified_matches = re.findall(pattern, modified)
    
    changes = 0
    for orig, mod in zip(original_matches, modified_matches):
        if orig != mod and orig.lower() == mod.lower():
            changes += 1
    
    return changes

def process_json_file(input_file, output_file=None, capitalize=True, remove_duplicates=True):
    """处理JSON文件"""
    if output_file is None:
        output_file = input_file
    
    try:
        # 读取文件
        print(f"正在读取文件: {input_file}")
        with open(input_file, 'r', encoding='utf-8') as f:
            original_content = f.read()
        
        processed_content = original_content
        
        # 修复JSON格式
        print("正在修复JSON格式...")
        processed_content = fix_json_format(processed_content)
        
        # 检测并删除重复键
        duplicate_info = []
        if remove_duplicates:
            print("正在检测重复键...")
            processed_content, duplicate_info = remove_duplicate_keys_manual(processed_content)
        
        # 处理首字母大写
        capitalization_changes = 0
        if capitalize:
            print("正在处理JSON值的首字母...")
            before_capitalize = processed_content
            processed_content = capitalize_json_values(processed_content)
            capitalization_changes = count_capitalization_changes(before_capitalize, processed_content)
        
        # 验证JSON格式
        try:
            json.loads(processed_content)
            print("✅ JSON格式验证通过")
        except json.JSONDecodeError as e:
            print(f"⚠️  JSON格式验证失败: {e}")
            print("继续保存，但可能需要手动修复...")
        
        # 保存文件
        print(f"正在保存到: {output_file}")
        with open(output_file, 'w', encoding='utf-8') as f:
            f.write(processed_content)
        
        print(f"\n✅ 处理完成！")
        
        # 显示处理结果
        if remove_duplicates:
            if duplicate_info:
                print(f"🔍 发现并删除了 {len(duplicate_info)} 个重复键:")
                for info in duplicate_info:
                    print(f"   重复键: '{info['key']}' (行 {info['line']})")
                    print(f"   内容: {info['content']}")
            else:
                print("🔍 未发现重复键")
        
        if capitalize:
            print(f"📊 总共修改了 {capitalization_changes} 个值的首字母")
            
            # 显示一些修改示例
            if capitalization_changes > 0:
                print("\n📝 首字母大写示例:")
                pattern = r': "([a-z])'
                examples = re.findall(pattern, original_content)[:5]
                for example in examples:
                    print(f"   : \"{example}...\" -> : \"{example.upper()}...\"")
        
        return True
        
    except FileNotFoundError:
        print(f"❌ 错误: 找不到文件 {input_file}")
        return False
    except Exception as e:
        print(f"❌ 处理失败: {e}")
        return False

def get_all_keys(obj, prefix=""):
    """递归获取JSON对象中的所有键路径"""
    keys = set()
    
    if isinstance(obj, dict):
        for key, value in obj.items():
            current_path = f"{prefix}.{key}" if prefix else key
            keys.add(current_path)
            
            # 递归处理嵌套对象
            if isinstance(value, dict):
                keys.update(get_all_keys(value, current_path))
            elif isinstance(value, list):
                # 处理数组中的对象
                for i, item in enumerate(value):
                    if isinstance(item, dict):
                        array_path = f"{current_path}[{i}]"
                        keys.update(get_all_keys(item, array_path))
    
    return keys

def set_nested_value(obj, key_path, value):
    """在嵌套字典中设置值"""
    keys = key_path.split('.')
    current = obj
    
    # 处理所有键除了最后一个
    for key in keys[:-1]:
        # 检查是否是数组索引
        if '[' in key and key.endswith(']'):
            array_key = key.split('[')[0]
            index = int(key.split('[')[1].split(']')[0])
            
            if array_key not in current:
                current[array_key] = []
            
            # 确保数组有足够的元素
            while len(current[array_key]) <= index:
                current[array_key].append({})
            
            current = current[array_key][index]
        else:
            if key not in current:
                current[key] = {}
            current = current[key]
    
    # 设置最后一个键的值
    final_key = keys[-1]
    if '[' in final_key and final_key.endswith(']'):
        array_key = final_key.split('[')[0]
        index = int(final_key.split('[')[1].split(']')[0])
        
        if array_key not in current:
            current[array_key] = []
        
        while len(current[array_key]) <= index:
            current[array_key].append({})
        
        current[array_key][index] = value
    else:
        current[final_key] = value

def compare_and_merge_json(json1_file, json2_file, output1_file=None, output2_file=None, default_value="aaaaaaaa"):
    """比较两个JSON文件，补全缺少的键值对"""
    try:
        # 读取两个JSON文件
        print(f"正在读取文件: {json1_file}")
        with open(json1_file, 'r', encoding='utf-8') as f:
            json1_content = f.read()
        
        print(f"正在读取文件: {json2_file}")
        with open(json2_file, 'r', encoding='utf-8') as f:
            json2_content = f.read()
        
        # 解析JSON
        try:
            json1_data = json.loads(json1_content)
            json2_data = json.loads(json2_content)
        except json.JSONDecodeError as e:
            print(f"❌ JSON解析错误: {e}")
            return False
        
        # 获取所有键路径
        print("正在分析JSON结构...")
        keys1 = get_all_keys(json1_data)
        keys2 = get_all_keys(json2_data)
        
        # 找出差异
        missing_in_json1 = keys2 - keys1
        missing_in_json2 = keys1 - keys2
        
        print(f"\n🔍 键值对比分析结果:")
        print(f"   JSON1 ({json1_file}) 中的键数量: {len(keys1)}")
        print(f"   JSON2 ({json2_file}) 中的键数量: {len(keys2)}")
        print(f"   JSON1 中缺少的键: {len(missing_in_json1)}")
        print(f"   JSON2 中缺少的键: {len(missing_in_json2)}")
        
        # 创建副本用于修改
        modified_json1 = json.loads(json.dumps(json1_data))
        modified_json2 = json.loads(json.dumps(json2_data))
        
        # 向JSON1添加缺少的键
        if missing_in_json1:
            print(f"\n📝 向 {json1_file} 添加缺少的键:")
            for key_path in sorted(missing_in_json1):
                print(f"   + {key_path}")
                set_nested_value(modified_json1, key_path, default_value)
        
        # 向JSON2添加缺少的键
        if missing_in_json2:
            print(f"\n📝 向 {json2_file} 添加缺少的键:")
            for key_path in sorted(missing_in_json2):
                print(f"   + {key_path}")
                set_nested_value(modified_json2, key_path, default_value)
        
        # 确定输出文件名
        if output1_file is None:
            output1_file = json1_file
        if output2_file is None:
            output2_file = json2_file
        
        # 保存修改后的文件
        print(f"\n💾 保存修改后的文件...")
        
        if missing_in_json1:
            with open(output1_file, 'w', encoding='utf-8') as f:
                json.dump(modified_json1, f, ensure_ascii=False, indent=2)
            print(f"   ✅ 已保存: {output1_file}")
        else:
            print(f"   ℹ️  {json1_file} 无需修改")
        
        if missing_in_json2:
            with open(output2_file, 'w', encoding='utf-8') as f:
                json.dump(modified_json2, f, ensure_ascii=False, indent=2)
            print(f"   ✅ 已保存: {output2_file}")
        else:
            print(f"   ℹ️  {json2_file} 无需修改")
        
        print(f"\n🎉 JSON键值对比和合并完成！")
        print(f"默认值设置为: \"{default_value}\"")
        
        return True
        
    except FileNotFoundError as e:
        print(f"❌ 错误: 找不到文件 {e}")
        return False
    except Exception as e:
        print(f"❌ 处理失败: {e}")
        return False

def main():
    print("🔧 JSON修复和处理工具")
    print("=" * 60)
    
    # 检查命令行参数
    if len(sys.argv) < 2:
        print("📖 用法:")
        print("  单文件处理: python json_fixer.py <JSON文件路径> [选项]")
        print("  双文件比较: python json_fixer.py --compare <JSON文件1> <JSON文件2> [选项]")
        print("")
        print("单文件处理选项:")
        print("  --no-capitalize      跳过首字母大写")
        print("  --no-dedup          跳过重复键检测")
        print("  --output <文件>      指定输出文件")
        print("")
        print("双文件比较选项:")
        print("  --output1 <文件>     指定第一个文件的输出路径")
        print("  --output2 <文件>     指定第二个文件的输出路径")
        print("  --default-value <值> 指定缺少键的默认值 (默认: aaaaaaaa)")
        print("")
        print("💡 如果不指定输出文件，将直接修改原文件")
        sys.exit(1)
    
    # 检查是否是比较模式
    if sys.argv[1] == '--compare':
        if len(sys.argv) < 4:
            print("❌ 错误: 比较模式需要两个JSON文件")
            print("用法: python json_fixer.py --compare <JSON文件1> <JSON文件2> [选项]")
            sys.exit(1)
        
        json1_file = sys.argv[2]
        json2_file = sys.argv[3]
        output1_file = None
        output2_file = None
        default_value = "aaaaaaaa"
        
        # 解析比较模式的参数
        i = 4
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
        success = compare_and_merge_json(json1_file, json2_file, output1_file, output2_file, default_value)
        
        if not success:
            sys.exit(1)
    
    else:
        # 原有的单文件处理模式
        input_file = sys.argv[1]
        output_file = None
        capitalize = True
        remove_duplicates = True
        
        # 解析命令行参数
        i = 2
        while i < len(sys.argv):
            if sys.argv[i] == '--no-capitalize':
                capitalize = False
            elif sys.argv[i] == '--no-dedup':
                remove_duplicates = False
            elif sys.argv[i] == '--output' and i + 1 < len(sys.argv):
                output_file = sys.argv[i + 1]
                i += 1
            i += 1
        
        # 检查文件是否存在
        if not os.path.exists(input_file):
            print(f"❌ 错误: 文件 {input_file} 不存在")
            sys.exit(1)
        
        # 处理文件
        success = process_json_file(input_file, output_file, capitalize, remove_duplicates)
        
        if not success:
            sys.exit(1)
    
    print("\n🎉 任务完成！")

if __name__ == "__main__":
    main()