<?php

namespace NextDeveloper\IAAS\Services;

use Illuminate\Database\Eloquent\Collection;
use NextDeveloper\IAAS\Database\Filters\VirtualMachinesPerspectiveQueryFilter;
use NextDeveloper\IAAS\Database\Models\VirtualMachinesPerspective;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractVirtualMachinesPerspectiveService;
use NextDeveloper\IAM\Database\Models\Accounts;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * This class is responsible from managing the data for VirtualMachinesPerspective
 *
 * Class VirtualMachinesPerspectiveService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class VirtualMachinesPerspectiveService extends AbstractVirtualMachinesPerspectiveService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
    public static function get(VirtualMachinesPerspectiveQueryFilter $filter = null, array $params = []): \Illuminate\Database\Eloquent\Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        if(array_key_exists('snapshot_of_virtual_machine', $filter->filters())) {
            $vm = VirtualMachinesPerspective::withoutGlobalScopes()
                ->where('uuid', $filter->filters()['snapshot_of_virtual_machine'])
                ->first();

            $filter->updateValue('snapshot_of_virtual_machine', $vm->id);
        }

        if(UserHelper::hasRole('datacenter-admin') || UserHelper::hasRole('cloud-node-admin')) {
            $model = VirtualMachinesPerspective::filter($filter);

            if(array_key_exists('iamAccountId', $filter->filters())) {
                $iamAccount = Accounts::withoutGlobalScopes()->where('uuid', $filter->filters()['iamAccountId'])->first();

                if(!$iamAccount) {
                    return new Collection();
                }

                $model->where('iam_account_id', $iamAccount->id);
            }

            return $model->paginate();
        }

        return parent::get($filter, $params);
    }
}
