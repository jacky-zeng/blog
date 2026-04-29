<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;
use Hyperf\Database\Model\Relations\BelongsTo;

class ArticleView extends Model
{
    protected ?string $table = 'article_views';

    protected array $fillable = [
        'article_id',
        'ip_address',
        'user_agent',
    ];

    protected array $casts = [
        'article_id' => 'integer',
    ];

    public int $id;
    public int $article_id;
    public string $ip_address;
    public ?string $user_agent;
    public string $created_at;

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'article_id');
    }
}
