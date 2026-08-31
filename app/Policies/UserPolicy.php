<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy extends TenantPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasTenantPermission($user, 'users.view')
            && $this->hasTenantAccess($user, $user->tenant_id);
    }

    public function view(User $user, User $model): bool
    {
        return $this->hasTenantPermission($user, 'users.view')
            && $this->hasTenantAccess($user, $model->tenant_id);
    }

    public function create(User $user): bool
    {
        return $this->hasTenantPermission($user, 'users.create')
            && $this->hasTenantAccess($user, $user->tenant_id);
    }

    public function update(User $user, User $model): bool
    {
        return $this->hasTenantPermission($user, 'users.update')
            && $this->hasTenantAccess($user, $model->tenant_id);
    }

    public function delete(User $user, User $model): bool
    {
        return $this->hasTenantPermission($user, 'users.delete')
            && $this->hasTenantAccess($user, $model->tenant_id);
    }
}