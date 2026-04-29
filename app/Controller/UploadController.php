<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * @Controller
 */
class UploadController
{
    /**
     * @Inject
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @Inject
     * @var ResponseInterface
     */
    protected ResponseInterface $response;

    /**
     * @PostMapping(path="/api/upload")
     */
    public function upload()
    {
        $file = $this->request->file('file');

        if (!$file) {
            return $this->response->json([
                'code' => 400,
                'message' => '请选择要上传的文件',
                'data' => null,
            ]);
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file->getMimeType(), $allowedTypes)) {
            return $this->response->json([
                'code' => 400,
                'message' => '只允许上传 JPG、PNG、GIF、WebP 格式的图片',
                'data' => null,
            ]);
        }

        if ($file->getSize() > $maxSize) {
            return $this->response->json([
                'code' => 400,
                'message' => '图片大小不能超过 5MB',
                'data' => null,
            ]);
        }

        try {
            $uploadDir = BASE_PATH . '/public/storage/images/' . date('Ymd');
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $extension = $file->getExtension();
            $filename = uniqid() . '.' . $extension;
            $filepath = $uploadDir . '/' . $filename;

            move_uploaded_file($file->getPathname(), $filepath);

            $url = '/storage/images/' . date('Ymd') . '/' . $filename;

            return $this->response->json([
                'code' => 200,
                'message' => '上传成功',
                'data' => [
                    'url' => $url,
                    'filename' => $filename,
                ],
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'code' => 500,
                'message' => '上传失败：' . $e->getMessage(),
                'data' => null,
            ]);
        }
    }
}
