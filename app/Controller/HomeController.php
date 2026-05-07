<?php

declare(strict_types=1);

namespace App\Controller;

use App\Cache\SiteCacheKey;
use App\Helper\ResponseHelper;
use App\Model\Article;
use App\Model\ArticleView;
use App\Model\Category;
use App\Model\Setting;
use App\Model\Tag;
use Hyperf\Cache\Cache;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;

#[Controller]
class HomeController
{
    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected Cache $cache;

    #[GetMapping(path: '/api/site/info')]
    public function info(): ResponseInterface
    {
        $cacheKey = SiteCacheKey::siteInfo();
        
        $cachedInfo = $this->cache->get($cacheKey);
        if ($cachedInfo) {
            return ResponseHelper::success(unserialize($cachedInfo));
        }

        $settings = Setting::all()->pluck('value', 'key');

        $result = [
            'site_name' => $settings['site_name'] ?? '我的博客',
            'site_subtitle' => $settings['site_subtitle'] ?? '记录生活，分享技术',
            'logo' => $settings['logo'] ?? '',
            'icp' => $settings['icp'] ?? '',
            'author_name' => $settings['author_name'] ?? '博主',
            'author_bio' => $settings['author_bio'] ?? '',
            'social_links' => json_decode($settings['social_links'] ?? '{}', true),
        ];

        $this->cache->set($cacheKey, serialize($result), 7200);

        return ResponseHelper::success($result);
    }

    #[GetMapping(path: '/api/archive')]
    public function archive(): ResponseInterface
    {
        $articles = Article::select(['id', 'title', 'slug', 'created_at'])
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($item) {
                return date('Y-m', strtotime($item->created_at));
            });

        return ResponseHelper::success($articles);
    }

    #[GetMapping(path: '/api/stats')]
    public function stats(): ResponseInterface
    {
        $totalArticles = Article::where('status', 1)->count();
        $totalCategories = Category::whereHas('articles', function ($query) {
            $query->where('status', 1);
        })->count();
        $totalTags = Tag::whereHas('articles', function ($query) {
            $query->where('status', 1);
        })->count();
        $totalViews = Article::sum('view_count');

        return ResponseHelper::success([
            'total_articles' => $totalArticles,
            'total_categories' => $totalCategories,
            'total_tags' => $totalTags,
            'total_views' => $totalViews,
        ]);
    }

    #[GetMapping(path: '/api/settings')]
    public function settings(): ResponseInterface
    {
        $cacheKey = SiteCacheKey::settings();
        
        $cachedSettings = $this->cache->get($cacheKey);
        if ($cachedSettings) {
            return ResponseHelper::success(unserialize($cachedSettings));
        }

        $settings = Setting::all()->pluck('value', 'key');

        $result = [
            'comment_enabled' => $settings['comment_enabled'] ?? '1',
            'comment_audit' => $settings['comment_audit'] ?? '1',
        ];

        $this->cache->set($cacheKey, serialize($result), 1800);

        return ResponseHelper::success($result);
    }

    #[GetMapping(path: '/api/search')]
    public function search(): ResponseInterface
    {
        $keyword = (string) $this->request->input('keyword', '');
        $page = (int) $this->request->input('page', 1);
        $pageSize = (int) $this->request->input('page_size', 10);

        if (empty($keyword)) {
            return ResponseHelper::error('请输入搜索关键词');
        }

        $articles = Article::with(['category', 'tags'])
            ->where('status', 1)
            ->where(function ($query) use ($keyword) {
                $query->where('title', 'like', '%' . $keyword . '%')
                    ->orWhere('content', 'like', '%' . $keyword . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate($pageSize, ['*'], 'page', $page);

        return ResponseHelper::success([
            'keyword' => $keyword,
            'articles' => $articles->toArray(),
        ]);
    }
}
