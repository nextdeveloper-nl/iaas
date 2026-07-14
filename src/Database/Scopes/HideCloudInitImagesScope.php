<?php

namespace NextDeveloper\IAAS\Database\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Hides auto-generated cloud-init configuration ISOs (config-<vm-uuid>.iso) from
 * repository image listings. Internal cloud-init flows must bypass this scope with
 * withoutGlobalScope(HideCloudInitImagesScope::class).
 */
class HideCloudInitImagesScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $table = $model->getTable();

        $builder->where(function (Builder $query) use ($table): void {
            $query->whereNull($table . '.name')
                ->orWhere($table . '.name', 'not like', 'config-%');
        });
    }
}
