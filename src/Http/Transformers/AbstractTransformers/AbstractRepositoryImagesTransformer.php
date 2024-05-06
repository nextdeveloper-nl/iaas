<?php

namespace NextDeveloper\IAAS\Http\Transformers\AbstractTransformers;

use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;

/**
 * Class RepositoryImagesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractRepositoryImagesTransformer extends AbstractTransformer
{

    /**
     * @param RepositoryImages $model
     *
     * @return array
     */
    public function transform(RepositoryImages $model)
    {
                        $iaasRepositoryId = \NextDeveloper\IAAS\Database\Models\Repositories::where('id', $model->iaas_repository_id)->first();
                    $iamAccountId = \NextDeveloper\IAM\Database\Models\Accounts::where('id', $model->iam_account_id)->first();
                    $iamUserId = \NextDeveloper\IAM\Database\Models\Users::where('id', $model->iam_user_id)->first();
        
        return $this->buildPayload(
            [
            'id'  =>  $model->uuid,
            'name'  =>  $model->name,
            'description'  =>  $model->description,
            'path'  =>  $model->path,
            'filename'  =>  $model->filename,
            'default_username'  =>  $model->default_username,
            'default_password'  =>  $model->default_password,
            'is_active'  =>  $model->is_active,
            'is_iso'  =>  $model->is_iso,
            'is_virtual_machine_image'  =>  $model->is_virtual_machine_image,
            'is_docker_image'  =>  $model->is_docker_image,
            'os'  =>  $model->os,
            'distro'  =>  $model->distro,
            'version'  =>  $model->version,
            'release_version'  =>  $model->release_version,
            'is_latest'  =>  $model->is_latest,
            'extra'  =>  $model->extra,
            'cpu_type'  =>  $model->cpu_type,
            'supported_virtualizations'  =>  $model->supported_virtualizations,
            'iaas_repository_id'  =>  $iaasRepositoryId ? $iaasRepositoryId->uuid : null,
            'hash'  =>  $model->hash,
            'iam_account_id'  =>  $iamAccountId ? $iamAccountId->uuid : null,
            'iam_user_id'  =>  $iamUserId ? $iamUserId->uuid : null,
            'created_at'  =>  $model->created_at,
            'updated_at'  =>  $model->updated_at,
            'deleted_at'  =>  $model->deleted_at,
            ]
        );
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE


}
