<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $user_id
 * @property string $token
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \App\Models\User\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerificationToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerificationToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerificationToken query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerificationToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerificationToken whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerificationToken whereUserId($value)
 * @mixin \Eloquent
 */
class EmailVerificationToken extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'integer';
    protected $fillable = [
        'user_id',
        'token',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
