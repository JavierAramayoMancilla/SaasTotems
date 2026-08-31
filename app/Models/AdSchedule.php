<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdSchedule extends Model
{
    use HasFactory;

    protected $table = 'ad_schedules';

    protected $fillable = [
        'display_advertisement_id',
        'day_of_week',
        'start_time',
        'end_time',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function displayAdvertisement()
    {
        return $this->belongsTo(DisplayAdvertisement::class, 'display_advertisement_id');
    }
}
