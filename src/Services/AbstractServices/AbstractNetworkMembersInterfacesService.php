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
use NextDeveloper\IAAS\Database\Models\NetworkMembersInterfaces;
use NextDeveloper\IAAS\Database\Filters\NetworkMembersInterfacesQueryFilter;
use NextDeveloper\Commons\Exceptions\ModelNotFoundException;
use NextDeveloper\Events\Services\Events;

/**
 * This class is responsible from managing the data for NetworkMembersInterfaces
 *
 * Class NetworkMembersInterfacesService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class AbstractNetworkMembersInterfacesService
{
    public static function get(NetworkMembersInterfacesQueryFilter $filter = null, array $params = []) : Collection|LengthAwarePaginator
    {
        $enablePaginate = array_key_exists('paginate', $params);

        /**
        * Here we are adding null request since if filter is null, this means that this function is called from
        * non http application. This is actually not I think its a correct way to handle this problem but it's a workaround.
        *
        * Please let me know if you have any other idea about this; baris.bulut@nextdeveloper.com
        */
        if($filter == null) {
            $filter = new NetworkMembersInterfacesQueryFilter(new Request());
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

        $model = NetworkMembersInterfaces::filter($filter);

        if($model && $enablePaginate) {
            return $model->paginate($perPage);
        } else {
            return $model->get();
        }
    }

    public static function getAll()
    {
        return NetworkMembersInterfaces::all();
    }

    /**
     * This method returns the model by looking at reference id
     *
     * @param  $ref
     * @return mixed
     */
    public static function getByRef($ref) : ?NetworkMembersInterfaces
    {
        return NetworkMembersInterfaces::findByRef($ref);
    }

    public static function getActions()
    {
        $model = NetworkMembersInterfaces::class;

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
        $object = NetworkMembersInterfaces::where('uuid', $objectId)->first();

        $action = '\\NextDeveloper\\IAAS\\Actions\\NetworkMembersInterfaces\\' . Str::studly($action);

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
     * @return NetworkMembersInterfaces|null
     */
    public static function getById($id) : ?NetworkMembersInterfaces
    {
        return NetworkMembersInterfaces::where('id', $id)->first();
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
            $obj = NetworkMembersInterfaces::where('uuid', $uuid)->first();

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
        if (array_key_exists('iaas_network_member_id', $data)) {
            $data['iaas_network_member_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAAS\Database\Models\NetworkMembers',
                $data['iaas_network_member_id']
            );
        }
                        
        try {
            $model = NetworkMembersInterfaces::create($data);
        } catch(\Exception $e) {
            throw $e;
        }

        Events::fire('created:NextDeveloper\IAAS\NetworkMembersInterfaces', $model);

        return $model->fresh();
    }

    /**
     * This function expects the ID inside the object.
     *
     * @param  array $data
     * @return NetworkMembersInterfaces
     */
    public static function updateRaw(array $data) : ?NetworkMembersInterfaces
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
        $model = NetworkMembersInterfaces::where('uuid', $id)->first();

        if (array_key_exists('iaas_network_member_id', $data)) {
            $data['iaas_network_member_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAAS\Database\Models\NetworkMembers',
                $data['iaas_network_member_id']
            );
        }
    
        Events::fire('updating:NextDeveloper\IAAS\NetworkMembersInterfaces', $model);

        try {
            $isUpdated = $model->update($data);
            $model = $model->fresh();
        } catch(\Exception $e) {
            throw $e;
        }

        Events::fire('updated:NextDeveloper\IAAS\NetworkMembersInterfaces', $model);

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
        $model = NetworkMembersInterfaces::where('uuid', $id)->first();

        Events::fire('deleted:NextDeveloper\IAAS\NetworkMembersInterfaces', $model);

        try {
            $model = $model->delete();
        } catch(\Exception $e) {
            throw $e;
        }

        return $model;
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}