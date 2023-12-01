<?php

namespace NextDeveloper\IAAS\Services\AbstractServices;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use NextDeveloper\IAM\Helpers\UserHelper;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\Commons\Helpers\DatabaseHelper;
use NextDeveloper\IAAS\Database\Models\NetworkPools;
use NextDeveloper\IAAS\Database\Filters\NetworkPoolsQueryFilter;
use NextDeveloper\IAAS\Events\NetworkPools\NetworkPoolsCreatedEvent;
use NextDeveloper\IAAS\Events\NetworkPools\NetworkPoolsCreatingEvent;
use NextDeveloper\IAAS\Events\NetworkPools\NetworkPoolsUpdatedEvent;
use NextDeveloper\IAAS\Events\NetworkPools\NetworkPoolsUpdatingEvent;
use NextDeveloper\IAAS\Events\NetworkPools\NetworkPoolsDeletedEvent;
use NextDeveloper\IAAS\Events\NetworkPools\NetworkPoolsDeletingEvent;


/**
 * This class is responsible from managing the data for NetworkPools
 *
 * Class NetworkPoolsService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class AbstractNetworkPoolsService
{
    public static function get(NetworkPoolsQueryFilter $filter = null, array $params = []) : Collection|LengthAwarePaginator
    {
        $enablePaginate = array_key_exists('paginate', $params);

        /**
        * Here we are adding null request since if filter is null, this means that this function is called from
        * non http application. This is actually not I think its a correct way to handle this problem but it's a workaround.
        *
        * Please let me know if you have any other idea about this; baris.bulut@nextdeveloper.com
        */
        if($filter == null) {
            $filter = new NetworkPoolsQueryFilter(new Request());
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

        $model = NetworkPools::filter($filter);

        if($model && $enablePaginate) {
            return $model->paginate($perPage);
        } else {
            return $model->get();
        }
    }

    public static function getAll()
    {
        return NetworkPools::all();
    }

    /**
     * This method returns the model by looking at reference id
     *
     * @param  $ref
     * @return mixed
     */
    public static function getByRef($ref) : ?NetworkPools
    {
        return NetworkPools::findByRef($ref);
    }

    /**
     * This method returns the model by lookint at its id
     *
     * @param  $id
     * @return NetworkPools|null
     */
    public static function getById($id) : ?NetworkPools
    {
        return NetworkPools::where('id', $id)->first();
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
        event(new NetworkPoolsCreatingEvent());

        if (array_key_exists('iaas_datacenter_id', $data)) {
            $data['iaas_datacenter_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAAS\Database\Models\Datacenters',
                $data['iaas_datacenter_id']
            );
        }
        if (array_key_exists('iaas_cloud_node_id', $data)) {
            $data['iaas_cloud_node_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAAS\Database\Models\CloudNodes',
                $data['iaas_cloud_node_id']
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
    
        try {
            $model = NetworkPools::create($data);
        } catch(\Exception $e) {
            throw $e;
        }

        event(new NetworkPoolsCreatedEvent($model));

        return $model->fresh();
    }

    /**
     This function expects the ID inside the object.
    
     @param  array $data
     @return NetworkPools
     */
    public static function updateRaw(array $data) : ?NetworkPools
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
        $model = NetworkPools::where('uuid', $id)->first();

        if (array_key_exists('iaas_datacenter_id', $data)) {
            $data['iaas_datacenter_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAAS\Database\Models\Datacenters',
                $data['iaas_datacenter_id']
            );
        }
        if (array_key_exists('iaas_cloud_node_id', $data)) {
            $data['iaas_cloud_node_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAAS\Database\Models\CloudNodes',
                $data['iaas_cloud_node_id']
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
    
        event(new NetworkPoolsUpdatingEvent($model));

        try {
            $isUpdated = $model->update($data);
            $model = $model->fresh();
        } catch(\Exception $e) {
            throw $e;
        }

        event(new NetworkPoolsUpdatedEvent($model));

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
        $model = NetworkPools::where('uuid', $id)->first();

        event(new NetworkPoolsDeletingEvent());

        try {
            $model = $model->delete();
        } catch(\Exception $e) {
            throw $e;
        }

        return $model;
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
