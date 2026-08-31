<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DisplayAdvertisement extends Model
{
    use HasFactory;

    protected $table = 'display_advertisements';

    protected $fillable = [
        'display_id',
        'advertisement_id',
        'position',
        'transition',
        'is_active',
    ];

    protected $casts = [
        'position' => 'integer',
        'is_active' => 'boolean',
    ];

    public function display()
    {
        return $this->belongsTo(Display::class);
    }

    public function advertisement()
    {
        return $this->belongsTo(Advertisement::class);
    }

    public function adSchedules()
    {
        return $this->hasMany(AdSchedule::class, 'display_advertisement_id');
    }
}
