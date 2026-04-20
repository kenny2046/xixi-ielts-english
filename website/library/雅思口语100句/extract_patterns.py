import re
import json

with open("ielts-speaking-part1-7band-training.html", "r", encoding="utf-8") as f:
    content = f.read()

match = re.search(r'const patterns\s*=\s*(\[[\s\S]*?\]\s*);', content, re.DOTALL)
if not match:
    raise ValueError("❌ 无法找到 patterns 数据")

js = match.group(1).strip()

# === 第一步：保护所有单引号（防止 That's 变成 That"s）===
js = js.replace("'", "___APOS___")

# === 第二步：清理注释和格式 ===
js = re.sub(r'//.*$', '', js, flags=re.MULTILINE)
js = re.sub(r'/\*[\s\S]*?\*/', '', js)
js = re.sub(r',\s*([}\]])', r'\1', js)
js = re.sub(r'(?<!["\w])(\w+)(?=\s*:)', r'"\1"', js)   # 只给 key 加引号

with open("patterns_raw.json", "w", encoding="utf-8") as f:
    f.write(js)

data = json.loads(js)

# === 第三步：把占位符还原成单引号 ===
def restore_apos(obj):
    if isinstance(obj, dict):
        return {k: restore_apos(v) for k, v in obj.items()}
    elif isinstance(obj, list):
        return [restore_apos(item) for item in obj]
    elif isinstance(obj, str):
        return obj.replace("___APOS___", "'")
    else:
        return obj

data = restore_apos(data)

with open("patterns.py", "w", encoding="utf-8") as f:
    f.write("patterns = " + json.dumps(data, ensure_ascii=False, indent=2))

print(f"✅ 成功提取 {len(data)} 个句型 → patterns.py")
print("   （已保存 patterns_raw.json 供调试）")
