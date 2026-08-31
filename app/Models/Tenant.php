<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $table = 'tenants';
    protected $fillable = [
        'code',
        'name',
        'slug',
        'description',
        'status'
    ];
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function displays()
    {
        return $this->hasMany(Display::class);
    }

    public function advertisements()
    {
        return $this->hasMany(Advertisement::class);
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
