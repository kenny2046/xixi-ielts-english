#!/bin/bash

PROD_DIR="/var/www/IAGSv16"
GIT_DIR="$HOME/iagsv16-docker-stack/website"
REPO_URL="https://github.com/kenny2046/xixi-ielts-english.git"

clear
echo "========================================"
echo "   熙熙雅思英语 - 大一统管理工具"
echo "========================================"
echo "当前生产目录: $PROD_DIR"
echo "当前 GitHub 项目: $GIT_DIR"
echo ""

while true; do
    echo "请选择操作："
    echo "1. 正向同步（把你改好的网站同步到 GitHub）"
    echo "2. 反向同步（GitHub 项目同步回真实网站）【危险！会覆盖】"
    echo "3. 日常推送到 GitHub（只提交当前修改）"
    echo "4. 新服务器一键部署（在新服务器上运行）"
    echo "5. 退出"
    read -p "请输入数字 (1-5): " choice

    case $choice in
        1)
            echo "🔄 开始正向同步..."
            rsync -av --delete "$PROD_DIR/" "$GIT_DIR/" \
                --exclude="visits*.txt" --exclude="visit_log.json" \
                --exclude="visits_today_*.txt" --exclude="*.log"
            cd ~/iagsv16-docker-stack
            git add .
            git commit -m "Update website: $(date '+%Y-%m-%d %H:%M:%S')" || echo "没有新变化"
            git push origin main
            echo "✅ 正向同步完成！"
            ;;
        2)
            echo "⚠️  警告：反向同步会覆盖你当前真实网站的所有文件！"
            read -p "确定继续吗？(y/N): " confirm
            if [[ "$confirm" == "y" || "$confirm" == "Y" ]]; then
                rsync -av --delete "$GIT_DIR/" "$PROD_DIR/"
                echo "✅ 反向同步完成！建议重启服务：sudo systemctl restart nginx php8.3-fpm"
            else
                echo "❌ 已取消"
            fi
            ;;
        3)
            echo "🔄 正在推送到 GitHub..."
            cd ~/iagsv16-docker-stack
            git add .
            git commit -m "Update: $(date '+%Y-%m-%d %H:%M:%S')" || echo "没有新变化"
            git push origin main
            echo "✅ 已推送到 GitHub"
            ;;
        4)
            echo "🚀 开始新服务器一键部署..."
            apt update && apt upgrade -y
            curl -fsSL https://get.docker.com | sh
            systemctl enable --now docker
            apt install -y nginx php8.3-fpm php8.3-mysql php8.3-cli mysql-server
            cp -r nginx-config/* /etc/nginx/ 2>/dev/null || true
            cp -r website /var/www/IAGSv16 2>/dev/null || true
            docker compose up -d
            systemctl restart nginx php8.3-fpm mysql
            echo "🎉 新服务器部署完成！"
            ;;
        5)
            echo "👋 再见！"
            exit 0
            ;;
        *)
            echo "❌ 请输入 1-5 的数字"
            ;;
    esac
    echo ""
    read -p "按 Enter 键继续..."
    clear
done
