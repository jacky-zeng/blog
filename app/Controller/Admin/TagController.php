<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Helper\ResponseHelper;
use App\Model\Tag;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\AuthMiddleware;
use Psr\Http\Message\ResponseInterface;

#[Controller]
#[Middleware(AuthMiddleware::class)]
class TagController
{
    #[GetMapping(path: '/api/admin/tags')]
    public function index(): ResponseInterface
    {
        $tags = Tag::withCount('articles')->get();

        return ResponseHelper::success($tags);
    }

    #[PostMapping(path: '/api/admin/tags')]
    public function store(): ResponseInterface
    {
        $data = request()->all();

        $rules = [
            'name' => 'required|max:50',
        ];

        $validator = validator($data, $rules);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors()->first());
        }

        if (!isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = $this->generateSlug((string) $data['name']);
        }

        $tag = Tag::create($data);

        return ResponseHelper::success($tag, '创建成功');
    }

    #[PutMapping(path: '/api/admin/tags/{id}')]
    public function update(int $id): ResponseInterface
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return ResponseHelper::error('标签不存在', 404);
        }

        $data = request()->all();

        $rules = [
            'name' => 'sometimes|required|max:50',
        ];

        $validator = validator($data, $rules);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors()->first());
        }

        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = $this->generateSlug((string) $data['name']);
        }

        $tag->update($data);

        return ResponseHelper::success($tag, '更新成功');
    }

    #[DeleteMapping(path: '/api/admin/tags/{id}')]
    public function destroy(int $id): ResponseInterface
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return ResponseHelper::error('标签不存在', 404);
        }

        $tag->delete();

        return ResponseHelper::success(null, '删除成功');
    }

    private function generateSlug(string $text): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text)));
        return $slug;
    }
}
