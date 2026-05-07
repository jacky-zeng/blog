<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Helper\ResponseHelper;
use App\Model\Comment;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Contract\RequestInterface;
use App\Middleware\AuthMiddleware;
use Psr\Http\Message\ResponseInterface;

#[Controller]
#[Middleware(AuthMiddleware::class)]
class CommentController
{
    #[Inject]
    protected RequestInterface $request;

    #[GetMapping(path: '/api/admin/comments')]
    public function index(): ResponseInterface
    {
        $page = (int) $this->request->input('page', 1);
        $pageSize = (int) $this->request->input('page_size', 10);
        $keyword = (string) $this->request->input('keyword', '');
        $status = $this->request->input('status');

        $query = Comment::select(['id', 'nickname', 'content', 'status', 'created_at', 'article_id'])
            ->with(['article:id,title', 'parent:id,nickname']);

        if (!empty($keyword)) {
            $query->where('content', 'like', '%' . $keyword . '%')
                ->orWhereHas('article', function ($q) use ($keyword) {
                    $q->where('title', 'like', '%' . $keyword . '%');
                });
        }

        if ($status !== null) {
            $query->where('status', (int) $status);
        }

        $comments = $query->orderBy('created_at', 'desc')
            ->paginate($pageSize, ['*'], 'page', $page);

        return ResponseHelper::success($comments->toArray());
    }

    #[PutMapping(path: '/api/admin/comments/{id}')]
    public function update(int $id): ResponseInterface
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return ResponseHelper::error('评论不存在', 404);
        }

        $data = $this->request->all();

        if (isset($data['status'])) {
            $comment->status = (int) $data['status'];
            $comment->save();
        }

        return ResponseHelper::success($comment, '更新成功');
    }

    #[DeleteMapping(path: '/api/admin/comments/{id}')]
    public function destroy(int $id): ResponseInterface
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return ResponseHelper::error('评论不存在', 404);
        }

        $comment->delete();

        return ResponseHelper::success(null, '删除成功');
    }
}
