<?php

declare(strict_types=1);

namespace App\Middleware;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware implements MiddlewareInterface
{
    #[Inject]
    protected ContainerInterface $container;

    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected HttpResponse $response;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $request->getHeaderLine('Authorization');

        if (empty($token)) {
            return $this->response->json([
                'code' => 401,
                'message' => '未授权访问',
                'data' => null,
            ], 401);
        }

        $token = str_replace('Bearer ', '', $token);

        if (!$this->validateToken($token)) {
            return $this->response->json([
                'code' => 401,
                'message' => 'Token无效或已过期',
                'data' => null,
            ], 401);
        }

        return $handler->handle($request);
    }

    private function validateToken(string $token): bool
    {
        $redis = $this->container->get(\Hyperf\Redis\Redis::class);
        $userId = $redis->get('auth:token:' . $token);

        return $userId !== false;
    }
}
