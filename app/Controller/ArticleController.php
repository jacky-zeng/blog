<?php

declare(strict_types=1);

namespace App\Controller;

use App\Cache\SiteCacheKey;
use App\Helper\ResponseHelper;
use App\Model\Article;
use App\Model\ArticleView;
use App\Model\Comment;
use App\Model\Setting;
use Hyperf\Cache\Cache;
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

    #[Inject]
    protected Cache $cache;

    #[GetMapping(path: '/api/articles')]
    public function index(): ResponseInterface
    {
        $page = (int) $this->request->input('page', 1);
        $pageSize = (int) $this->request->input('page_size', 10);
        $categoryId = $this->request->input('category_id');
        $tagId = $this->request->input('tag_id');

        $cacheKey = SiteCacheKey::articlesList($page, $pageSize);
        
        $cachedArticles = $this->cache->get($cacheKey);
        if ($cachedArticles && $page <= 5) {
            return ResponseHelper::success(unserialize($cachedArticles));
        }

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

        $articles->getCollection()->transform(function ($article) {
            $article->content = $this->stripHtmlAndTruncate((string) $article->content);
            return $article;
        });

        $result = $articles->toArray();
        
        if ($page <= 5) {
            $this->cache->set($cacheKey, serialize($result), 600);
        }

        return ResponseHelper::success($result);
    }

    #[GetMapping(path: '/api/articles/{slug}')]
    public function show(string $slug): ResponseInterface
    {
        if (($response = $this->validateSlug($slug)) !== null) {
            return $response;
        }

        $cacheKey = SiteCacheKey::articleDetail($slug);
        
        $cachedArticle = $this->cache->get($cacheKey);
        if ($cachedArticle) {
            $article = unserialize($cachedArticle);
        } else {
            $article = Article::with(['category', 'tags'])
                ->where('status', 1)
                ->where('slug', $slug)
                ->first();

            if (!$article) {
                return ResponseHelper::error('文章不存在', 404);
            }
            
            $this->cache->set($cacheKey, serialize($article), 7200);
        }

        $article->increment('view_count');

        $this->recordView($article->id);

        return ResponseHelper::success($article);
    }

    #[GetMapping(path: '/api/articles/{slug}/comments')]
    public function comments(string $slug): ResponseInterface
    {
        if (($response = $this->validateSlug($slug)) !== null) {
            return $response;
        }

        $cacheKey = SiteCacheKey::articleComments($slug);
        
        $cachedComments = $this->cache->get($cacheKey);
        if ($cachedComments) {
            return ResponseHelper::success(unserialize($cachedComments));
        }

        $article = Article::where('status', 1);

        if (is_numeric($slug)) {
            $article = $article->where('id', (int) $slug)->first();
        } else {
            $article = $article->where('slug', $slug)->first();
        }

        if (!$article) {
            return ResponseHelper::error('文章不存在', 404);
        }

        $comments = Comment::where('article_id', $article->id)
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->get();

        $this->cache->set($cacheKey, serialize($comments), 300);

        return ResponseHelper::success($comments);
    }

    #[PostMapping(path: '/api/articles/{slug}/comments')]
    public function storeComment(string $slug): ResponseInterface
    {
        if (($response = $this->validateSlug($slug)) !== null) {
            return $response;
        }

        $article = Article::where('status', 1);

        if (is_numeric($slug)) {
            $article = $article->where('id', (int) $slug)->first();
        } else {
            $article = $article->where('slug', $slug)->first();
        }

        if (!$article) {
            return ResponseHelper::error('文章不存在', 404);
        }

        $nickname = (string) $this->request->input('nickname', '');
        $email = (string) $this->request->input('email', '');
        $content = (string) $this->request->input('content', '');
        $parentId = $this->request->input('parent_id');

        if (empty($nickname) || empty($email) || empty($content)) {
            return ResponseHelper::error('请填写完整信息');
        }

        $settings = Setting::all()->pluck('value', 'key');
        $commentAudit = isset($settings['comment_audit']) ? (int) $settings['comment_audit'] : 1;
        
        $commentData = [
            'article_id' => $article->id,
            'nickname' => $nickname,
            'email' => $email,
            'content' => $content,
            'ip_address' => $this->getClientIp(),
            'status' => $commentAudit === 1 ? 0 : 1,
        ];

        if ($parentId !== null) {
            $commentData['parent_id'] = (int) $parentId;
        }

        $comment = Comment::create($commentData);

        return ResponseHelper::success($comment, '评论成功');
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

    private function stripHtmlAndTruncate(string $content, int $length = 200): string
    {
        $content = strip_tags($content);
        $content = preg_replace('/\s+/', ' ', $content);
        $content = trim($content);
        
        if (mb_strlen($content) > $length) {
            return mb_substr($content, 0, $length) . '...';
        }
        
        return $content;
    }

    private function validateSlug(string $slug): ?ResponseInterface
    {
        if (strlen($slug) < 11) {
            return ResponseHelper::error('文章不存在', 404);
        }

        $first3Chars = substr($slug, 0, 3);

        $str = "qysfjxbpm6391";
        $checkChars = [$first3Chars[0], $first3Chars[1], $first3Chars[2]];

        foreach ($checkChars as $c) {
            if (strpos($str, $c) === false) {
                return ResponseHelper::error('文章不存在', 404);
            }
        }

        return null;
    }
}
