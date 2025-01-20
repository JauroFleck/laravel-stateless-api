<?php

namespace App\Models\Admin;

use App\Enums\Admin\SystemConfigurationKeys;
use Illuminate\Database\Eloquent\Model;

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
