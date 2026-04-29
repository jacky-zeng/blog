<?php

declare(strict_types=1);

namespace App\Seeder;

use Hyperf\Database\Seeders\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            \App\Seeder\UserSeeder::class,
            \App\Seeder\SettingSeeder::class,
        ]);
    }
}
