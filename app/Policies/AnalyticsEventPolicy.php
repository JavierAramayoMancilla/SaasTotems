<?php

namespace App\Policies;

use App\Models\AnalyticsEvent;
use App\Models\User;

class AnalyticsEventPolicy extends TenantPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasTenantPermission(
            $user,
            'analytics.view'
        );
    }

    public function view(
        User $user,
        AnalyticsEvent $analyticsEvent
    ): bool {
        return $this->hasTenantPermission(
            $user,
            'analytics.view'
        ) && $this->hasTenantAccess(
            $user,
            $analyticsEvent->tenant_id
        );
    }
}