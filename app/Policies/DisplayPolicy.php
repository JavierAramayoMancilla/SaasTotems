<?php

namespace App\Policies;

use App\Models\Display;
use App\Models\User;

class DisplayPolicy extends TenantPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasTenantPermission($user, 'displays.view') && $this->hasTenantAccess($user, $user->tenant_id);
    }

    public function view(User $user, Display $display): bool
    {
        return $this->hasTenantPermission($user, 'displays.view')
            && $this->hasTenantAccess($user, $display->tenant_id);
    }

    public function create(User $user): bool
    {
        return $this->hasTenantPermission($user, 'displays.create') && $this->hasTenantAccess($user, $user->tenant_id);
    }

    public function update(User $user, Display $display): bool
    {
        return $this->hasTenantPermission($user, 'displays.update')
            && $this->hasTenantAccess($user, $display->tenant_id);
    }

    public function delete(User $user, Display $display): bool
    {
        return $this->hasTenantPermission($user, 'displays.delete')
            && $this->hasTenantAccess($user, $display->tenant_id);
    }
}
