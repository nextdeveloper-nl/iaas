<?php

namespace NextDeveloper\IAAS\Services\AbstractServices;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use NextDeveloper\IAM\Helpers\UserHelper;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\Commons\Helpers\DatabaseHelper;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\IAAS\Database\Filters\StorageVolumesQueryFilter;
use NextDeveloper\IAAS\Events\StorageVolumes\StorageVolumesCreatedEvent;
use NextDeveloper\IAAS\Events\StorageVolumes\StorageVolumesCreatingEvent;
use NextDeveloper\IAAS\Events\StorageVolumes\StorageVolumesUpdatedEvent;
use NextDeveloper\IAAS\Events\StorageVolumes\StorageVolumesUpdatingEvent;
use NextDeveloper\IAAS\Events\StorageVolumes\StorageVolumesDeletedEvent;
use NextDeveloper\IAAS\Events\StorageVolumes\StorageVolumesDeletingEvent;


/**
 * This class is responsible from managing the data for StorageVolumes
 *
 * Class StorageVolumesService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class AbstractStorageVolumesService
{
    public static function get(StorageVolumesQueryFilter $filter = null, array $params = []) : Collection|LengthAwarePaginator
    {
        $enablePaginate = array_key_exists('paginate', $params);

        /**
        * Here we are adding null request since if filter is null, this means that this function is called from
        * non http application. This is actually not I think its a correct way to handle this problem but it's a workaround.
        *
        * Please let me know if you have any other idea about this; baris.bulut@nextdeveloper.com
        */
        if($filter == null) {
            $filter = new StorageVolumesQueryFilter(new Request());
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

        $model = StorageVolumes::filter($filter);

        if($model && $enablePaginate) {
            return $model->paginate($perPage);
        } else {
            return $model->get();
        }
    }

    public static function getAll()
    {
        return StorageVolumes::all();
    }

    /**
     * This method returns the model by looking at reference id
     *
     * @param  $ref
     * @return mixed
     */
    public static function getByRef($ref) : ?StorageVolumes
    {
        return StorageVolumes::findByRef($ref);
    }

    /**
     * This method returns the model by lookint at its id
     *
     * @param  $id
     * @return StorageVolumes|null
     */
    public static function getById($id) : ?StorageVolumes
    {
        return StorageVolumes::where('id', $id)->first();
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
        event(new StorageVolumesCreatingEvent());

        if (array_key_exists('iaas_storage_pool_id', $data)) {
            $data['iaas_storage_pool_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAAS\Database\Models\StoragePools',
                $data['iaas_storage_pool_id']
            );
        }
        if (array_key_exists('iaas_storage_member_id', $data)) {
            $data['iaas_storage_member_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAAS\Database\Models\StorageMembers',
                $data['iaas_storage_member_id']
            );
        }
    
        try {
            $model = StorageVolumes::create($data);
        } catch(\Exception $e) {
            throw $e;
        }

        event(new StorageVolumesCreatedEvent($model));

        return $model->fresh();
    }

    /**
     This function expects the ID inside the object.
    
     @param  array $data
     @return StorageVolumes
     */
    public static function updateRaw(array $data) : ?StorageVolumes
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
        $model = StorageVolumes::where('uuid', $id)->first();

        if (array_key_exists('iaas_storage_pool_id', $data)) {
            $data['iaas_storage_pool_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAAS\Database\Models\StoragePools',
                $data['iaas_storage_pool_id']
            );
        }
        if (array_key_exists('iaas_storage_member_id', $data)) {
            $data['iaas_storage_member_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\IAAS\Database\Models\StorageMembers',
                $data['iaas_storage_member_id']
            );
        }
    
        event(new StorageVolumesUpdatingEvent($model));

        try {
            $isUpdated = $model->update($data);
            $model = $model->fresh();
        } catch(\Exception $e) {
            throw $e;
        }

        event(new StorageVolumesUpdatedEvent($model));

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
        $model = StorageVolumes::where('uuid', $id)->first();

        event(new StorageVolumesDeletingEvent());

        try {
            $model = $model->delete();
        } catch(\Exception $e) {
            throw $e;
        }

        return $model;
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
