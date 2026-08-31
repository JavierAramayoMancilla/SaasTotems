<?php

namespace App\Policies;

use App\Models\User;

abstract class TenantPolicy
{
    protected function hasTenantAccess(User $user, ?int $tenantId = null): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        if (blank($user->tenant_id)) {
            return false;
        }

        return $tenantId === null || (int) $user->tenant_id === (int) $tenantId;
    }

    protected function hasTenantPermission(User $user, string $permission): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $user->hasPermissionTo($permission);
    }
}
