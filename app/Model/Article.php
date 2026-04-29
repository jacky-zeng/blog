<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;
use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\Relations\BelongsToMany;
use Hyperf\Database\Model\Relations\BelongsTo;

class Article extends Model
{
    protected ?string $table = 'articles';

    protected array $fillable = [
        'title',
        'slug',
        'summary',
        'content',
        'cover_image',
        'category_id',
        'status',
        'view_count',
        'seo_title',
        'seo_description',
    ];

    protected array $casts = [
        'status' => 'integer',
        'view_count' => 'integer',
        'category_id' => 'integer',
    ];

    public int $id;
    public string $title;
    public string $slug;
    public ?string $summary;
    public string $content;
    public ?string $cover_image;
    public ?int $category_id;
    public int $status;
    public int $view_count;
    public ?string $seo_title;
    public ?string $seo_description;
    public string $created_at;
    public string $updated_at;

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'article_tags', 'article_id', 'tag_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'article_id');
    }

    public function views(): HasMany
    {
        return $this->hasMany(ArticleView::class, 'article_id');
    }
}
