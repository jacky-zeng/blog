<?php

declare(strict_types=1);

namespace App\Controller;

use App\Helper\ResponseHelper;
use App\Model\Category;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;

#[Controller]
class CategoryController
{
    #[Inject]
    protected RequestInterface $request;

    #[GetMapping(path: '/api/categories')]
    public function index(): ResponseInterface
    {
        $categories = Category::whereHas('articles', function ($query) {
            $query->where('status', 1);
        })
            ->withCount(['articles' => function ($query) {
                $query->where('status', 1);
            }])
            ->orderBy('sort_order', 'asc')
            ->get();

        return ResponseHelper::success($categories);
    }

    #[GetMapping(path: '/api/category/{slug}')]
    public function show(string $slug): ResponseInterface
    {
        $category = Category::where('slug', $slug)->first();

        if (!$category) {
            return ResponseHelper::error('分类不存在', 404);
        }

        $page = (int) $this->request->input('page', 1);
        $pageSize = (int) $this->request->input('page_size', 10);

        $articles = $category->articles()
            ->with(['category', 'tags'])
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->paginate($pageSize, ['*'], 'page', $page);

        return ResponseHelper::success([
            'category' => $category,
            'articles' => $articles->toArray(),
        ]);
    }
}
