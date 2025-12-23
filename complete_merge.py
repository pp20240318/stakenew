#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
完整JSON合并脚本 - 循环执行直到完全同步
"""

import json
import sys
import os
from json_merge_fixed import compare_and_merge_flat_json

def complete_merge(json1_file, json2_file, max_iterations=20):
    """循环执行合并直到两个JSON文件完全同步"""
    print("🔄 开始完整JSON合并过程...")
    
    iteration = 0
    while iteration < max_iterations:
        iteration += 1
        print(f"\n=== 第 {iteration} 次迭代 ===")
        
        # 检查当前差异
        try:
            with open(json1_file, 'r', encoding='utf-8') as f:
                json1_data = json.load(f)
            with open(json2_file, 'r', encoding='utf-8') as f:
                json2_data = json.load(f)
            
            keys1 = set(json1_data.keys())
            keys2 = set(json2_data.keys())
            missing_in_json1 = keys2 - keys1
            missing_in_json2 = keys1 - keys2
            
            print(f"JSON1 键数量: {len(keys1)}")
            print(f"JSON2 键数量: {len(keys2)}")
            print(f"JSON1 中缺少: {len(missing_in_json1)}")
            print(f"JSON2 中缺少: {len(missing_in_json2)}")
            
            # 如果没有差异，则完成
            if len(missing_in_json1) == 0 and len(missing_in_json2) == 0:
                print("🎉 完全同步完成！两个JSON文件现在具有相同的键。")
                return True
            
            # 执行合并
            success = compare_and_merge_flat_json(json1_file, json2_file, default_value="")
            if not success:
                print(f"❌ 第 {iteration} 次迭代失败")
                return False
                
        except Exception as e:
            print(f"❌ 第 {iteration} 次迭代出错: {e}")
            return False
    
    print(f"⚠️  达到最大迭代次数 ({max_iterations})，可能需要手动检查")
    return False

def main():
    if len(sys.argv) < 3:
        print("用法: python complete_merge.py <JSON文件1> <JSON文件2>")
        sys.exit(1)
    
    json1_file = sys.argv[1]
    json2_file = sys.argv[2]
    
    if not os.path.exists(json1_file):
        print(f"❌ 文件不存在: {json1_file}")
        sys.exit(1)
    
    if not os.path.exists(json2_file):
        print(f"❌ 文件不存在: {json2_file}")
        sys.exit(1)
    
    success = complete_merge(json1_file, json2_file)
    
    if success:
        print("\n✅ 合并完成！")
    else:
        print("\n❌ 合并未完全完成，请检查日志")
        sys.exit(1)

if __name__ == "__main__":
    main() 