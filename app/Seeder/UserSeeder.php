<?php

declare(strict_types=1);

namespace App\Seeder;

use App\Model\User;
use Hyperf\Database\Seeders\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
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
