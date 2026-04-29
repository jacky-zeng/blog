<?php

declare(strict_types=1);

namespace App\Controller;

use App\Helper\ResponseHelper;
use App\Model\Article;
use App\Model\ArticleView;
use App\Model\Category;
use App\Model\Comment;
use App\Model\Setting;
use App\Model\Tag;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Psr\Http\Message\ResponseInterface;

#[Controller]
class ArticleController
{
    #[GetMapping(path: '/api/articles')]
    public function index(): ResponseInterface
    {
        $page = (int) request()->input('page', 1);
        $pageSize = (int) request()->input('page_size', 10);
        $keyword = (string) request()->input('keyword', '');
        $categoryId = request()->input('category_id');
        $tagId = request()->input('tag_id');

        $query = Article::with(['category', 'tags'])
            ->where('status', 1);

        if (!empty($keyword)) {
            $query->where('title', 'like', '%' . $keyword . '%');
        }

        if ($categoryId !== null) {
            $query->where('category_id', (int) $categoryId);
        }

        if ($tagId !== null) {
            $query->whereHas('tags', function ($q) use ($tagId) {
                $q->where('tags.id', (int) $tagId);
            });
        }

        $articles = $query->orderBy('created_at', 'desc')
            ->paginate($pageSize, ['*'], 'page', $page);

        return ResponseHelper::success($articles->toArray());
    }

    #[GetMapping(path: '/api/articles/{slug}')]
    public function show(string $slug): ResponseInterface
    {
        $article = Article::with(['category', 'tags'])
            ->where('slug', $slug)
            ->where('status', 1)
            ->first();

        if (!$article) {
            return ResponseHelper::error('文章不存在', 404);
        }

        $this->recordView($article);

        $article->increment('view_count');

        return ResponseHelper::success($article);
    }

    #[GetMapping(path: '/api/articles/{slug}/comments')]
    public function comments(string $slug): ResponseInterface
    {
        $article = Article::where('slug', $slug)
            ->where('status', 1)
            ->first();

        if (!$article) {
            return ResponseHelper::error('文章不存在', 404);
        }

        $comments = Comment::with(['article', 'parent'])
            ->where('article_id', $article->id)
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->get();

        return ResponseHelper::success($comments);
    }

    #[PostMapping(path: '/api/articles/{slug}/comments')]
    public function storeComment(string $slug): ResponseInterface
    {
        $article = Article::where('slug', $slug)
            ->where('status', 1)
            ->first();

        if (!$article) {
            return ResponseHelper::error('文章不存在', 404);
        }

        $data = request()->all();

        $rules = [
            'nickname' => 'required|max:50',
            'email' => 'required|email|max:100',
            'content' => 'required',
        ];

        $validator = validator($data, $rules);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors()->first());
        }

        $data['article_id'] = $article->id;
        $data['status'] = 0;
        $data['ip_address'] = (string) (request()->getServerParams()['remote_addr'] ?? '127.0.0.1');

        $comment = Comment::create($data);

        return ResponseHelper::success($comment, '评论提交成功，等待审核');
    }

    private function recordView(Article $article): void
    {
        $ipAddress = (string) (request()->getServerParams()['remote_addr'] ?? '127.0.0.1');
        $userAgent = request()->getHeaderLine('User-Agent');

        ArticleView::create([
            'article_id' => $article->id,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }
}
