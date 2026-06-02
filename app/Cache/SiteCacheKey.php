<?php

declare(strict_types=1);

namespace App\Cache;

class SiteCacheKey
{
    public static function articleDetail(string $slug): string
    {
        return "article:detail:{$slug}";
    }
    
    public static function articleDetailPrefix(): string
    {
        return "article:detail:";
    }
    
    public static function siteInfo(): string
    {
        return "site:info";
    }
    
    public static function articleComments(string $slug): string
    {
        return "article:comments:{$slug}";
    }
    
    public static function tags(): string
    {
        return "tags:list";
    }
    
    public static function categories(): string
    {
        return "categories:list";
    }
    
    public static function settings(): string
    {
        return "settings:list";
    }
    
    public static function articlesList(int $page, int $pageSize, $categoryId = null, $tagId = null): string
    {
        $key = "articles:list:{$page}:{$pageSize}";
        if ($categoryId !== null) {
            $key .= ":category:{$categoryId}";
        }
        if ($tagId !== null) {
            $key .= ":tag:{$tagId}";
        }
        return $key;
    }
    
    public static function articlesListPrefix(): string
    {
        return "articles:list:";
    }
}
