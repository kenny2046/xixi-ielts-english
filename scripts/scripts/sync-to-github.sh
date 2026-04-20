#!/bin/bash
cd "$(dirname "$0")/.."

echo "🔄 开始同步到 GitHub..."

# 清理临时敏感文件（防止上传访问记录等）
rm -f website/visits*.txt website/visit_log.json website/visits_today_*.txt 2>/dev/null || true

git add .
git commit -m "Update: $(date '+%Y-%m-%d %H:%M:%S')" || echo "没有新变化，跳过提交"
git push origin main

echo "✅ 同步完成！GitHub 已更新"
echo "仓库地址：https://github.com/kenny2046/iagsv16-docker-stack"
