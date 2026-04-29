<?php

declare(strict_types=1);

namespace App\Controller;

use App\Helper\ResponseHelper;
use App\Model\Tag;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Psr\Http\Message\ResponseInterface;

#[Controller]
class TagController
{
    #[GetMapping(path: '/api/tags')]
    public function index(): ResponseInterface
    {
        $tags = Tag::withCount('articles')
            ->orderBy('articles_count', 'desc')
            ->get();

        return ResponseHelper::success($tags);
    }

    #[GetMapping(path: '/api/tags/{slug}')]
    public function show(string $slug): ResponseInterface
    {
        $tag = Tag::where('slug', $slug)->first();

        if (!$tag) {
            return ResponseHelper::error('标签不存在', 404);
        }

        $page = (int) request()->input('page', 1);
        $pageSize = (int) request()->input('page_size', 10);

        $articles = $tag->articles()
            ->with(['category'])
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->paginate($pageSize, ['*'], 'page', $page);

        return ResponseHelper::success([
            'tag' => $tag,
            'articles' => $articles->toArray(),
        ]);
    }
}
