<?php

namespace NextDeveloper\IAAS\Services\AbstractServices;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use NextDeveloper\IAM\Helpers\UserHelper;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\Commons\Helpers\DatabaseHelper;
use NextDeveloper\IAAS\Database\Models\IaasComputeMember;
use NextDeveloper\IAAS\Database\Filters\IaasComputeMemberQueryFilter;
use NextDeveloper\IAAS\Events\IaasComputeMember\IaasComputeMemberCreatedEvent;
use NextDeveloper\IAAS\Events\IaasComputeMember\IaasComputeMemberCreatingEvent;
use NextDeveloper\IAAS\Events\IaasComputeMember\IaasComputeMemberUpdatedEvent;
use NextDeveloper\IAAS\Events\IaasComputeMember\IaasComputeMemberUpdatingEvent;
use NextDeveloper\IAAS\Events\IaasComputeMember\IaasComputeMemberDeletedEvent;
use NextDeveloper\IAAS\Events\IaasComputeMember\IaasComputeMemberDeletingEvent;


/**
* This class is responsible from managing the data for IaasComputeMember
*
* Class IaasComputeMemberService.
*
* @package NextDeveloper\IAAS\Database\Models
*/
class AbstractIaasComputeMemberService {
    public static function get(IaasComputeMemberQueryFilter $filter = null, array $params = []) : Collection|LengthAwarePaginator {
        $enablePaginate = array_key_exists('paginate', $params);

        /**
        * Here we are adding null request since if filter is null, this means that this function is called from
        * non http application. This is actually not I think its a correct way to handle this problem but it's a workaround.
        *
        * Please let me know if you have any other idea about this; baris.bulut@nextdeveloper.com
        */
        if($filter == null)
            $filter = new IaasComputeMemberQueryFilter(new Request());

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

        $model = IaasComputeMember::filter($filter);

        if($model && $enablePaginate)
            return $model->paginate($perPage);
        else
            return $model->get();
    }

    public static function getAll() {
        return IaasComputeMember::all();
    }

    /**
    * This method returns the model by looking at reference id
    *
    * @param $ref
    * @return mixed
    */
    public static function getByRef($ref) : ?IaasComputeMember {
        return IaasComputeMember::findByRef($ref);
    }

    /**
    * This method returns the model by lookint at its id
    *
    * @param $id
    * @return IaasComputeMember|null
    */
    public static function getById($id) : ?IaasComputeMember {
        return IaasComputeMember::where('id', $id)->first();
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
        event( new IaasComputeMemberCreatingEvent() );

                if (array_key_exists('iaas_compute_pool_id', $data))
            $data['iaas_compute_pool_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAAS\Database\Models\IaasComputePool',
                $data['iaas_compute_pool_id']
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
            $model = IaasComputeMember::create($data);
        } catch(\Exception $e) {
            throw $e;
        }

        event( new IaasComputeMemberCreatedEvent($model) );

        return $model->fresh();
    }

/**
* This function expects the ID inside the object.
*
* @param array $data
* @return IaasComputeMember
*/
public static function updateRaw(array $data) : ?IaasComputeMember
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
        $model = IaasComputeMember::where('uuid', $id)->first();

                if (array_key_exists('iaas_compute_pool_id', $data))
            $data['iaas_compute_pool_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAAS\Database\Models\IaasComputePool',
                $data['iaas_compute_pool_id']
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
	
        event( new IaasComputeMemberUpdatingEvent($model) );

        try {
           $isUpdated = $model->update($data);
           $model = $model->fresh();
        } catch(\Exception $e) {
           throw $e;
        }

        event( new IaasComputeMemberUpdatedEvent($model) );

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
        $model = IaasComputeMember::where('uuid', $id)->first();

        event( new IaasComputeMemberDeletingEvent() );

        try {
            $model = $model->delete();
        } catch(\Exception $e) {
            throw $e;
        }

        event( new IaasComputeMemberDeletedEvent($model) );

        return $model;
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
