cd ~/iagsv16-docker-stack

cat > README.md << 'EOL'
# 熙熙雅思英语

**一个双胞胎奶爸的中年逆袭故事**

大家好，我是一个双胞胎奶爸。  
人到中年，生活给了我巨大的危机感……直到我给自己定下一个新目标——**拿下雅思7分**！  
为了这个目标，我开发了听力工厂、口语工厂、阅读工厂、词汇工厂等一系列小工具。  
今天，我把整套系统完全开源，希望能帮助更多正在备考雅思的朋友。

**欢迎微信私信交流，一起学习、一起进步！**

<img src="docs/wechat-qr.png" width="260" alt="微信二维码">

---

## ✨ 系统模块

- **熙熙雅思主站**（PHP）：听力/口语/阅读/写作/词汇工厂 + 真题库 + 音频切片器
- **PDFDing**（Docker）：专业 PDF 文档管理系统
- **后端**：Nginx + PHP 8.3-FPM + MySQL

## 🚀 快速部署（新服务器一键部署）

在新服务器上执行以下 **3 条命令** 即可完成全部部署：

```bash
# 1. 克隆项目
git clone https://github.com/kenny2046/xixi-ielts-english.git
cd xixi-ielts-english

# 2. 运行大一统管理工具（推荐方式）
sudo ./xixi-manager.sh
