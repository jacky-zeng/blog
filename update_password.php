<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

$container = require_once __DIR__ . '/config/container.php';

$user = \App\Model\User::where('username', 'admin')->first();

if ($user) {
    $user->password = password_hash('admin123', PASSWORD_BCRYPT);
    $user->save();
    echo "密码更新成功！\n";
} else {
    echo "用户不存在！\n";
}
