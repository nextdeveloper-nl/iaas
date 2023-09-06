<?php

namespace NextDeveloper\IAAS\Services\AbstractServices;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use NextDeveloper\IAM\Helpers\UserHelper;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\Commons\Helpers\DatabaseHelper;
use NextDeveloper\IAAS\Database\Models\IaasComputePool;
use NextDeveloper\IAAS\Database\Filters\IaasComputePoolQueryFilter;
use NextDeveloper\IAAS\Events\IaasComputePool\IaasComputePoolCreatedEvent;
use NextDeveloper\IAAS\Events\IaasComputePool\IaasComputePoolCreatingEvent;
use NextDeveloper\IAAS\Events\IaasComputePool\IaasComputePoolUpdatedEvent;
use NextDeveloper\IAAS\Events\IaasComputePool\IaasComputePoolUpdatingEvent;
use NextDeveloper\IAAS\Events\IaasComputePool\IaasComputePoolDeletedEvent;
use NextDeveloper\IAAS\Events\IaasComputePool\IaasComputePoolDeletingEvent;


/**
* This class is responsible from managing the data for IaasComputePool
*
* Class IaasComputePoolService.
*
* @package NextDeveloper\IAAS\Database\Models
*/
class AbstractIaasComputePoolService {
    public static function get(IaasComputePoolQueryFilter $filter = null, array $params = []) : Collection|LengthAwarePaginator {
        $enablePaginate = array_key_exists('paginate', $params);

        /**
        * Here we are adding null request since if filter is null, this means that this function is called from
        * non http application. This is actually not I think its a correct way to handle this problem but it's a workaround.
        *
        * Please let me know if you have any other idea about this; baris.bulut@nextdeveloper.com
        */
        if($filter == null)
            $filter = new IaasComputePoolQueryFilter(new Request());

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

        $model = IaasComputePool::filter($filter);

        if($model && $enablePaginate)
            return $model->paginate($perPage);
        else
            return $model->get();
    }

    public static function getAll() {
        return IaasComputePool::all();
    }

    /**
    * This method returns the model by looking at reference id
    *
    * @param $ref
    * @return mixed
    */
    public static function getByRef($ref) : ?IaasComputePool {
        return IaasComputePool::findByRef($ref);
    }

    /**
    * This method returns the model by lookint at its id
    *
    * @param $id
    * @return IaasComputePool|null
    */
    public static function getById($id) : ?IaasComputePool {
        return IaasComputePool::where('id', $id)->first();
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
        event( new IaasComputePoolCreatingEvent() );

                if (array_key_exists('iaas_datacenter_id', $data))
            $data['iaas_datacenter_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAAS\Database\Models\IaasDatacenter',
                $data['iaas_datacenter_id']
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
            $model = IaasComputePool::create($data);
        } catch(\Exception $e) {
            throw $e;
        }

        event( new IaasComputePoolCreatedEvent($model) );

        return $model->fresh();
    }

/**
* This function expects the ID inside the object.
*
* @param array $data
* @return IaasComputePool
*/
public static function updateRaw(array $data) : ?IaasComputePool
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
        $model = IaasComputePool::where('uuid', $id)->first();

                if (array_key_exists('iaas_datacenter_id', $data))
            $data['iaas_datacenter_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAAS\Database\Models\IaasDatacenter',
                $data['iaas_datacenter_id']
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
	
        event( new IaasComputePoolUpdatingEvent($model) );

        try {
           $isUpdated = $model->update($data);
           $model = $model->fresh();
        } catch(\Exception $e) {
           throw $e;
        }

        event( new IaasComputePoolUpdatedEvent($model) );

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
        $model = IaasComputePool::where('uuid', $id)->first();

        event( new IaasComputePoolDeletingEvent() );

        try {
            $model = $model->delete();
        } catch(\Exception $e) {
            throw $e;
        }

        event( new IaasComputePoolDeletedEvent($model) );

        return $model;
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
