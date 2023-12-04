<?php

namespace NextDeveloper\IAAS\Services\AbstractServices;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use NextDeveloper\IAM\Helpers\UserHelper;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\Commons\Helpers\DatabaseHelper;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Database\Filters\VirtualMachinesQueryFilter;
use NextDeveloper\Commons\Exceptions\ModelNotFoundException;
use NextDeveloper\IAAS\Events\VirtualMachines\VirtualMachinesCreatedEvent;
use NextDeveloper\IAAS\Events\VirtualMachines\VirtualMachinesCreatingEvent;
use NextDeveloper\IAAS\Events\VirtualMachines\VirtualMachinesUpdatedEvent;
use NextDeveloper\IAAS\Events\VirtualMachines\VirtualMachinesUpdatingEvent;
use NextDeveloper\IAAS\Events\VirtualMachines\VirtualMachinesDeletedEvent;
use NextDeveloper\IAAS\Events\VirtualMachines\VirtualMachinesDeletingEvent;

/**
 * This class is responsible from managing the data for VirtualMachines
 *
 * Class VirtualMachinesService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class AbstractVirtualMachinesService
{
    public static function get(VirtualMachinesQueryFilter $filter = null, array $params = []) : Collection|LengthAwarePaginator
    {
        $enablePaginate = array_key_exists('paginate', $params);

        /**
        * Here we are adding null request since if filter is null, this means that this function is called from
        * non http application. This is actually not I think its a correct way to handle this problem but it's a workaround.
        *
        * Please let me know if you have any other idea about this; baris.bulut@nextdeveloper.com
        */
        if($filter == null) {
            $filter = new VirtualMachinesQueryFilter(new Request());
        }

        $perPage = config('commons.pagination.per_page');

        if($perPage == null) {
            $perPage = 20;
        }

        if(array_key_exists('per_page', $params)) {
            $perPage = intval($params['per_page']);

            if($perPage == 0) {
                $perPage = 20;
            }
        }

        if(array_key_exists('orderBy', $params)) {
            $filter->orderBy($params['orderBy']);
        }

        $model = VirtualMachines::filter($filter);

        if($model && $enablePaginate) {
            return $model->paginate($perPage);
        } else {
            return $model->get();
        }
    }

    public static function getAll()
    {
        return VirtualMachines::all();
    }

    /**
     * This method returns the model by looking at reference id
     *
     * @param  $ref
     * @return mixed
     */
    public static function getByRef($ref) : ?VirtualMachines
    {
        return VirtualMachines::findByRef($ref);
    }

    /**
     * This method returns the model by lookint at its id
     *
     * @param  $id
     * @return VirtualMachines|null
     */
    public static function getById($id) : ?VirtualMachines
    {
        return VirtualMachines::where('id', $id)->first();
    }

    /**
     * This method returns the sub objects of the related models
     *
     * @param  $uuid
     * @param  $object
     * @return void
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public static function relatedObjects($uuid, $object)
    {
        try {
            $obj = VirtualMachines::where('uuid', $uuid)->first();

            if(!$obj) {
                throw new ModelNotFoundException('Cannot find the related model');
            }

            if($obj) {
                return $obj->$object;
            }
        } catch (\Exception $e) {
            dd($e);
        }
    }

    /**
     * This method created the model from an array.
     *
     * Throws an exception if stuck with any problem.
     *
     * @param  array $data
     * @return mixed
     * @throw  Exception
     */
    public static function create(array $data)
    {
        event(new VirtualMachinesCreatingEvent());

        if (array_key_exists('iaas_cloud_node_id', $data)) {
            $data['iaas_cloud_node_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAAS\Database\Models\CloudNodes',
                $data['iaas_cloud_node_id']
            );
        }
        if (array_key_exists('iaas_compute_member_id', $data)) {
            $data['iaas_compute_member_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAAS\Database\Models\ComputeMembers',
                $data['iaas_compute_member_id']
            );
        }
        if (array_key_exists('iam_account_id', $data)) {
            $data['iam_account_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAM\Database\Models\Accounts',
                $data['iam_account_id']
            );
        }
        if (array_key_exists('iam_user_id', $data)) {
            $data['iam_user_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAM\Database\Models\Users',
                $data['iam_user_id']
            );
        }
        if (array_key_exists('from_template_id', $data)) {
            $data['from_template_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\\Database\Models\FromTemplates',
                $data['from_template_id']
            );
        }
    
        try {
            $model = VirtualMachines::create($data);
        } catch(\Exception $e) {
            throw $e;
        }

        event(new VirtualMachinesCreatedEvent($model));

        return $model->fresh();
    }

    /**
     This function expects the ID inside the object.
    
     @param  array $data
     @return VirtualMachines
     */
    public static function updateRaw(array $data) : ?VirtualMachines
    {
        if(array_key_exists('id', $data)) {
            return self::update($data['id'], $data);
        }

        return null;
    }

    /**
     * This method updated the model from an array.
     *
     * Throws an exception if stuck with any problem.
     *
     * @param
     * @param  array $data
     * @return mixed
     * @throw  Exception
     */
    public static function update($id, array $data)
    {
        $model = VirtualMachines::where('uuid', $id)->first();

        if (array_key_exists('iaas_cloud_node_id', $data)) {
            $data['iaas_cloud_node_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAAS\Database\Models\CloudNodes',
                $data['iaas_cloud_node_id']
            );
        }
        if (array_key_exists('iaas_compute_member_id', $data)) {
            $data['iaas_compute_member_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAAS\Database\Models\ComputeMembers',
                $data['iaas_compute_member_id']
            );
        }
        if (array_key_exists('iam_account_id', $data)) {
            $data['iam_account_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAM\Database\Models\Accounts',
                $data['iam_account_id']
            );
        }
        if (array_key_exists('iam_user_id', $data)) {
            $data['iam_user_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAM\Database\Models\Users',
                $data['iam_user_id']
            );
        }
        if (array_key_exists('from_template_id', $data)) {
            $data['from_template_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\\Database\Models\FromTemplates',
                $data['from_template_id']
            );
        }
    
        event(new VirtualMachinesUpdatingEvent($model));

        try {
            $isUpdated = $model->update($data);
            $model = $model->fresh();
        } catch(\Exception $e) {
            throw $e;
        }

        event(new VirtualMachinesUpdatedEvent($model));

        return $model->fresh();
    }

    /**
     * This method updated the model from an array.
     *
     * Throws an exception if stuck with any problem.
     *
     * @param
     * @param  array $data
     * @return mixed
     * @throw  Exception
     */
    public static function delete($id)
    {
        $model = VirtualMachines::where('uuid', $id)->first();

        event(new VirtualMachinesDeletingEvent());

        try {
            $model = $model->delete();
        } catch(\Exception $e) {
            throw $e;
        }

        return $model;
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
