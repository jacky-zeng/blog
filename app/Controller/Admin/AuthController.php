<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Helper\ResponseHelper;
use App\Model\User;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Redis\Redis;
use Psr\Http\Message\ResponseInterface;

#[Controller]
class AuthController
{
    #[Inject]
    protected Redis $redis;

    #[Inject]
    protected RequestInterface $request;

    #[PostMapping(path: '/api/admin/login')]
    public function login(): ResponseInterface
    {
        $username = (string) $this->request->input('username');
        $password = (string) $this->request->input('password');

        if (!$username || !$password) {
            return ResponseHelper::error('用户名和密码不能为空');
        }

        $user = User::where('username', $username)->first();

        if (!$user) {
            return ResponseHelper::error('用户不存在');
        }

        if (!password_verify($password, $user->password)) {
            return ResponseHelper::error('密码错误');
        }

        $token = $this->generateToken();
        $this->redis->set('auth:token:' . $token, (string) $user->id, 86400 * 7);

        return ResponseHelper::success([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'nickname' => $user->nickname,
                'avatar' => $user->avatar,
                'bio' => $user->bio,
            ],
        ]);
    }

    #[PostMapping(path: '/api/admin/logout')]
    public function logout(): ResponseInterface
    {
        $token = $this->request->getHeaderLine('Authorization');
        $token = str_replace('Bearer ', '', $token);

        if ($token) {
            $this->redis->del('auth:token:' . $token);
        }

        return ResponseHelper::success(null, '退出成功');
    }

    #[RequestMapping(path: '/api/admin/me', methods: 'GET')]
    public function me(): ResponseInterface
    {
        $token = $this->request->getHeaderLine('Authorization');
        $token = str_replace('Bearer ', '', $token);

        $userId = $this->redis->get('auth:token:' . $token);

        if (!$userId) {
            return ResponseHelper::error('Token无效', 401);
        }

        $user = User::find((int) $userId);

        if (!$user) {
            return ResponseHelper::error('用户不存在', 401);
        }

        return ResponseHelper::success([
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'nickname' => $user->nickname,
            'avatar' => $user->avatar,
            'bio' => $user->bio,
        ]);
    }

    #[PostMapping(path: '/api/admin/change-password')]
    public function changePassword(): ResponseInterface
    {
        $token = $this->request->getHeaderLine('Authorization');
        $token = str_replace('Bearer ', '', $token);

        $userId = $this->redis->get('auth:token:' . $token);

        if (!$userId) {
            return ResponseHelper::error('Token无效', 401);
        }

        $user = User::find((int) $userId);

        if (!$user) {
            return ResponseHelper::error('用户不存在', 401);
        }

        $oldPassword = (string) $this->request->input('old_password');
        $newPassword = (string) $this->request->input('new_password');
        $confirmPassword = (string) $this->request->input('confirm_password');

        if (!$oldPassword || !$newPassword || !$confirmPassword) {
            return ResponseHelper::error('请填写完整信息');
        }

        if (!password_verify($oldPassword, $user->password)) {
            return ResponseHelper::error('原密码错误');
        }

        if ($newPassword !== $confirmPassword) {
            return ResponseHelper::error('两次输入的新密码不一致');
        }

        if (strlen($newPassword) < 6) {
            return ResponseHelper::error('新密码长度不能少于6位');
        }

        $user->password = password_hash($newPassword, PASSWORD_DEFAULT);
        $user->save();

        return ResponseHelper::success(null, '密码修改成功');
    }

    private function generateToken(): string
    {
        return md5(uniqid('blog_', true) . time());
    }
}
