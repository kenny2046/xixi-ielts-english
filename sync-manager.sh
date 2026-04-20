#!/bin/bash

PROD_DIR="/var/www/IAGSv16"
GIT_DIR="$HOME/iagsv16-docker-stack/website"

echo "========================================"
echo "  熙熙雅思英语 - 同步管理工具"
echo "========================================"

while true; do
    echo ""
    echo "请选择操作："
    options=("1. 正向同步（生产目录 → GitHub 项目）"
             "2. 反向同步（GitHub 项目 → 生产目录）"
             "3. 退出")
    
    select opt in "${options[@]}"; do
        case $REPLY in
            1)
                echo "🔄 开始正向同步（从真实网站 → GitHub 项目）..."
                rsync -av --delete "$PROD_DIR/" "$GIT_DIR/" \
                    --exclude="visits*.txt" \
                    --exclude="visit_log.json" \
                    --exclude="visits_today_*.txt" \
                    --exclude="*.log"
                
                echo "✅ 文件同步完成！正在提交到 GitHub..."
                cd ~/iagsv16-docker-stack
                git add .
                git commit -m "Update website: $(date '+%Y-%m-%d %H:%M:%S')" || echo "没有新变化"
                git push origin main
                echo "🎉 正向同步已完成！"
                break
                ;;
            2)
                echo "⚠️  警告：反向同步会覆盖你当前真实网站的文件！"
                read -p "确定要继续吗？(y/N): " confirm
                if [[ "$confirm" == "y" || "$confirm" == "Y" ]]; then
                    echo "🔄 开始反向同步（从 GitHub 项目 → 真实网站）..."
                    rsync -av --delete "$GIT_DIR/" "$PROD_DIR/" \
                        --exclude="visits*.txt" \
                        --exclude="visit_log.json" \
                        --exclude="visits_today_*.txt" \
                        --exclude="*.log"
                    echo "✅ 反向同步完成！"
                    echo "🔧 建议重启 Nginx：sudo systemctl restart nginx"
                else
                    echo "❌ 已取消反向同步"
                fi
                break
                ;;
            3)
                echo "👋 已退出同步管理工具"
                exit 0
                ;;
            *)
                echo "❌ 请输入正确的选项（1-3）"
                ;;
        esac
    done
done
