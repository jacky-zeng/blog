<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;
use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\Relations\BelongsTo;

class User extends Model
{
    protected ?string $table = 'users';

    protected array $fillable = [
        'username',
        'password',
        'email',
        'nickname',
        'avatar',
        'bio',
    ];

    protected array $hidden = [
        'password',
    ];

    public int $id;
    public string $username;
    public string $password;
    public ?string $email;
    public ?string $nickname;
    public ?string $avatar;
    public ?string $bio;
    public string $created_at;
    public string $updated_at;
}
