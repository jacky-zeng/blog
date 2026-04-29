# 个人博客系统

基于 Hyperf (PHP 8.1+) + Vue 3.0 的前后端分离个人博客系统

## 项目结构

```
hyperf-skeleton/
├── app/                    # 后端应用目录
│   ├── Controller/          # 控制器
│   │   ├── Admin/          # 后台管理控制器
│   │   └── ...             # 前台展示控制器
│   ├── Model/              # 数据模型
│   ├── Middleware/         # 中间件
│   └── Helper/            # 辅助类
├── migrations/              # 数据库迁移文件
├── seeders/               # 数据填充文件
├── webVue/                # 前端Vue项目
│   ├── src/
│   │   ├── views/         # 页面组件
│   │   ├── layouts/       # 布局组件
│   │   ├── router/        # 路由配置
│   │   ├── stores/        # Pinia状态管理
│   │   └── utils/         # 工具函数
│   ├── package.json
│   └── vite.config.js
└── config/                # 配置文件
```

## 功能特性

### 后台管理系统
- 用户认证（登录/登出）
- Dashboard 数据统计
- 文章管理（CRUD）
- 分类管理
- 标签管理
- 评论管理（审核）
- 系统设置

### 前台展示系统
- 文章列表展示
- 文章详情页
- 分类页面
- 标签页面
- 文章归档
- 关于页面
- 评论功能
- 搜索功能

## 环境要求

- PHP >= 8.1
- Composer
- MySQL >= 5.7
- Redis
- Node.js >= 16
- npm 或 yarn

## 安装步骤

### 1. 后端安装

```bash
# 进入项目目录
cd /Users/zengyanqi/myProject/hyperf-skeleton

# 安装依赖
composer install

# 复制环境配置文件
cp .env.example .env

# 编辑 .env 文件，配置数据库连接
```

编辑 `.env` 文件：

```env
APP_NAME=blog
DB_DRIVER=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=blog
DB_USERNAME=root
DB_PASSWORD=your_password
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
REDIS_HOST=localhost
REDIS_PORT=6379
```

### 2. 数据库迁移

```bash
# 运行数据库迁移
php bin/hyperf.php migrate

# 填充初始数据
php bin/hyperf.php db:seed
```

### 3. 前端安装

```bash
# 进入前端目录
cd webVue

# 安装依赖
npm install

# 开发模式运行
npm run dev

# 生产构建
npm run build
```

## 启动项目

### 启动后端服务

```bash
# 在项目根目录
php bin/hyperf.php start

# 或者使用 Swoole
php bin/hyperf.php server
```

后端服务将运行在 `http://127.0.0.1:9501`

### 启动前端服务

```bash
# 在 webVue 目录
npm run dev
```

前端服务将运行在 `http://localhost:3000`

## 默认账号

- 用户名：`admin`
- 密码：`admin123`

## API接口文档

### 后台管理接口

#### 用户认证
- `POST /api/admin/login` - 用户登录
- `POST /api/admin/logout` - 用户登出
- `GET /api/admin/me` - 获取当前用户信息

#### Dashboard
- `GET /api/admin/dashboard` - 获取Dashboard数据

#### 文章管理
- `GET /api/admin/articles` - 获取文章列表
- `GET /api/admin/articles/{id}` - 获取文章详情
- `POST /api/admin/articles` - 创建文章
- `PUT /api/admin/articles/{id}` - 更新文章
- `DELETE /api/admin/articles/{id}` - 删除文章

#### 分类管理
- `GET /api/admin/categories` - 获取分类列表
- `POST /api/admin/categories` - 创建分类
- `PUT /api/admin/categories/{id}` - 更新分类
- `DELETE /api/admin/categories/{id}` - 删除分类

#### 标签管理
- `GET /api/admin/tags` - 获取标签列表
- `POST /api/admin/tags` - 创建标签
- `PUT /api/admin/tags/{id}` - 更新标签
- `DELETE /api/admin/tags/{id}` - 删除标签

#### 评论管理
- `GET /api/admin/comments` - 获取评论列表
- `PUT /api/admin/comments/{id}` - 更新评论状态
- `DELETE /api/admin/comments/{id}` - 删除评论

#### 系统设置
- `GET /api/admin/settings` - 获取系统设置
- `PUT /api/admin/settings` - 更新系统设置

### 前台展示接口

#### 文章
- `GET /api/articles` - 获取文章列表
- `GET /api/articles/{slug}` - 获取文章详情
- `GET /api/articles/{slug}/comments` - 获取文章评论
- `POST /api/articles/{slug}/comments` - 提交评论

#### 分类
- `GET /api/categories` - 获取分类列表
- `GET /api/categories/{slug}` - 获取分类详情及文章

#### 标签
- `GET /api/tags` - 获取标签列表
- `GET /api/tags/{slug}` - 获取标签详情及文章

#### 其他
- `GET /api/site/info` - 获取网站信息
- `GET /api/archive` - 获取文章归档
- `GET /api/search` - 搜索文章

## 数据库表结构

- `users` - 用户表
- `categories` - 分类表
- `tags` - 标签表
- `articles` - 文章表
- `article_tags` - 文章标签关联表
- `article_views` - 文章访问记录表
- `comments` - 评论表
- `settings` - 系统配置表

## 技术栈

### 后端
- Hyperf 3.1
- PHP 8.1+
- MySQL
- Redis
- Swoole

### 前端
- Vue 3.4
- Vue Router 4.2
- Pinia 2.1
- Element Plus 2.5
- Axios 1.6
- Vite 5.0

## 开发说明

### 后端开发
- 控制器位于 `app/Controller/` 目录
- 模型位于 `app/Model/` 目录
- 中间件位于 `app/Middleware/` 目录
- 使用注解方式定义路由

### 前端开发
- 页面组件位于 `webVue/src/views/` 目录
- 布局组件位于 `webVue/src/layouts/` 目录
- 使用 Vue Router 进行路由管理
- 使用 Pinia 进行状态管理
- 使用 Element Plus UI 组件库

## 注意事项

1. 确保 Redis 服务正常运行
2. 确保数据库连接配置正确
3. 前端开发时，Vite 已配置代理，API 请求会自动转发到后端
4. 生产环境部署时，需要修改前端 API 基础路径
5. 建议使用 HTTPS 部署生产环境

## 许可证

Apache-2.0
