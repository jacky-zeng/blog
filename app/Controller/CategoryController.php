<?php

declare(strict_types=1);

namespace App\Controller;

use App\Cache\SiteCacheKey;
use App\Helper\ResponseHelper;
use App\Model\Category;
use Hyperf\Cache\Cache;
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

    #[Inject]
    protected Cache $cache;

    #[GetMapping(path: '/api/categories')]
    public function index(): ResponseInterface
    {
        $cacheKey = SiteCacheKey::categories();
        
        $cachedCategories = $this->cache->get($cacheKey);
        if ($cachedCategories) {
            return ResponseHelper::success(unserialize($cachedCategories));
        }

        $categories = Category::select(['id', 'name', 'slug'])
            ->whereHas('articles', function ($query) {
                $query->where('status', 1);
            })
            ->withCount(['articles' => function ($query) {
                $query->where('status', 1);
            }])
            ->orderBy('sort_order', 'asc')
            ->get();

        $this->cache->set($cacheKey, serialize($categories), 1800);

        return ResponseHelper::success($categories);
    }

    #[GetMapping(path: '/api/category/{slug}')]
    public function show(string $slug): ResponseInterface
    {
        $category = Category::where('slug', $slug)
            ->withCount(['articles' => function ($query) {
                $query->where('status', 1);
            }])
            ->first();

        if (!$category) {
            return ResponseHelper::error('分类不存在', 404);
        }

        $page = (int) $this->request->input('page', 1);
        $pageSize = (int) $this->request->input('page_size', 10);

        $articles = $category->articles()
            ->with(['category:id,name,slug', 'tags:id,name,slug'])
            ->select(['id', 'title', 'slug', 'summary', 'content', 'created_at', 'category_id'])
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->paginate($pageSize, ['*'], 'page', $page);

        $articles->getCollection()->transform(function ($article) {
            $article->content = $this->stripHtmlAndTruncate((string) $article->content);
            return $article;
        });

        return ResponseHelper::success([
            'category' => $category,
            'articles' => $articles->toArray(),
        ]);
    }

    private function stripHtmlAndTruncate(string $content, int $length = 500): string
    {
        $content = strip_tags($content);
        $content = preg_replace('/\s+/', ' ', $content);
        $content = trim($content);
        
        if (mb_strlen($content) > $length) {
            return mb_substr($content, 0, $length) . '...';
        }
        
        return $content;
    }
}
