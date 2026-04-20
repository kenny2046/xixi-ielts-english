#!/bin/bash
echo "🔄 正在把 /var/www/IAGSv16/ 的最新修改同步到 GitHub 项目..."

# 把实际运行目录的修改同步到项目里的 website/
rsync -av --delete /var/www/IAGSv16/ ~/iagsv16-docker-stack/website/ \
  --exclude="visits*.txt" \
  --exclude="visit_log.json" \
  --exclude="visits_today_*.txt" \
  --exclude="*.log"

echo "✅ 网站文件同步完成！"

# 自动提交并推送到 GitHub
cd ~/iagsv16-docker-stack
git add .
git commit -m "Update website: $(date '+%Y-%m-%d %H:%M:%S')" || echo "没有新变化"
git push origin main

echo "🎉 全部同步到 GitHub 完成！"
echo "仓库地址：https://github.com/kenny2046/xixi-ielts-english"
