<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Display extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'displays';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'status',
        'last_sync_at',
    ];

    protected $casts = [
        'last_sync_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Display $display) {
            $display->uuid ??= (string) Str::uuid();
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function displayAdvertisements()
    {
        return $this->hasMany(DisplayAdvertisement::class);
    }

    public function menus()
    {
        return $this->hasMany(Menu::class);
    }

    public function analyticsEvents()
    {
        return $this->hasMany(AnalyticsEvent::class);
    }
}