<?php

namespace App\Policies;

use App\Models\AdSchedule;
use App\Models\User;

class AdSchedulePolicy extends TenantPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasTenantPermission($user, 'ad_schedules.view') && $this->hasTenantAccess($user, $user->tenant_id);
    }

    public function view(User $user, AdSchedule $adSchedule): bool
    {
        return $this->hasTenantPermission($user, 'ad_schedules.view')
            && $this->hasTenantAccess($user, $this->tenantOf($adSchedule));
    }

    public function create(User $user): bool
    {
        return $this->hasTenantPermission($user, 'ad_schedules.create') && $this->hasTenantAccess($user, $user->tenant_id);
    }

    public function update(User $user, AdSchedule $adSchedule): bool
    {
        return $this->hasTenantPermission($user, 'ad_schedules.update')
            && $this->hasTenantAccess($user, $this->tenantOf($adSchedule));
    }

    public function delete(User $user, AdSchedule $adSchedule): bool
    {
        return $this->hasTenantPermission($user, 'ad_schedules.delete')
            && $this->hasTenantAccess($user, $this->tenantOf($adSchedule));
    }

    protected function tenantOf(AdSchedule $adSchedule): ?int
    {
        return $adSchedule->displayAdvertisement?->display?->tenant_id;
    }
}
