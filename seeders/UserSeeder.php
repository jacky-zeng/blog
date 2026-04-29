<?php

declare(strict_types=1);

namespace Hyperf\Database\Seeders;

use App\Model\User;
use Hyperf\Database\Seeder\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::query()->truncate();

        User::create([
            'username' => 'admin',
            'password' => password_hash('admin123', PASSWORD_BCRYPT),
            'email' => 'admin@example.com',
            'nickname' => '管理员',
            'bio' => '博客管理员',
        ]);
    }
}
