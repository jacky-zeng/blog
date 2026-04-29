<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class Setting extends Model
{
    protected ?string $table = 'settings';

    protected array $fillable = [
        'key',
        'value',
        'description',
    ];

    public int $id;
    public string $key;
    public string $value;
    public ?string $description;
    public string $created_at;
    public string $updated_at;
}
