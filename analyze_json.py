import json

# 读取两个JSON文件
with open('bot/locale/zh-Hans.json', 'r', encoding='utf-8') as f:
    zh_data = json.load(f)

with open('bot/locale/en.json', 'r', encoding='utf-8') as f:
    en_data = json.load(f)

# 分析键的差异
zh_keys = set(zh_data.keys())
en_keys = set(en_data.keys())

missing_in_en = zh_keys - en_keys
missing_in_zh = en_keys - zh_keys

print(f"zh-Hans.json 键数量: {len(zh_keys)}")
print(f"en.json 键数量: {len(en_keys)}")
print(f"en.json 中缺少的键数量: {len(missing_in_en)}")
print(f"zh-Hans.json 中缺少的键数量: {len(missing_in_zh)}")

if missing_in_en:
    print("\nen.json 中缺少的键 (前20个):")
    for i, key in enumerate(sorted(missing_in_en)[:20]):
        print(f"  {i+1:2d}. {key}")

if missing_in_zh:
    print("\nzh-Hans.json 中缺少的键 (前20个):")
    for i, key in enumerate(sorted(missing_in_zh)[:20]):
        print(f"  {i+1:2d}. {key}") 