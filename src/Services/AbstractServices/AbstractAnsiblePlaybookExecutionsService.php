<?php

namespace NextDeveloper\IAAS\Services\AbstractServices;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use NextDeveloper\IAM\Helpers\UserHelper;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\Commons\Helpers\DatabaseHelper;
use NextDeveloper\Commons\Database\Models\AvailableActions;
use NextDeveloper\IAAS\Database\Models\AnsiblePlaybookExecutions;
use NextDeveloper\IAAS\Database\Filters\AnsiblePlaybookExecutionsQueryFilter;
use NextDeveloper\Commons\Exceptions\ModelNotFoundException;
use NextDeveloper\Events\Services\Events;

/**
 * This class is responsible from managing the data for AnsiblePlaybookExecutions
 *
 * Class AnsiblePlaybookExecutionsService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class AbstractAnsiblePlaybookExecutionsService
{
    public static function get(AnsiblePlaybookExecutionsQueryFilter $filter = null, array $params = []) : Collection|LengthAwarePaginator
    {
        $enablePaginate = array_key_exists('paginate', $params);

        /**
        * Here we are adding null request since if filter is null, this means that this function is called from
        * non http application. This is actually not I think its a correct way to handle this problem but it's a workaround.
        *
        * Please let me know if you have any other idea about this; baris.bulut@nextdeveloper.com
        */
        if($filter == null) {
            $filter = new AnsiblePlaybookExecutionsQueryFilter(new Request());
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

        $model = AnsiblePlaybookExecutions::filter($filter);

        if($model && $enablePaginate) {
            return $model->paginate($perPage);
        } else {
            return $model->get();
        }
    }

    public static function getAll()
    {
        return AnsiblePlaybookExecutions::all();
    }

    /**
     * This method returns the model by looking at reference id
     *
     * @param  $ref
     * @return mixed
     */
    public static function getByRef($ref) : ?AnsiblePlaybookExecutions
    {
        return AnsiblePlaybookExecutions::findByRef($ref);
    }

    public static function getActions()
    {
        $model = AnsiblePlaybookExecutions::class;

        $model = Str::remove('Database\\Models\\', $model);

        $actions = AvailableActions::where('input', $model)
            ->get();

        return $actions;
    }

    /**
     * This method initiates the related action with the given parameters.
     */
    public static function doAction($objectId, $action, ...$params)
    {
        $object = AnsiblePlaybookExecutions::where('uuid', $objectId)->first();

        $action = '\\NextDeveloper\\IAAS\\Actions\\AnsiblePlaybookExecutions\\' . Str::studly($action);

        if(class_exists($action)) {
            $action = new $action($object, $params);

            dispatch($action);

            return $action->getActionId();
        }

        return null;
    }

    /**
     * This method returns the model by lookint at its id
     *
     * @param  $id
     * @return AnsiblePlaybookExecutions|null
     */
    public static function getById($id) : ?AnsiblePlaybookExecutions
    {
        return AnsiblePlaybookExecutions::where('id', $id)->first();
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
            $obj = AnsiblePlaybookExecutions::where('uuid', $uuid)->first();

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
        if (array_key_exists('iaas_virtual_machine_id', $data)) {
            $data['iaas_virtual_machine_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAAS\Database\Models\VirtualMachines',
                $data['iaas_virtual_machine_id']
            );
        }
        if (array_key_exists('iam_account_id', $data)) {
            $data['iam_account_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAM\Database\Models\Accounts',
                $data['iam_account_id']
            );
        }
            
        if(!array_key_exists('iam_account_id', $data)) {
            $data['iam_account_id'] = UserHelper::currentAccount()->id;
        }
        if (array_key_exists('iam_user_id', $data)) {
            $data['iam_user_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAM\Database\Models\Users',
                $data['iam_user_id']
            );
        }
                    
        if(!array_key_exists('iam_user_id', $data)) {
            $data['iam_user_id']    = UserHelper::me()->id;
        }
            
        try {
            $model = AnsiblePlaybookExecutions::create($data);
        } catch(\Exception $e) {
            throw $e;
        }

        Events::fire('created:NextDeveloper\IAAS\AnsiblePlaybookExecutions', $model);

        return $model->fresh();
    }

    /**
     * This function expects the ID inside the object.
     *
     * @param  array $data
     * @return AnsiblePlaybookExecutions
     */
    public static function updateRaw(array $data) : ?AnsiblePlaybookExecutions
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
        $model = AnsiblePlaybookExecutions::where('uuid', $id)->first();

        if (array_key_exists('iaas_virtual_machine_id', $data)) {
            $data['iaas_virtual_machine_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAAS\Database\Models\VirtualMachines',
                $data['iaas_virtual_machine_id']
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
    
        Events::fire('updating:NextDeveloper\IAAS\AnsiblePlaybookExecutions', $model);

        try {
            $isUpdated = $model->update($data);
            $model = $model->fresh();
        } catch(\Exception $e) {
            throw $e;
        }

        Events::fire('updated:NextDeveloper\IAAS\AnsiblePlaybookExecutions', $model);

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
        $model = AnsiblePlaybookExecutions::where('uuid', $id)->first();

        Events::fire('deleted:NextDeveloper\IAAS\AnsiblePlaybookExecutions', $model);

        try {
            $model = $model->delete();
        } catch(\Exception $e) {
            throw $e;
        }

        return $model;
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}