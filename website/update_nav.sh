#!/bin/bash
cd /var/www/IAGSv16

# ==================== 主 Dashboard index.html ====================
cat > index.html << 'HTML'
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IAGSv16 雅思全能工厂</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@400;500;700&display=swap');
        body {font-family: 'Noto Sans SC', sans-serif; margin:0; background: linear-gradient(135deg, #0f172a, #1e2937); color:#e2e8f0;}
        .container {max-width:1280px; margin:40px auto; padding:0 20px;}
        h1 {text-align:center; color:#60a5fa; font-size:2.5rem;}
        .subtitle {text-align:center; color:#94a3b8;}
        .grid {display:grid; grid-template-columns:repeat(auto-fit, minmax(340px,1fr)); gap:24px; margin-top:40px;}
        .card {background:#1e2937; border-radius:20px; padding:28px; transition:all 0.3s;}
        .card:hover {transform:translateY(-8px);}
        .btn {background:#3b82f6; color:white; padding:14px 32px; border-radius:9999px; text-decoration:none; font-weight:600;}
        .btn:hover {background:#2563eb; transform:scale(1.05);}
        .upload-area {border:3px dashed #3b82f6; padding:30px; border-radius:20px; text-align:center; margin-top:30px;}
    </style>
</head>
<body>
    <div class="container">
        <h1>🎯 IAGSv16 雅思全能工厂</h1>
        <p class="subtitle">工厂工具 • 规范化上传 • 题库分类</p>

        <div class="grid">
            <!-- 工厂工具卡片（自动扫描） -->
        </div>

        <div class="upload-area">
            <h3>📤 上传新练习文件（自动雅思命名）</h3>
            <form id="uploadForm" enctype="multipart/form-data">
                <select name="skill" required style="margin:8px;padding:10px;width:220px;">
                    <option value="">选择技能</option>
                    <option value="listening">听力 Listening</option>
                    <option value="reading">阅读 Reading</option>
                    <option value="writing">写作 Writing</option>
                    <option value="speaking">口语 Speaking</option>
                    <option value="vocabulary">词汇 Vocabulary</option>
                </select>

                <select name="cambridge" required style="margin:8px;padding:10px;width:140px;">
                    <option value="">剑桥册</option>
                    <option value="4">C4</option><option value="5">C5</option><option value="6">C6</option>
                    <option value="7">C7</option><option value="8">C8</option><option value="9">C9</option>
                    <option value="10">C10</option><option value="11">C11</option><option value="12">C12</option>
                    <option value="13">C13</option><option value="14">C14</option><option value="15">C15</option>
                    <option value="16">C16</option><option value="17">C17</option><option value="18">C18</option>
                    <option value="19">C19</option><option value="20">C20</option>
                </select>

                <input type="number" name="test" placeholder="Test (1-4)" min="1" max="4" required style="margin:8px;padding:10px;width:100px;">

                <input type="number" name="part" placeholder="Section/Passage/Task (1-4)" min="1" max="4" style="margin:8px;padding:10px;width:160px;">

                <select name="type" style="margin:8px;padding:10px;width:160px;">
                    <option value="html">HTML 练习文件</option>
                    <option value="audio">听力音频文件</option>
                </select>

                <input type="file" name="file" id="fileInput" required style="margin:15px 0;display:block;margin-left:auto;margin-right:auto;">
                <button type="button" onclick="uploadFile()" class="btn">上传并自动命名</button>
            </form>
            <div id="status" style="margin-top:15px; font-size:0.95rem;"></div>
        </div>

        <div style="text-align:center; margin-top:40px;">
            <a href="tiku.html" class="btn" style="background:#10b981; font-size:1.1rem; padding:16px 40px;">📖 进入题库分类页面</a>
            <a href="tools/" class="btn" style="margin-left:20px;">🛠️ 查看所有工厂工具</a>
        </div>
    </div>

    <script>
        async function uploadFile() {
            const form = document.getElementById('uploadForm');
            const formData = new FormData(form);
            const status = document.getElementById('status');
            status.innerHTML = '上传中...';

            const res = await fetch('upload.php', {method:'POST', body:formData});
            const json = await res.json();
            status.innerHTML = json.success ? `✅ ${json.message}` : `❌ ${json.message}`;
            if (json.success) setTimeout(() => location.reload(), 1800);
        }

        // 自动显示工厂工具卡片
        fetch('tools/').then(r => r.text()).then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const links = [...doc.querySelectorAll('a[href$=".html"]')];
            const grid = document.querySelector('.grid');
            links.forEach(link => {
                const name = link.getAttribute('href').replace('.html','');
                const card = document.createElement('div');
                card.className = 'card';
                card.innerHTML = `<h3>${name}</h3><a href="tools/${link.getAttribute('href')}" class="btn" target="_blank">立即进入</a>`;
                grid.appendChild(card);
            });
        });
    </script>
</body>
</html>
HTML

# ==================== 题库分类页面 tiku.html ====================
cat > tiku.html << 'TIKU'
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IAGSv16 题库分类</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@400;500;700&display=swap');
        body {font-family: 'Noto Sans SC', sans-serif; margin:0; background:#0f172a; color:#e2e8f0;}
        .container {max-width:1280px; margin:40px auto; padding:0 20px;}
        h1 {text-align:center; color:#60a5fa;}
        .filters {display:flex; gap:15px; flex-wrap:wrap; justify-content:center; margin:30px 0;}
        .filter-btn {padding:10px 22px; background:#1e2937; border-radius:9999px; cursor:pointer;}
        .filter-btn.active {background:#3b82f6;}
        .grid {display:grid; grid-template-columns:repeat(auto-fit, minmax(320px,1fr)); gap:20px;}
        .card {background:#1e2937; border-radius:16px; padding:20px;}
        .audio {color:#67e8f9;}
    </style>
</head>
<body>
    <div class="container">
        <h1>📖 IAGS 题库分类</h1>
        <div class="filters" id="filters"></div>
        <div class="grid" id="library-grid"></div>
    </div>

    <script>
        // 题库文件列表（update_nav.sh 会自动更新）
        let allFiles = [];
        function renderLibrary(filterSkill = '') {
            const grid = document.getElementById('library-grid');
            grid.innerHTML = '';
            allFiles.forEach(f => {
                if (filterSkill && !f.filename.includes(filterSkill)) return;
                const card = document.createElement('div');
                card.className = 'card';
                card.innerHTML = f.isAudio 
                    ? `<span class="audio">🎙️ ${f.filename}</span>` 
                    : `<a href="library/${f.filename}" target="_blank">${f.filename}</a>`;
                grid.appendChild(card);
            });
        }

        // 初始加载
        fetch('library/').then(r=>r.text()).then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const files = [...doc.querySelectorAll('a[href$=".html"], a[href$=".mp3"], a[href$=".wav"], a[href$=".m4a"]')];
            allFiles = files.map(link => ({
                filename: link.getAttribute('href'),
                isAudio: link.getAttribute('href').match(/\.(mp3|wav|m4a)$/)
            }));
            renderLibrary();
        });
    </script>
</body>
</html>
TIKU

chmod +x update_nav.sh
bash update_nav.sh

# 6. 更新 Nginx 配置（支持 PHP）
echo "⚙️ 更新 Nginx 配置..."
cat > /etc/nginx/sites-available/iagsv16 << 'NGINX'
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    server_name _;
    root /var/www/IAGSv16;
    index index.html index.php;

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~* \.(html|css|js|png|jpg|jpeg|gif|ico|svg|mp3|wav|m4a)$ {
        expires 1d;
        add_header Cache-Control "public, max-age=86400";
    }
}
NGINX

ln -sf /etc/nginx/sites-available/iagsv16 /etc/nginx/sites-enabled/
nginx -t && systemctl restart nginx

# 7. 权限设置
chown -R www-data:www-data /var/www/IAGSv16
chmod -R 755 /var/www/IAGSv16

echo ""
echo "✅ Patch3 部署完成！"
echo "🌐 主 Dashboard（带规范化上传）：http://156.239.235.235/"
echo "📖 题库分类页面：http://156.239.235.235/tiku.html"
echo "🛠️ 工厂工具：http://156.239.235.235/tools/"
echo "🎙️ 听力音频保存目录：/var/www/IAGSv16/audios/"
echo ""
echo "使用说明："
echo "   1. 打开 http://156.239.235.235/"
echo "   2. 在上传区域选择剑桥册、Test、技能、Section/Passage 等"
echo "   3. 上传文件后会自动重命名为标准雅思文件名并存入 library/"
echo "   4. 点击“进入题库分类页面”可按技能筛选"
echo "   5. 所有原有工厂工具位置和功能完全不变"
echo ""
echo "现在就可以开始规范化上传啦！"
