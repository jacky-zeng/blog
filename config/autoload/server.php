<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
use Hyperf\Server\Event;
use Hyperf\Server\Server;
use Swoole\Constant;
use function Hyperf\Support\env;

if (env('APP_ENV', 'dev') === 'product') {
    return [
        // 生产环境建议用 SWOOLE_PROCESS 更稳定
        'mode' => SWOOLE_PROCESS,

        'servers' => [
            [
                'name' => 'http',
                'type' => Server::SERVER_HTTP,
                'host' => '0.0.0.0',
                'port' => 9501,
                'sock_type' => SWOOLE_SOCK_TCP,
                'callbacks' => [
                    Event::ON_REQUEST => [Hyperf\HttpServer\Server::class, 'onRequest'],
                ],
                'options' => [
                    // Whether to enable request lifecycle event
                    'enable_request_lifecycle' => false,
                ],
            ],
        ],

        'settings' => [
            // 静态资源：生产环境关闭，交给Nginx处理
            'enable_static_handler' => false,
            'document_root' => BASE_PATH . '/public',

            // ========== 2核2GB 专用优化配置 ==========
            Constant::OPTION_DAEMONIZE => true, 
            Constant::OPTION_WORKER_NUM => 4,          // 2核 → 推荐 4 进程（最稳）
            Constant::OPTION_MAX_REQUEST => 50000,      // 防内存泄漏（2核够用）
            Constant::OPTION_ENABLE_COROUTINE => true, // 必须开启协程
            Constant::OPTION_MAX_CONNECTION => 5000,   // 2核2GB 推荐 5000
            Constant::OPTION_PACKAGE_MAX_LENGTH => 10 * 1024 * 1024, // 10MB 上传
            Constant::OPTION_LOG_LEVEL => SWOOLE_LOG_WARNING, // 日志级别
            Constant::OPTION_LOG_FILE => BASE_PATH . '/runtime/logs/swoole.log',
            Constant::OPTION_BUFFER_OUTPUT_SIZE => 2 * 1024 * 1024, // 输出缓冲区
        ],
        'callbacks' => [
            Event::ON_WORKER_START => [Hyperf\Framework\Bootstrap\WorkerStartCallback::class, 'onWorkerStart'],
            Event::ON_PIPE_MESSAGE => [Hyperf\Framework\Bootstrap\PipeMessageCallback::class, 'onPipeMessage'],
            Event::ON_WORKER_EXIT => [Hyperf\Framework\Bootstrap\WorkerExitCallback::class, 'onWorkerExit'],
        ],
    ];
} else {
    return [
        'mode' => SWOOLE_BASE,
        'servers' => [
            [
                'name' => 'http',
                'type' => Server::SERVER_HTTP,
                'host' => '0.0.0.0',
                'port' => 9501,
                'sock_type' => SWOOLE_SOCK_TCP,
                'callbacks' => [
                    Event::ON_REQUEST => [Hyperf\HttpServer\Server::class, 'onRequest'],
                ],
                'options' => [
                    // Whether to enable request lifecycle event
                    'enable_request_lifecycle' => false,
                ],
            ],
        ],
        'settings' => [
            'enable_static_handler' => true,
            'document_root' => BASE_PATH . '/public',
            'static_handler_locations' => ['/storage'],
            Constant::OPTION_ENABLE_COROUTINE => true,
            Constant::OPTION_WORKER_NUM => swoole_cpu_num(),
            Constant::OPTION_PID_FILE => BASE_PATH . '/runtime/hyperf.pid',
            Constant::OPTION_OPEN_TCP_NODELAY => true,
            Constant::OPTION_MAX_COROUTINE => 100000,
            Constant::OPTION_OPEN_HTTP2_PROTOCOL => true,
            Constant::OPTION_MAX_REQUEST => 100000,
            Constant::OPTION_SOCKET_BUFFER_SIZE => 2 * 1024 * 1024,
            Constant::OPTION_BUFFER_OUTPUT_SIZE => 2 * 1024 * 1024,
        ],
        'callbacks' => [
            Event::ON_WORKER_START => [Hyperf\Framework\Bootstrap\WorkerStartCallback::class, 'onWorkerStart'],
            Event::ON_PIPE_MESSAGE => [Hyperf\Framework\Bootstrap\PipeMessageCallback::class, 'onPipeMessage'],
            Event::ON_WORKER_EXIT => [Hyperf\Framework\Bootstrap\WorkerExitCallback::class, 'onWorkerExit'],
        ],
    ];
}
