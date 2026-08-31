<?php

namespace App\Policies;

use App\Models\Advertisement;
use App\Models\User;

class AdvertisementPolicy extends TenantPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasTenantPermission($user, 'advertisements.view') && $this->hasTenantAccess($user, $user->tenant_id);
    }

    public function view(User $user, Advertisement $advertisement): bool
    {
        return $this->hasTenantPermission($user, 'advertisements.view')
            && $this->hasTenantAccess($user, $advertisement->tenant_id);
    }

    public function create(User $user): bool
    {
        return $this->hasTenantPermission($user, 'advertisements.create') && $this->hasTenantAccess($user, $user->tenant_id);
    }

    public function update(User $user, Advertisement $advertisement): bool
    {
        return $this->hasTenantPermission($user, 'advertisements.update')
            && $this->hasTenantAccess($user, $advertisement->tenant_id);
    }

    public function delete(User $user, Advertisement $advertisement): bool
    {
        return $this->hasTenantPermission($user, 'advertisements.delete')
            && $this->hasTenantAccess($user, $advertisement->tenant_id);
    }
}
