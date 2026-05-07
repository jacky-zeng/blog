<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Cache\SiteCacheKey;
use App\Helper\ResponseHelper;
use App\Helper\ValidatorHelper;
use App\Model\Category;
use Hyperf\Cache\Cache;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Contract\RequestInterface;
use App\Middleware\AuthMiddleware;
use Psr\Http\Message\ResponseInterface;

#[Controller]
#[Middleware(AuthMiddleware::class)]
class CategoryController
{
    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected Cache $cache;

    #[GetMapping(path: '/api/admin/categories')]
    public function index(): ResponseInterface
    {
        $categories = Category::select(['id', 'name', 'slug', 'sort_order', 'created_at'])
            ->withCount('articles')
            ->orderBy('sort_order', 'asc')
            ->get();

        return ResponseHelper::success($categories);
    }

    #[GetMapping(path: '/api/admin/categories/{id}')]
    public function show(int $id): ResponseInterface
    {
        $category = Category::with('articles')->find($id);

        if (!$category) {
            return ResponseHelper::error('分类不存在', 404);
        }

        return ResponseHelper::success($category);
    }

    #[PostMapping(path: '/api/admin/categories')]
    public function store(): ResponseInterface
    {
        $data = $this->request->all();

        $rules = [
            'name' => 'required|max:50',
        ];

        $validator = ValidatorHelper::make($data, $rules);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->first());
        }

        if (!isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = $this->generateSlug((string) $data['name']);
        }

        if (!isset($data['sort_order'])) {
            $data['sort_order'] = 0;
        }

        $category = Category::create($data);

        $this->cache->delete(SiteCacheKey::categories());

        return ResponseHelper::success($category, '创建成功');
    }

    #[PutMapping(path: '/api/admin/categories/{id}')]
    public function update(int $id): ResponseInterface
    {
        $category = Category::find($id);

        if (!$category) {
            return ResponseHelper::error('分类不存在', 404);
        }

        $data = $this->request->all();

        $rules = [
            'name' => 'sometimes|required|max:50',
        ];

        $validator = ValidatorHelper::make($data, $rules);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->first());
        }

        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = $this->generateSlug((string) $data['name']);
        }

        $category->update($data);

        $this->cache->delete(SiteCacheKey::categories());

        return ResponseHelper::success($category, '更新成功');
    }

    #[DeleteMapping(path: '/api/admin/categories/{id}')]
    public function destroy(int $id): ResponseInterface
    {
        $category = Category::withCount('articles')->find($id);

        if (!$category) {
            return ResponseHelper::error('分类不存在', 404);
        }

        if ($category->articles_count > 0) {
            return ResponseHelper::error('该分类下还有文章，无法删除');
        }

        $category->delete();

        $this->cache->delete(SiteCacheKey::categories());

        return ResponseHelper::success(null, '删除成功');
    }

    private function generateSlug(string $text): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text)));
        return $slug;
    }
}
