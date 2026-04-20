import re
import requests
import os
import time

with open("ielts-speaking-part1-7band-training.html", "r", encoding="utf-8") as f:
    html = f.read()

# 超级强正则：提取所有 en:"..." 例句（支持转义引号）
examples = re.findall(r'en:"((?:[^"\\]|\\.)*?)"', html)

print(f"✅ 从 HTML 中提取到 {len(examples)} 个例句（应为 300）")

API_KEY = "6f9a947bae9d7af88b825d418f52faf3e8df3ec679e5f3fbd4929618c3c2734a"
VOICE_ID = "EXAVITQu4vr4xnSDxMaL"          # Bella - 当前最稳定自然女声
OUTPUT_DIR = "part1_audio"
os.makedirs(OUTPUT_DIR, exist_ok=True)

headers = {"Accept": "audio/mpeg", "Content-Type": "application/json", "xi-api-key": API_KEY}

idx = 0
success = 0
for p in range(1, 101):
    for e in range(1, 4):
        if idx >= len(examples):
            break
        text = examples[idx].replace('\\"', '"').replace("\\'", "'").replace("\\\\", "\\")
        filename = f"pat_{str(p).zfill(3)}_ex_{str(e).zfill(2)}.mp3"
        filepath = os.path.join(OUTPUT_DIR, filename)
        
        if os.path.exists(filepath):
            print(f"✅ 已存在 {filename}")
            idx += 1
            success += 1
            continue
            
        data = {
            "text": text,
            "model_id": "eleven_turbo_v2_5",
            "voice_settings": {"stability": 0.75, "similarity_boost": 0.85, "style": 0.1, "speed": 1.0}
        }
        
        try:
            response = requests.post(f"https://api.elevenlabs.io/v1/text-to-speech/{VOICE_ID}", 
                                   json=data, headers=headers, timeout=30)
            response.raise_for_status()
            
            with open(filepath, "wb") as f:
                f.write(response.content)
            
            print(f"✅ 生成 {filename}")
            success += 1
            time.sleep(0.7)
            
        except Exception as e:
            print(f"❌ 失败 {filename} → {e}")
            time.sleep(2)
        
        idx += 1

print(f"🎉 完成！共处理 {success} 个音频文件")
