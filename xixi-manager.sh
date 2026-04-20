#!/bin/bash
PROD_DIR="/var/www/IAGSv16"
GIT_DIR="$HOME/iagsv16-docker-stack/website"
BACKUP_DIR="$HOME/iagsv16-docker-stack/backup"

clear
echo "========================================"
echo "   熙熙雅思英语 - 大一统管理工具"
echo "========================================"

while true; do
    echo ""
    echo "1. 正向同步（网站文件 → GitHub）"
    echo "2. 反向同步（GitHub → 真实网站）【危险】"
    echo "3. 日常推送到 GitHub"
    echo "4. 新服务器一键部署"
    echo "5. PDFDing 数据打包（上传到 GitHub）"
    echo "6. PDFDing 数据还原（从 GitHub 解压）"
    echo "7. 退出"
    read -p "请输入数字 (1-7): " choice

    case $choice in
        1)
            echo "🔄 正向同步网站文件..."
            rsync -av --delete "$PROD_DIR/" "$GIT_DIR/" --exclude="visits*.txt" --exclude="visit_log.json" --exclude="visits_today_*.txt" --exclude="*.log"
            cd ~/iagsv16-docker-stack
            git add .
            git commit -m "Update website: $(date '+%Y-%m-%d %H:%M:%S')" || echo "没有新变化"
            git push origin main
            ;;
        2)
            echo "⚠️ 警告：将覆盖真实网站文件！"
            read -p "确定继续？(y/N): " c
            [[ "$c" == "y" || "$c" == "Y" ]] && rsync -av --delete "$GIT_DIR/" "$PROD_DIR/"
            ;;
        3)
            cd ~/iagsv16-docker-stack
            git add .
            git commit -m "Update: $(date '+%Y-%m-%d %H:%M:%S')" || echo "没有新变化"
            git push origin main
            ;;
        4)
            echo "🚀 新服务器一键部署..."
            apt update && apt upgrade -y
            curl -fsSL https://get.docker.com | sh
            systemctl enable --now docker
            apt install -y nginx php8.3-fpm php8.3-mysql php8.3-cli mysql-server
            cp -r nginx-config/* /etc/nginx/ 2>/dev/null || true
            cp -r website /var/www/IAGSv16 2>/dev/null || true
            docker compose up -d
            systemctl restart nginx php8.3-fpm mysql
            echo "🎉 部署完成！"
            ;;
        5)
            echo "📦 正在打包 PDFDing 数据到 GitHub..."
            mkdir -p "$BACKUP_DIR"
            docker run --rm -v pdfding_media:/data -v "$BACKUP_DIR":/backup busybox tar czvf /backup/pdfding_media.tar.gz /data
            cd ~/iagsv16-docker-stack
            git add backup/pdfding_media.tar.gz
            git commit -m "PDF backup: $(date '+%Y-%m-%d %H:%M:%S')" || echo "没有新变化"
            git push origin main
            echo "✅ PDF 已打包并推送到 GitHub！"
            ;;
        6)
            echo "🔄 正在从 GitHub 还原 PDF 到 PDFDing..."
            cd ~/iagsv16-docker-stack
            docker run --rm -v pdfding_media:/data -v "$BACKUP_DIR":/backup busybox tar xzvf /backup/pdfding_media.tar.gz -C /data
            echo "✅ PDF 已还原完成！"
            ;;
        7)
            echo "👋 再见！"
            exit 0
            ;;
        *)
            echo "❌ 请输入 1-7 的数字"
            ;;
    esac
    echo ""
    read -p "按 Enter 键继续..."
    clear
done
