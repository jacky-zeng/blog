<?php

declare(strict_types=1);

$host = '127.0.0.1';
$port = 3306;
$database = 'Blog';
$username = 'root';
$password = '123456';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $hashedPassword = password_hash('admin123', PASSWORD_BCRYPT);
    $sql = "UPDATE users SET password = ? WHERE username = 'admin'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$hashedPassword]);

    echo "密码更新成功！\n";
    echo "影响的行数: " . $stmt->rowCount() . "\n";

} catch (PDOException $e) {
    echo "数据库连接失败: " . $e->getMessage() . "\n";
    exit(1);
}
