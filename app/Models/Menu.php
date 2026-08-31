<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'advertisement_id',
        'name',
        'slug',
        'is_active',
        'version',
        'published_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'version' => 'integer',
        'published_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function advertisement()
    {
        return $this->belongsTo(Advertisement::class);
    }

    public function items()
    {
        return $this->hasMany(MenuItem::class);
    }
}