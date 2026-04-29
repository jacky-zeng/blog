<?php

declare(strict_types=1);

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique()->comment('用户名');
            $table->string('password', 255)->comment('密码（bcrypt加密）');
            $table->string('email', 100)->unique()->comment('邮箱');
            $table->string('nickname', 50)->nullable()->comment('昵称');
            $table->string('avatar', 255)->nullable()->comment('头像路径');
            $table->text('bio')->nullable()->comment('个人简介');
            $table->datetimes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
}
