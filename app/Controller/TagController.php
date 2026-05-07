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

        $tags = Tag::whereHas('articles', function ($query) {
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
        $tag = Tag::where('slug', $slug)
            ->withCount(['articles' => function ($query) {
                $query->where('status', 1);
            }])
            ->first();

        if (!$tag) {
            return ResponseHelper::error('标签不存在', 404);
        }

        $page = (int) $this->request->input('page', 1);
        $pageSize = (int) $this->request->input('page_size', 10);

        $articles = $tag->articles()
            ->with(['category', 'tags'])
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->paginate($pageSize, ['*'], 'page', $page);

        return ResponseHelper::success([
            'tag' => $tag,
            'articles' => $articles->toArray(),
        ]);
    }
}
