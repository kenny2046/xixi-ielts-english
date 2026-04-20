# IAGSv16 + PDFDing 雅思学习平台

基于 **Ubuntu 24.04 + Nginx + PHP 8.3 + MySQL + Docker** 的完整雅思学习工具栈。

## ✨ 系统模块

### 1. IAGSv16 主站（PHP）
- 听力工厂、口语工厂、阅读工厂、写作工厂、词汇工厂
- 雅思真题库（Cambrige4 等）
- 音频自动切片器、听力材料上传、管理
- 用户登录、学习进度追踪、访问统计
- 工具箱（行列转置、PDF裁剪等）

### 2. PDFDing（Docker）
- 专业的 PDF 文档管理系统（上传、阅读、搜索、标注）

### 3. 后端服务
- **Nginx**：80/443 端口（反向代理）
- **PHP 8.3-FPM**
- **MySQL**：数据库 `iagsv17`

## 📸 截图

把你的截图放到 `docs/` 文件夹，然后在这里引用：
- ![网站首页](docs/homepage.png)
- ![PDFDing 界面](docs/pdfding.png)
- ![工具列表](docs/tools.png)

## 🚀 部署到新服务器

```bash
git clone https://github.com/kenny2046/iagsv16-docker-stack.git
cd iagsv16-docker-stack

cp .env.example .env          # 修改数据库密码和域名
docker compose up -d
