<?php

declare(strict_types=1);

namespace App\Controller;

use App\Cache\SiteCacheKey;
use App\Helper\ResponseHelper;
use App\Model\Tag;
use Hyperf\Cache\Cache;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;

#[Controller]
class TagController
{
    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected Cache $cache;

    #[GetMapping(path: '/api/tags')]
    public function index(): ResponseInterface
    {
        $cacheKey = SiteCacheKey::tags();
        
        $cachedTags = $this->cache->get($cacheKey);
        if ($cachedTags) {
            return ResponseHelper::success(unserialize($cachedTags));
        }

        $tags = Tag::select(['id', 'name', 'slug'])
            ->whereHas('articles', function ($query) {
                $query->where('status', 1);
            })
            ->withCount(['articles' => function ($query) {
                $query->where('status', 1);
            }])
            ->get();

        $this->cache->set($cacheKey, serialize($tags), 1800);

        return ResponseHelper::success($tags);
    }

    #[GetMapping(path: '/api/tag/{slug}')]
    public function show(string $slug): ResponseInterface
    {
        $page = (int) $this->request->input('page', 1);
        $pageSize = (int) $this->request->input('page_size', 10);

        $page = max(1, min($page, 10));
        if (!in_array($pageSize, [5, 10])) {
            $pageSize = 10;
        }

        $cacheKey = "tag:articles:{$slug}:{$page}:{$pageSize}";
        
        $cachedData = $this->cache->get($cacheKey);
        if ($cachedData) {
            return ResponseHelper::success(unserialize($cachedData));
        }

        $tag = Tag::where('slug', $slug)
            ->withCount(['articles' => function ($query) {
                $query->where('status', 1);
            }])
            ->first();

        if (!$tag) {
            return ResponseHelper::error('标签不存在', 404);
        }

        $articles = $tag->articles()
            ->with(['category:id,name,slug', 'tags:id,name,slug'])
            ->select(['title', 'slug', 'summary', 'content', 'created_at', 'category_id'])
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->paginate($pageSize, ['*'], 'page', $page);

        $articles->getCollection()->transform(function ($article) {
            $article->content = $this->stripHtmlAndTruncate((string) $article->content);
            return $article;
        });

        $result = [
            'tag' => $tag,
            'articles' => $articles->toArray(),
        ];

        $this->cache->set($cacheKey, serialize($result), 600);

        return ResponseHelper::success($result);
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
