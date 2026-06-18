# 后台线上部署说明

当前 `japantriptools.com` 前台部署在 GitHub Pages，属于静态站，不能运行 Laravel 后台、登录和数据库。后台建议单独部署为 `admin.japantriptools.com`，使用 Laravel + PostgreSQL/MySQL。

## 推荐架构

- 前台：继续使用 GitHub Pages，访问 `https://japantriptools.com`。
- 后台：部署 Laravel 容器，访问 `https://admin.japantriptools.com/admin`。
- 数据库：PostgreSQL 优先，也可以用 MySQL。
- 图片上传：测试期可以用服务器本地磁盘；正式运营建议接对象存储或选择带持久磁盘的主机。

## 已准备好的部署文件

- `Dockerfile`：生产容器构建文件。
- `.dockerignore`：避免把 `.env`、`vendor`、`node_modules`、本地数据库等上传到镜像。
- `docker/apache.conf`：Apache 站点配置，入口指向 `public`。
- `docker/entrypoint.sh`：启动时自动执行迁移、创建 storage link、缓存配置和视图。

## 必填环境变量

后台上线前必须设置：

```env
APP_NAME="Japan Trip Tools"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://admin.japantriptools.com
APP_KEY=base64:由平台或 artisan key:generate 生成

ADMIN_NAME="超级管理员"
ADMIN_EMAIL=你的管理员邮箱
ADMIN_PASSWORD=一个强密码

DB_CONNECTION=pgsql
DB_URL=postgresql://...

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
RUN_MIGRATIONS=true
RUN_SEEDERS=true
```

第一次部署可以设置 `RUN_SEEDERS=true` 来初始化角色、管理员和演示内容。确认后台可登录后，建议改成 `RUN_SEEDERS=false`，避免以后部署时重置演示内容字段。

## Render / Railway / Fly 的基本步骤

1. 新建 Web Service，连接 GitHub 仓库 `heroachao/jp-travel-portal`。
2. 设置应用根目录为 `platform`。
3. 选择 Docker 部署，使用 `platform/Dockerfile`。
4. 新建 PostgreSQL 数据库，把连接串填到 `DB_URL`。
5. 设置上面的生产环境变量。
6. 第一次部署完成后打开 `/admin`，确认登录正常。
7. 在域名管理里给 `admin.japantriptools.com` 添加平台提供的 CNAME 或 A 记录。
8. 等 HTTPS 证书签发后，再把后台地址正式用于运营。

## 安全注意

- 不要提交 `.env`、密码、Cookie、数据库连接串或 Google 账号信息。
- 生产环境如果没有设置 `ADMIN_PASSWORD`，Seeder 会拒绝创建默认管理员。
- 上线后立即关闭或限制不需要公开访问的调试信息，保持 `APP_DEBUG=false`。
- 后台先不要放在主域名根目录，建议用 `admin.japantriptools.com` 或平台临时域名测试。
