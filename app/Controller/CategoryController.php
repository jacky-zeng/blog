<?php

declare(strict_types=1);

namespace App\Controller;

use App\Helper\ResponseHelper;
use App\Model\Category;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Psr\Http\Message\ResponseInterface;

#[Controller]
class CategoryController
{
    #[GetMapping(path: '/api/categories')]
    public function index(): ResponseInterface
    {
        $categories = Category::withCount('articles')
            ->orderBy('sort_order', 'asc')
            ->get();

        return ResponseHelper::success($categories);
    }

    #[GetMapping(path: '/api/categories/{slug}')]
    public function show(string $slug): ResponseInterface
    {
        $category = Category::where('slug', $slug)->first();

        if (!$category) {
            return ResponseHelper::error('分类不存在', 404);
        }

        $page = (int) request()->input('page', 1);
        $pageSize = (int) request()->input('page_size', 10);

        $articles = $category->articles()
            ->with(['tags'])
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->paginate($pageSize, ['*'], 'page', $page);

        return ResponseHelper::success([
            'category' => $category,
            'articles' => $articles->toArray(),
        ]);
    }
}
