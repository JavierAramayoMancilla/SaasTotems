<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (! Auth::check()) {
            return;
        }

        $user = Auth::user();

        if (! $user || ! method_exists($user, 'hasRole')) {
            return;
        }

        if ($user->hasRole('superadmin')) {
            return;
        }

        if (blank($user->tenant_id)) {
            $builder->whereRaw('0 = 1');
            return;
        }

        $builder->where($model->getTable() . '.tenant_id', $user->tenant_id);
    }
}
