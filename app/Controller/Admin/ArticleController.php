<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Helper\ResponseHelper;
use App\Helper\ValidatorHelper;
use App\Model\Article;
use App\Model\Tag;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Contract\RequestInterface;
use App\Middleware\AuthMiddleware;
use Psr\Http\Message\ResponseInterface;

#[Controller]
#[Middleware(AuthMiddleware::class)]
class ArticleController
{
    #[Inject]
    protected RequestInterface $request;

    #[GetMapping(path: '/api/admin/articles')]
    public function index(): ResponseInterface
    {
        $page = (int) $this->request->input('page', 1);
        $pageSize = (int) $this->request->input('page_size', 10);
        $keyword = (string) $this->request->input('keyword', '');
        $categoryId = $this->request->input('category_id');
        $status = $this->request->input('status');

        $query = Article::with(['category', 'tags']);

        if (!empty($keyword)) {
            $query->where('title', 'like', '%' . $keyword . '%');
        }

        if ($categoryId !== null) {
            $query->where('category_id', (int) $categoryId);
        }

        if ($status !== null) {
            $query->where('status', (int) $status);
        }

        $articles = $query->orderBy('created_at', 'desc')
            ->paginate($pageSize, ['*'], 'page', $page);

        return ResponseHelper::success($articles->toArray());
    }

    #[GetMapping(path: '/api/admin/articles/{id}')]
    public function show(int $id): ResponseInterface
    {
        $article = Article::with(['category', 'tags'])->find($id);

        if (!$article) {
            return ResponseHelper::error('文章不存在', 404);
        }

        return ResponseHelper::success($article);
    }

    #[PostMapping(path: '/api/admin/articles')]
    public function store(): ResponseInterface
    {
        $data = $this->request->all();

        $rules = [
            'title' => 'required|max:200',
            'content' => 'required',
            'category_id' => 'required|integer',
        ];

        $validator = ValidatorHelper::make($data, $rules);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->first());
        }

        if (!isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = $this->generateSlug((string) $data['title']);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $article = Article::create($data);

        if (isset($data['tags']) && is_array($data['tags'])) {
            $this->syncTags($article, $data['tags']);
        }

        return ResponseHelper::success($article->load('category', 'tags'), '创建成功');
    }

    #[PutMapping(path: '/api/admin/articles/{id}')]
    public function update(int $id): ResponseInterface
    {
        $article = Article::find($id);

        if (!$article) {
            return ResponseHelper::error('文章不存在', 404);
        }

        $data = $this->request->all();

        $rules = [
            'title' => 'sometimes|required|max:200',
            'content' => 'sometimes|required',
            'category_id' => 'sometimes|required|integer',
        ];

        $validator = ValidatorHelper::make($data, $rules);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->first());
        }

        unset($data['slug']);

        $data['updated_at'] = date('Y-m-d H:i:s');

        $article->update($data);

        if (isset($data['tags']) && is_array($data['tags'])) {
            $this->syncTags($article, $data['tags']);
        }

        return ResponseHelper::success($article->load('category', 'tags'), '更新成功');
    }

    #[DeleteMapping(path: '/api/admin/articles/{id}')]
    public function destroy(int $id): ResponseInterface
    {
        $article = Article::find($id);

        if (!$article) {
            return ResponseHelper::error('文章不存在', 404);
        }

        $article->delete();

        return ResponseHelper::success(null, '删除成功');
    }

    private function syncTags(Article $article, array $tagNames): void
    {
        $tagIds = [];

        foreach ($tagNames as $tagName) {
            $tag = Tag::firstOrCreate(
                ['name' => (string) $tagName],
                ['slug' => $this->generateSlug((string) $tagName)]
            );
            $tagIds[] = $tag->id;
        }

        $article->tags()->sync($tagIds);
    }

    private function generateSlug(string $text): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text)));

        $slug = trim($slug, '-');

        if (empty($slug) || $slug === '-' || $slug === '--' || ctype_digit($slug)) {
            $slug = 'article-' . (string) time();
        }

        return $slug;
    }
}
