<?php

namespace App\Models\Admin;

use App\Enums\Admin\SystemConfigurationKeys;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property SystemConfigurationKeys $key
 * @property string|null $value
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemConfiguration newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemConfiguration newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemConfiguration query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemConfiguration whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemConfiguration whereValue($value)
 * @mixin \Eloquent
 */
class SystemConfiguration extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['key', 'value'];

    protected function casts(): array
    {
        return [
            'key' => SystemConfigurationKeys::class,
        ];
    }
}
