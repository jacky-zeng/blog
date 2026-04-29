<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Helper\ResponseHelper;
use App\Model\Article;
use App\Model\ArticleView;
use App\Model\Category;
use App\Model\Comment;
use App\Model\Tag;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\AuthMiddleware;
use Psr\Http\Message\ResponseInterface;

#[Controller]
#[Middleware(AuthMiddleware::class)]
class DashboardController
{
    #[GetMapping(path: '/api/admin/dashboard')]
    public function index(): ResponseInterface
    {
        $totalArticles = (int) Article::count();
        $publishedArticles = (int) Article::where('status', 1)->count();
        $draftArticles = (int) Article::where('status', 0)->count();
        $totalCategories = (int) Category::count();
        $totalTags = (int) Tag::count();
        $totalComments = (int) Comment::count();
        $pendingComments = (int) Comment::where('status', 0)->count();
        $totalViews = (int) Article::sum('view_count');

        $todayViews = (int) ArticleView::whereDate('created_at', date('Y-m-d'))->count();
        $todayArticles = (int) Article::whereDate('created_at', date('Y-m-d'))->count();

        $recentArticles = Article::with('category')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $hotArticles = Article::with('category')
            ->orderBy('view_count', 'desc')
            ->limit(5)
            ->get();

        $categoryStats = Category::withCount('articles')
            ->orderBy('articles_count', 'desc')
            ->get();

        return ResponseHelper::success([
            'stats' => [
                'total_articles' => $totalArticles,
                'published_articles' => $publishedArticles,
                'draft_articles' => $draftArticles,
                'total_categories' => $totalCategories,
                'total_tags' => $totalTags,
                'total_comments' => $totalComments,
                'pending_comments' => $pendingComments,
                'total_views' => $totalViews,
                'today_views' => $todayViews,
                'today_articles' => $todayArticles,
            ],
            'recent_articles' => $recentArticles,
            'hot_articles' => $hotArticles,
            'category_stats' => $categoryStats,
        ]);
    }
}
