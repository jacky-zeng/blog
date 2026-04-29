<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Di\Annotation\Inject;

#[Controller]
class StorageController
{
    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected ResponseInterface $response;

    #[GetMapping(path: '/storage/{path:.+}')]
    public function serve(string $path)
    {
        $filePath = BASE_PATH . '/public/storage/' . $path;

        if (!file_exists($filePath)) {
            return $this->response->json([
                'code' => 404,
                'message' => 'File not found',
            ], 404);
        }

        $mime = $this->getMimeType($filePath);
        
        $content = file_get_contents($filePath);
        
        return $this->response->raw($content)->withHeader('Content-Type', $mime);
    }

    private function getMimeType(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'pdf' => 'application/pdf',
            'txt' => 'text/plain',
        ];
        
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}
