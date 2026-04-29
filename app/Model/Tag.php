<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;
use Hyperf\Database\Model\Relations\BelongsToMany;

class Tag extends Model
{
    protected ?string $table = 'tags';

    protected array $fillable = [
        'name',
        'slug',
    ];

    public int $id;
    public string $name;
    public string $slug;
    public string $created_at;
    public string $updated_at;

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_tags', 'tag_id', 'article_id');
    }
}
