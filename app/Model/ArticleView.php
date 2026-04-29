<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;
use Hyperf\Database\Model\Relations\BelongsTo;

class ArticleView extends Model
{
    protected ?string $table = 'article_views';

    public bool $timestamps = false;

    protected array $fillable = [
        'article_id',
        'ip_address',
        'user_agent',
    ];

    protected array $casts = [
        'article_id' => 'integer',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'article_id');
    }
}
