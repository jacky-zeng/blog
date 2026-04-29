<?php

declare(strict_types=1);

namespace App\Controller;

use App\Helper\ResponseHelper;
use App\Model\Article;
use App\Model\ArticleView;
use App\Model\Comment;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;

#[Controller]
class ArticleController
{
    #[Inject]
    protected RequestInterface $request;

    #[GetMapping(path: '/api/articles')]
    public function index(): ResponseInterface
    {
        $page = (int) $this->request->input('page', 1);
        $pageSize = (int) $this->request->input('page_size', 10);
        $categoryId = $this->request->input('category_id');
        $tagId = $this->request->input('tag_id');

        $query = Article::with(['category', 'tags'])
            ->where('status', 1);

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

    #[GetMapping(path: '/api/article/{slug}')]
    public function show(string $slug): ResponseInterface
    {
        $article = Article::with(['category', 'tags'])
            ->where('slug', $slug)
            ->where('status', 1)
            ->first();

        if (!$article) {
            return ResponseHelper::error('文章不存在', 404);
        }

        $article->increment('view_count');

        $this->recordView($article->id);

        return ResponseHelper::success($article);
    }

    #[PostMapping(path: '/api/comment')]
    public function comment(): ResponseInterface
    {
        $articleId = (int) $this->request->input('article_id');
        $nickname = (string) $this->request->input('nickname', '');
        $email = (string) $this->request->input('email', '');
        $content = (string) $this->request->input('content', '');
        $parentId = $this->request->input('parent_id');

        if (empty($nickname) || empty($email) || empty($content)) {
            return ResponseHelper::error('请填写完整信息');
        }

        $article = Article::find($articleId);

        if (!$article) {
            return ResponseHelper::error('文章不存在');
        }

        $commentData = [
            'article_id' => $articleId,
            'nickname' => $nickname,
            'email' => $email,
            'content' => $content,
            'ip_address' => $this->getClientIp(),
            'status' => 1,
        ];

        if ($parentId !== null) {
            $commentData['parent_id'] = (int) $parentId;
        }

        $comment = Comment::create($commentData);

        return ResponseHelper::success($comment, '评论成功');
    }

    private function recordView(int $articleId): void
    {
        $ip = $this->getClientIp();
        $userAgent = $this->request->getHeaderLine('User-Agent') ?: '';

        ArticleView::create([
            'article_id' => $articleId,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ]);
    }

    private function getClientIp(): string
    {
        $serverParams = $this->request->getServerParams();

        return $serverParams['remote_addr'] ?? '0.0.0.0';
    }
}
