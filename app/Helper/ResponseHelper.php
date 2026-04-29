<?php

declare(strict_types=1);

namespace App\Helper;

use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class ResponseHelper
{
    public static function success(mixed $data = null, string $message = 'success', int $code = 200): PsrResponseInterface
    {
        $response = [
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ];

        return (new \Hyperf\HttpServer\Response())->json($response);
    }

    public static function error(string $message = 'error', int $code = 400, mixed $data = null): PsrResponseInterface
    {
        $response = [
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ];

        return (new \Hyperf\HttpServer\Response())->json($response);
    }
}
