-- 插入用户数据
INSERT INTO `users` (`username`, `password`, `email`, `nickname`, `bio`, `created_at`, `updated_at`)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com', '管理员', '博客管理员', NOW(), NOW());

-- 插入网站设置数据
INSERT INTO `settings` (`key`, `value`, `description`, `created_at`, `updated_at`) VALUES
('site_name', '我的博客', '网站名称', NOW(), NOW()),
('site_subtitle', '记录生活，分享技术', '网站副标题', NOW(), NOW()),
('logo', '', '网站Logo', NOW(), NOW()),
('icp', '', '备案号', NOW(), NOW()),
('author_name', '博主', '作者名称', NOW(), NOW()),
('author_bio', '热爱技术，热爱生活', '作者简介', NOW(), NOW()),
('social_links', '{}', '社交链接（JSON格式）', NOW(), NOW()),
('comment_enabled', '1', '是否开启评论', NOW(), NOW()),
('comment_audit', '1', '评论是否需要审核', NOW(), NOW());
