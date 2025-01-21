<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property string $email
 * @property string $token
 * @property \Illuminate\Support\Carbon $created_at
 * @property bool $used
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordResetToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordResetToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordResetToken query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordResetToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordResetToken whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordResetToken whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordResetToken whereUsed($value)
 * @mixin \Eloquent
 */
class PasswordResetToken extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'email';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'email',
        'token',
        'created_at',
        'used',
    ];

    protected function casts(): array
    {
        return [
            'used' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function user(): User
    {
        return User::where('email', $this->email)->firstOrFail();
    }
}
