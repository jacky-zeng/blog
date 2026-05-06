<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Helper\ResponseHelper;
use App\Model\Article;
use App\Model\ArticleView;
use App\Model\Category;
use App\Model\Comment;
use App\Model\Tag;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Contract\RequestInterface;
use App\Middleware\AuthMiddleware;
use Psr\Http\Message\ResponseInterface;

#[Controller]
#[Middleware(AuthMiddleware::class)]
class DashboardController
{
    #[Inject]
    protected RequestInterface $request;

    #[GetMapping(path: '/api/admin/dashboard')]
    public function stats(): ResponseInterface
    {
        $articleCount = Article::count();
        $categoryCount = Category::count();
        $commentCount = Comment::count();
        $viewCount = Article::sum('view_count');

        $categoryStats = Category::withCount(['articles' => function ($query) {
            $query->where('status', 1);
        }])->orderBy('articles_count', 'desc')->get();

        $hotArticles = Article::with('category')
            ->where('status', 1)
            ->orderBy('view_count', 'desc')
            ->limit(5)
            ->get();

        $recentArticles = Article::with('category')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return ResponseHelper::success([
            'stats' => [
                'total_articles' => $articleCount,
                'total_categories' => $categoryCount,
                'total_comments' => $commentCount,
                'total_views' => $viewCount,
            ],
            'category_stats' => $categoryStats,
            'hot_articles' => $hotArticles,
            'recent_articles' => $recentArticles,
        ]);
    }
}
