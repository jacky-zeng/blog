<?php

declare(strict_types=1);

namespace Hyperf\Database\Seeders;

use App\Model\Setting;
use Hyperf\Database\Seeder\Seeder;

class SettingSeeder extends Seeder
{
    public function run()
    {
        Setting::query()->truncate();

        $settings = [
            ['key' => 'site_name', 'value' => '我的博客', 'description' => '网站名称'],
            ['key' => 'site_subtitle', 'value' => '记录生活，分享技术', 'description' => '网站副标题'],
            ['key' => 'logo', 'value' => '', 'description' => '网站Logo'],
            ['key' => 'icp', 'value' => '', 'description' => '备案号'],
            ['key' => 'author_name', 'value' => '博主', 'description' => '作者名称'],
            ['key' => 'author_bio', 'value' => '热爱技术，热爱生活', 'description' => '作者简介'],
            ['key' => 'social_links', 'value' => '{}', 'description' => '社交链接（JSON格式）'],
            ['key' => 'comment_enabled', 'value' => '1', 'description' => '是否开启评论'],
            ['key' => 'comment_audit', 'value' => '1', 'description' => '评论是否需要审核'],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}
