<?php

namespace App\Policies;

use App\Models\MenuItem;
use App\Models\User;

class MenuItemPolicy extends TenantPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasTenantPermission($user, 'menu_items.view') && $this->hasTenantAccess($user, $user->tenant_id);
    }

    public function view(User $user, MenuItem $menuItem): bool
    {
        return $this->hasTenantPermission($user, 'menu_items.view')
            && $this->hasTenantAccess($user, $this->tenantOf($menuItem));
    }

    public function create(User $user): bool
    {
        return $this->hasTenantPermission($user, 'menu_items.create') && $this->hasTenantAccess($user, $user->tenant_id);
    }

    public function update(User $user, MenuItem $menuItem): bool
    {
        return $this->hasTenantPermission($user, 'menu_items.update')
            && $this->hasTenantAccess($user, $this->tenantOf($menuItem));
    }

    public function delete(User $user, MenuItem $menuItem): bool
    {
        return $this->hasTenantPermission($user, 'menu_items.delete')
            && $this->hasTenantAccess($user, $this->tenantOf($menuItem));
    }

    protected function tenantOf(MenuItem $menuItem): ?int
    {
        return $menuItem->menu?->tenant_id
            ?? $menuItem->menu()->value('tenant_id');
    }
}
