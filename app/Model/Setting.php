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
}
