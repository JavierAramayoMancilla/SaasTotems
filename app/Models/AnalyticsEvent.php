<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnalyticsEvent extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'analytics_events';
    const UPDATED_AT = null;
    //public $timestamps = true;

    protected $fillable = [
        'tenant_id',
        'display_id',
        'event_type',
        'advertisement_id',
        'menu_item_id',
        'session_id',
        'started_at',
        'duration',
        'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'duration' => 'integer',
        'metadata' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function display()
    {
        return $this->belongsTo(Display::class);
    }

    public function advertisement()
    {
        return $this->belongsTo(Advertisement::class);
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }
}
