<?php

namespace NextDeveloper\IAAS\Services\AbstractServices;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use NextDeveloper\IAM\Helpers\UserHelper;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\Commons\Helpers\DatabaseHelper;
use NextDeveloper\IAAS\Database\Models\IaasStoragePool;
use NextDeveloper\IAAS\Database\Filters\IaasStoragePoolQueryFilter;
use NextDeveloper\IAAS\Events\IaasStoragePool\IaasStoragePoolCreatedEvent;
use NextDeveloper\IAAS\Events\IaasStoragePool\IaasStoragePoolCreatingEvent;
use NextDeveloper\IAAS\Events\IaasStoragePool\IaasStoragePoolUpdatedEvent;
use NextDeveloper\IAAS\Events\IaasStoragePool\IaasStoragePoolUpdatingEvent;
use NextDeveloper\IAAS\Events\IaasStoragePool\IaasStoragePoolDeletedEvent;
use NextDeveloper\IAAS\Events\IaasStoragePool\IaasStoragePoolDeletingEvent;


/**
* This class is responsible from managing the data for IaasStoragePool
*
* Class IaasStoragePoolService.
*
* @package NextDeveloper\IAAS\Database\Models
*/
class AbstractIaasStoragePoolService {
    public static function get(IaasStoragePoolQueryFilter $filter = null, array $params = []) : Collection|LengthAwarePaginator {
        $enablePaginate = array_key_exists('paginate', $params);

        /**
        * Here we are adding null request since if filter is null, this means that this function is called from
        * non http application. This is actually not I think its a correct way to handle this problem but it's a workaround.
        *
        * Please let me know if you have any other idea about this; baris.bulut@nextdeveloper.com
        */
        if($filter == null)
            $filter = new IaasStoragePoolQueryFilter(new Request());

        $perPage = config('commons.pagination.per_page');

        if($perPage == null)
            $perPage = 20;

        if(array_key_exists('per_page', $params)) {
            $perPage = intval($params['per_page']);

            if($perPage == 0)
                $perPage = 20;
        }

        if(array_key_exists('orderBy', $params)) {
            $filter->orderBy($params['orderBy']);
        }

        $model = IaasStoragePool::filter($filter);

        if($model && $enablePaginate)
            return $model->paginate($perPage);
        else
            return $model->get();
    }

    public static function getAll() {
        return IaasStoragePool::all();
    }

    /**
    * This method returns the model by looking at reference id
    *
    * @param $ref
    * @return mixed
    */
    public static function getByRef($ref) : ?IaasStoragePool {
        return IaasStoragePool::findByRef($ref);
    }

    /**
    * This method returns the model by lookint at its id
    *
    * @param $id
    * @return IaasStoragePool|null
    */
    public static function getById($id) : ?IaasStoragePool {
        return IaasStoragePool::where('id', $id)->first();
    }

    /**
    * This method created the model from an array.
    *
    * Throws an exception if stuck with any problem.
    *
    * @param array $data
    * @return mixed
    * @throw Exception
    */
    public static function create(array $data) {
        event( new IaasStoragePoolCreatingEvent() );

                if (array_key_exists('iaas_cloud_node_id', $data))
            $data['iaas_cloud_node_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAAS\Database\Models\IaasCloudNode',
                $data['iaas_cloud_node_id']
            );
	        if (array_key_exists('iam_account_id', $data))
            $data['iam_account_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAM\Database\Models\IamAccount',
                $data['iam_account_id']
            );
	        if (array_key_exists('iam_user_id', $data))
            $data['iam_user_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAM\Database\Models\IamUser',
                $data['iam_user_id']
            );
	        
        try {
            $model = IaasStoragePool::create($data);
        } catch(\Exception $e) {
            throw $e;
        }

        event( new IaasStoragePoolCreatedEvent($model) );

        return $model->fresh();
    }

/**
* This function expects the ID inside the object.
*
* @param array $data
* @return IaasStoragePool
*/
public static function updateRaw(array $data) : ?IaasStoragePool
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
    * @param array $data
    * @return mixed
    * @throw Exception
    */
    public static function update($id, array $data) {
        $model = IaasStoragePool::where('uuid', $id)->first();

                if (array_key_exists('iaas_cloud_node_id', $data))
            $data['iaas_cloud_node_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAAS\Database\Models\IaasCloudNode',
                $data['iaas_cloud_node_id']
            );
	        if (array_key_exists('iam_account_id', $data))
            $data['iam_account_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAM\Database\Models\IamAccount',
                $data['iam_account_id']
            );
	        if (array_key_exists('iam_user_id', $data))
            $data['iam_user_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAM\Database\Models\IamUser',
                $data['iam_user_id']
            );
	
        event( new IaasStoragePoolUpdatingEvent($model) );

        try {
           $isUpdated = $model->update($data);
           $model = $model->fresh();
        } catch(\Exception $e) {
           throw $e;
        }

        event( new IaasStoragePoolUpdatedEvent($model) );

        return $model->fresh();
    }

    /**
    * This method updated the model from an array.
    *
    * Throws an exception if stuck with any problem.
    *
    * @param
    * @param array $data
    * @return mixed
    * @throw Exception
    */
    public static function delete($id, array $data) {
        $model = IaasStoragePool::where('uuid', $id)->first();

        event( new IaasStoragePoolDeletingEvent() );

        try {
            $model = $model->delete();
        } catch(\Exception $e) {
            throw $e;
        }

        event( new IaasStoragePoolDeletedEvent($model) );

        return $model;
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
