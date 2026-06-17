<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Di\Annotation\Inject;

#[Controller]
class UploadController
{
    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected ResponseInterface $response;

    #[PostMapping(path: '/api/upload')]
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
            $uploadDir = BASE_PATH . '/public/storage/images/blog';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $extension = $file->getExtension();
            $filename = uniqid() . '.' . $extension;
            $filepath = $uploadDir . '/' . $filename;

            move_uploaded_file($file->getPathname(), $filepath);

            $url = '/storage/images/blog/' . $filename;

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

    #[PostMapping(path: '/api/upload/video/chunk')]
    public function uploadVideoChunk()
    {
        $file = $this->request->file('chunk');
        $fileHash = $this->request->input('fileHash');
        $chunkIndex = (int) $this->request->input('chunkIndex');
        $totalChunks = (int) $this->request->input('totalChunks');
        $filename = $this->request->input('filename');

        if (!$file || !$fileHash || $chunkIndex === null || $totalChunks === null || !$filename) {
            return $this->response->json([
                'code' => 400,
                'message' => '参数不完整',
                'data' => null,
            ]);
        }

        try {
            $chunkDir = BASE_PATH . '/public/storage/video/chunks/' . $fileHash;
            
            if (!is_dir($chunkDir)) {
                mkdir($chunkDir, 0755, true);
            }

            $chunkPath = $chunkDir . '/' . $chunkIndex . '.part';
            move_uploaded_file($file->getPathname(), $chunkPath);

            return $this->response->json([
                'code' => 200,
                'message' => '分片上传成功',
                'data' => [
                    'chunkIndex' => $chunkIndex,
                    'totalChunks' => $totalChunks,
                ],
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'code' => 500,
                'message' => '分片上传失败：' . $e->getMessage(),
                'data' => null,
            ]);
        }
    }

    #[PostMapping(path: '/api/upload/video/merge')]
    public function mergeVideoChunks()
    {
        $fileHash = $this->request->input('fileHash');
        $totalChunks = (int) $this->request->input('totalChunks');
        $filename = $this->request->input('filename');

        if (!$fileHash || $totalChunks === null || !$filename) {
            return $this->response->json([
                'code' => 400,
                'message' => '参数不完整',
                'data' => null,
            ]);
        }

        $chunkDir = BASE_PATH . '/public/storage/video/chunks/' . $fileHash;
        $videoDir = BASE_PATH . '/public/storage/video';

        if (!is_dir($chunkDir)) {
            return $this->response->json([
                'code' => 400,
                'message' => '分片目录不存在',
                'data' => null,
            ]);
        }

        if (!is_dir($videoDir)) {
            mkdir($videoDir, 0755, true);
        }

        try {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $allowedExtensions = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv'];
            
            if (!in_array(strtolower($extension), $allowedExtensions)) {
                $this->deleteDir($chunkDir);
                return $this->response->json([
                    'code' => 400,
                    'message' => '只允许上传 MP4、WebM、OGG、MOV、AVI、MKV 格式的视频',
                    'data' => null,
                ]);
            }
            
            $newFilename = uniqid('', true) . '.' . $extension;
            $outputPath = $videoDir . '/' . $newFilename;

            $output = fopen($outputPath, 'wb');

            for ($i = 0; $i < $totalChunks; $i++) {
                $chunkPath = $chunkDir . '/' . $i . '.part';
                
                if (!file_exists($chunkPath)) {
                    fclose($output);
                    $this->deleteDir($chunkDir);
                    return $this->response->json([
                        'code' => 400,
                        'message' => '分片 ' . $i . ' 不存在',
                        'data' => null,
                    ]);
                }

                $chunk = fopen($chunkPath, 'rb');
                stream_copy_to_stream($chunk, $output);
                fclose($chunk);
            }

            fclose($output);

            $this->deleteDir($chunkDir);

            $url = '/storage/video/' . $newFilename;

            return $this->response->json([
                'code' => 200,
                'message' => '视频合并成功',
                'data' => [
                    'url' => $url,
                    'filename' => $newFilename,
                ],
            ]);
        } catch (\Exception $e) {
            $this->deleteDir($chunkDir);
            return $this->response->json([
                'code' => 500,
                'message' => '视频合并失败：' . $e->getMessage(),
                'data' => null,
            ]);
        }
    }

    private function deleteDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDir($path) : unlink($path);
        }

        rmdir($dir);
    }
}
