<?php

namespace App\Policies;

use App\Models\Menu;
use App\Models\User;

class MenuPolicy extends TenantPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasTenantPermission(
            $user,
            'menus.view'
        ) && $this->hasTenantAccess(
            $user,
            $user->tenant_id
        );
    }

    public function view(User $user, Menu $menu): bool
    {
        return $this->hasTenantPermission(
            $user,
            'menus.view'
        ) && $this->hasTenantAccess(
            $user,
            $menu->tenant_id
        );
    }

    public function create(User $user): bool
    {
        return $this->hasTenantPermission(
            $user,
            'menus.create'
        ) && $this->hasTenantAccess(
            $user,
            $user->tenant_id
        );
    }

    public function update(User $user, Menu $menu): bool
    {
        return $this->hasTenantPermission(
            $user,
            'menus.update'
        ) && $this->hasTenantAccess(
            $user,
            $menu->tenant_id
        );
    }

    public function delete(User $user, Menu $menu): bool
    {
        return $this->hasTenantPermission(
            $user,
            'menus.delete'
        ) && $this->hasTenantAccess(
            $user,
            $menu->tenant_id
        );
    }
}