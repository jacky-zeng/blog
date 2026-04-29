<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;
use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\Relations\BelongsTo;

class Category extends Model
{
    protected ?string $table = 'categories';

    protected array $fillable = [
        'name',
        'slug',
        'sort_order',
    ];

    public int $id;
    public string $name;
    public string $slug;
    public int $sort_order;
    public string $created_at;
    public string $updated_at;

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'category_id');
    }
}
