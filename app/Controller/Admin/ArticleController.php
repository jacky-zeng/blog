<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Cache\SiteCacheKey;
use App\Helper\ResponseHelper;
use App\Helper\ValidatorHelper;
use App\Model\Article;
use App\Model\Tag;
use Hyperf\Cache\Cache;
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

    #[Inject]
    protected Cache $cache;

    #[GetMapping(path: '/api/admin/articles')]
    public function index(): ResponseInterface
    {
        $page = (int) $this->request->input('page', 1);
        $pageSize = (int) $this->request->input('page_size', 10);
        $keyword = (string) $this->request->input('keyword', '');
        $categoryId = $this->request->input('category_id');
        $status = $this->request->input('status');

        $query = Article::with(['category:id,name,slug', 'tags:id,name,slug']);

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
            ->paginate($pageSize, ['id', 'title', 'slug', 'category_id', 'status', 'view_count', 'created_at'], 'page', $page);

        return ResponseHelper::success($articles->toArray());
    }

    #[GetMapping(path: '/api/admin/articles/{id}')]
    public function show(int $id): ResponseInterface
    {
        $article = Article::with(['category:id,name,slug', 'tags:id,name,slug'])
            ->select(['id', 'title', 'slug', 'summary', 'content', 'cover_image', 'category_id', 'status', 'view_count', 'created_at', 'updated_at'])
            ->find($id);

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

        //if (!isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = $this->generateSlug((string) $data['title']);
        //}

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $article = Article::create($data);

        if (isset($data['tags']) && is_array($data['tags'])) {
            $this->syncTags($article, $data['tags']);
        }

        $this->clearArticlesListCache();

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

        $this->cache->delete(SiteCacheKey::articleDetail($article->slug));
        $this->clearArticlesListCache();

        return ResponseHelper::success($article->load('category', 'tags'), '更新成功');
    }

    #[DeleteMapping(path: '/api/admin/articles/{id}')]
    public function destroy(int $id): ResponseInterface
    {
        $article = Article::find($id);

        if (!$article) {
            return ResponseHelper::error('文章不存在', 404);
        }

        $this->cache->delete(SiteCacheKey::articleDetail($article->slug));
        $this->clearArticlesListCache();
        
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
        // $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text)));

        // $slug = trim($slug, '-');

        // if (empty($slug) || $slug === '-' || $slug === '--' || ctype_digit($slug)) {
        //     $slug = 'article-' . (string) time();
        // }

        // return $slug;

        // 1. 统一标题格式（去空格、转小写、固定编码）
        $title = trim(mb_strtolower($text, 'UTF-8'));
        
        // 2. 生成标题哈希（固定规则：MD5 + 截取前12位）
        $hash = md5($title);
        $hashPart = substr($hash, 0, 12);
        
        // 3. 自定义混淆盐值（核心规则，修改后生成结果会变）
        $salt = 'z'.time().'yq';
        $mixStr = $hashPart . $salt . strlen($title);
        
        // 4. 二次哈希 + 只保留数字+字母，截取8位

        //从"qysfjxbpm6391"中随机挑出3个字符
        $randomChars = substr(str_shuffle('qysfjxbpm6391'), 0, 3);

        return $randomChars.substr(preg_replace('/[^a-z0-9]/', '', md5($mixStr)), 0, 8);
    }

    private function clearArticlesListCache(): void
    {
        $prefix = SiteCacheKey::articlesListPrefix();
        
        for ($page = 1; $page <= 5; $page++) {
            $pageSizes = [10, 20, 30, 50];
            foreach ($pageSizes as $pageSize) {
                $this->cache->delete($prefix . $page . ':' . $pageSize);
            }
        }
    }
}
