import json

# 读取两个JSON文件
with open('bot/locale/zh-Hans.json', 'r', encoding='utf-8') as f:
    zh_data = json.load(f)

with open('bot/locale/en.json', 'r', encoding='utf-8') as f:
    en_data = json.load(f)

print(f"zh-Hans.json 键数量: {len(zh_data)}")
print(f"en.json 键数量: {len(en_data)}")

# 检查空值
empty_values_en = [k for k, v in en_data.items() if v == ""]
empty_values_zh = [k for k, v in zh_data.items() if v == ""]

print(f"en.json 中的空值数量: {len(empty_values_en)}")
print(f"zh-Hans.json 中的空值数量: {len(empty_values_zh)}")

if empty_values_en:
    print("\nen.json 中的空值键 (前10个):")
    for i, key in enumerate(empty_values_en[:10]):
        print(f"  {i+1:2d}. {key}")

# 检查剩余差异
zh_keys = set(zh_data.keys())
en_keys = set(en_data.keys())
missing_in_en = zh_keys - en_keys
missing_in_zh = en_keys - zh_keys

print(f"\n剩余差异:")
print(f"en.json 仍缺少的键: {len(missing_in_en)}")
print(f"zh-Hans.json 仍缺少的键: {len(missing_in_zh)}")

if missing_in_en:
    print(f"\nen.json 仍缺少的键 (前5个):")
    for i, key in enumerate(sorted(missing_in_en)[:5]):
        print(f"  {i+1}. {key}") 