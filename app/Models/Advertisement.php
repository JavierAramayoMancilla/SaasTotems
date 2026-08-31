<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'type',
        'media_path',
        'duration',
        'is_active',
        'starts_at',
        'ends_at',
    ];
    protected $casts = [
        'duration' => 'integer',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function displayAdvertisements()
    {
        return $this->hasMany(DisplayAdvertisement::class, 'advertisement_id');
    }

    public function analyticsEvents()
    {
        return $this->hasMany(AnalyticsEvent::class, 'advertisement_id');
    }
}
