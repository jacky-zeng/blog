<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;
use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\Relations\BelongsTo;

class Comment extends Model
{
    protected ?string $table = 'comments';

    public bool $timestamps = true;

    const UPDATED_AT = null;

    protected array $fillable = [
        'article_id',
        'parent_id',
        'nickname',
        'email',
        'content',
        'status',
        'ip_address',
    ];

    protected array $casts = [
        'article_id' => 'integer',
        'parent_id' => 'integer',
        'status' => 'integer',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'article_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }
}
