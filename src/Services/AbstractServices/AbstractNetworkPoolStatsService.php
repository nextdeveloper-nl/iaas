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
use NextDeveloper\IAAS\Database\Models\NetworkPoolStats;
use NextDeveloper\IAAS\Database\Filters\NetworkPoolStatsQueryFilter;
use NextDeveloper\Commons\Exceptions\ModelNotFoundException;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\Commons\Exceptions\NotAllowedException;

/**
 * This class is responsible from managing the data for NetworkPoolStats
 *
 * Class NetworkPoolStatsService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class AbstractNetworkPoolStatsService
{
    public static function get(NetworkPoolStatsQueryFilter $filter = null, array $params = []) : Collection|LengthAwarePaginator
    {
        $enablePaginate = array_key_exists('paginate', $params);

        $request = new Request();

        /**
        * Here we are adding null request since if filter is null, this means that this function is called from
        * non http application. This is actually not I think its a correct way to handle this problem but it's a workaround.
        *
        * Please let me know if you have any other idea about this; baris.bulut@nextdeveloper.com
        */
        if($filter == null) {
            $filter = new NetworkPoolStatsQueryFilter($request);
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

        $model = NetworkPoolStats::filter($filter);

        if($enablePaginate) {
            //  We are using this because we have been experiencing huge security problem when we use the paginate method.
            //  The reason was, when the pagination method was using, somehow paginate was discarding all the filters.
            return new \Illuminate\Pagination\LengthAwarePaginator(
                $model->skip(($request->get('page', 1) - 1) * $perPage)->take($perPage)->get(),
                $model->count(),
                $perPage,
                $request->get('page', 1)
            );
        }

        return $model->get();
    }

    public static function getAll()
    {
        return NetworkPoolStats::all();
    }

    /**
     * This method returns the model by looking at reference id
     *
     * @param  $ref
     * @return mixed
     */
    public static function getByRef($ref) : ?NetworkPoolStats
    {
        return NetworkPoolStats::findByRef($ref);
    }

    public static function getActions()
    {
        $model = NetworkPoolStats::class;

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
        $object = NetworkPoolStats::where('uuid', $objectId)->first();

        $action = AvailableActions::where('name', $action)
            ->where('input', 'NextDeveloper\IAAS\NetworkPoolStats')
            ->first();

        $class = $action->class;

        if(class_exists($class)) {
            $action = new $class($object, $params);
            $actionId = $action->getActionId();

            dispatch($action);

            return $actionId;
        }

        return null;
    }

    /**
     * This method returns the model by lookint at its id
     *
     * @param  $id
     * @return NetworkPoolStats|null
     */
    public static function getById($id) : ?NetworkPoolStats
    {
        return NetworkPoolStats::where('id', $id)->first();
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
            $obj = NetworkPoolStats::where('uuid', $uuid)->first();

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
        if (array_key_exists('iaas_network_pool_id', $data)) {
            $data['iaas_network_pool_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAAS\Database\Models\NetworkPools',
                $data['iaas_network_pool_id']
            );
        }
                        
        try {
            $model = NetworkPoolStats::create($data);
        } catch(\Exception $e) {
            throw $e;
        }

        return $model->fresh();
    }

    /**
     * This function expects the ID inside the object.
     *
     * @param  array $data
     * @return NetworkPoolStats
     */
    public static function updateRaw(array $data) : ?NetworkPoolStats
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
        $model = NetworkPoolStats::where('uuid', $id)->first();

        if(!$model) {
            throw new NotAllowedException(
                'We cannot find the related object to update. ' .
                'Maybe you dont have the permission to update this object?'
            );
        }

        if (array_key_exists('iaas_network_pool_id', $data)) {
            $data['iaas_network_pool_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAAS\Database\Models\NetworkPools',
                $data['iaas_network_pool_id']
            );
        }
    
        try {
            $isUpdated = $model->update($data);
            $model = $model->fresh();
        } catch(\Exception $e) {
            throw $e;
        }

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
        $model = NetworkPoolStats::where('uuid', $id)->first();

        if(!$model) {
            throw new NotAllowedException(
                'We cannot find the related object to delete. ' .
                'Maybe you dont have the permission to update this object?'
            );
        }

        try {
            $model = $model->delete();
        } catch(\Exception $e) {
            throw $e;
        }

        return $model;
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
