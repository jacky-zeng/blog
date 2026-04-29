<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;
use Hyperf\Database\Model\Relations\HasMany;

class Category extends Model
{
    protected ?string $table = 'categories';

    protected array $fillable = [
        'name',
        'slug',
        'sort_order',
    ];

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'category_id');
    }
}
