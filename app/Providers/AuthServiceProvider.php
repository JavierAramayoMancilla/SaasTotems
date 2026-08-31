<?php

namespace App\Providers;

use App\Models\Advertisement;
use App\Models\AdSchedule;
use App\Models\AnalyticsEvent;
use App\Models\Display;
use App\Models\DisplayAdvertisement;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\User;
use App\Policies\AdSchedulePolicy;
use App\Policies\AdvertisementPolicy;
use App\Policies\AnalyticsEventPolicy;
use App\Policies\DisplayAdvertisementPolicy;
use App\Policies\DisplayPolicy;
use App\Policies\MenuItemPolicy;
use App\Policies\MenuPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        Display::class => DisplayPolicy::class,
        DisplayAdvertisement::class => DisplayAdvertisementPolicy::class,
        Advertisement::class => AdvertisementPolicy::class,
        Menu::class => MenuPolicy::class,
        MenuItem::class => MenuItemPolicy::class,
        AdSchedule::class => AdSchedulePolicy::class,
        AnalyticsEvent::class => AnalyticsEventPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
