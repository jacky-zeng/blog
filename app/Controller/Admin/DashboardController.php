<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Helper\ResponseHelper;
use App\Model\Article;
use App\Model\ArticleView;
use App\Model\Comment;
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
        $publishedCount = Article::where('status', 1)->count();
        $draftCount = Article::where('status', 0)->count();
        $commentCount = Comment::count();
        $pendingCommentCount = Comment::where('status', 0)->count();

        $viewCount = Article::sum('view_count');
        $totalViews = ArticleView::count();

        $recentArticles = Article::with('category')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentComments = Comment::with('article')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $today = date('Y-m-d');
        $todayViews = ArticleView::whereDate('created_at', $today)->count();

        $weekViews = ArticleView::where('created_at', '>=', date('Y-m-d', strtotime('-7 days')))->count();

        $monthViews = ArticleView::where('created_at', '>=', date('Y-m-d', strtotime('-30 days')))->count();

        return ResponseHelper::success([
            'article_count' => $articleCount,
            'published_count' => $publishedCount,
            'draft_count' => $draftCount,
            'comment_count' => $commentCount,
            'pending_comment_count' => $pendingCommentCount,
            'view_count' => $viewCount,
            'total_views' => $totalViews,
            'recent_articles' => $recentArticles,
            'recent_comments' => $recentComments,
            'today_views' => $todayViews,
            'week_views' => $weekViews,
            'month_views' => $monthViews,
        ]);
    }
}
