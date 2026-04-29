<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Helper\ResponseHelper;
use App\Model\Setting;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Contract\RequestInterface;
use App\Middleware\AuthMiddleware;
use Psr\Http\Message\ResponseInterface;

#[Controller]
#[Middleware(AuthMiddleware::class)]
class SettingController
{
    #[Inject]
    protected RequestInterface $request;

    #[GetMapping(path: '/api/admin/settings')]
    public function index(): ResponseInterface
    {
        $settings = Setting::all()->pluck('value', 'key');

        return ResponseHelper::success($settings);
    }

    #[PutMapping(path: '/api/admin/settings')]
    public function update(): ResponseInterface
    {
        $data = $this->request->all();

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => (string) $key],
                ['value' => (string) $value]
            );
        }

        $settings = Setting::all()->pluck('value', 'key');

        return ResponseHelper::success($settings, '更新成功');
    }
}
