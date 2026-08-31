<?php

namespace App\Policies;

use App\Models\DisplayAdvertisement;
use App\Models\User;

class DisplayAdvertisementPolicy extends TenantPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasTenantPermission($user, 'displays.view') && $this->hasTenantAccess($user, $user->tenant_id);
    }

    public function view(User $user, DisplayAdvertisement $displayAdvertisement): bool
    {
        return $this->hasTenantPermission($user, 'displays.view')
            && $this->hasTenantAccess($user, $this->tenantOf($displayAdvertisement));
    }

    public function create(User $user): bool
    {
        return $this->hasTenantPermission($user, 'displays.create') && $this->hasTenantAccess($user, $user->tenant_id);
    }

    public function update(User $user, DisplayAdvertisement $displayAdvertisement): bool
    {
        return $this->hasTenantPermission($user, 'displays.update')
            && $this->hasTenantAccess($user, $this->tenantOf($displayAdvertisement));
    }

    public function delete(User $user, DisplayAdvertisement $displayAdvertisement): bool
    {
        return $this->hasTenantPermission($user, 'displays.delete')
            && $this->hasTenantAccess($user, $this->tenantOf($displayAdvertisement));
    }

    protected function tenantOf(DisplayAdvertisement $displayAdvertisement): ?int
    {
        return $displayAdvertisement->display?->tenant_id
            ?? $displayAdvertisement->display()->value('tenant_id')
            ?? $displayAdvertisement->advertisement?->tenant_id
            ?? $displayAdvertisement->advertisement()->value('tenant_id');
    }
}
