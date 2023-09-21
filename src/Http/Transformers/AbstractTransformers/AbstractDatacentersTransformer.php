<?php

namespace NextDeveloper\IAAS\Http\Transformers\AbstractTransformers;

use NextDeveloper\IAAS\Database\Models\Datacenters;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;

/**
 * Class DatacentersTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractDatacentersTransformer extends AbstractTransformer
{

    /**
     * @param Datacenters $model
     *
     * @return array
     */
    public function transform(Datacenters $model)
    {
                        $iamAccountId = \NextDeveloper\IAM\Database\Models\Accounts::where('id', $model->iam_account_id)->first();
                    $commonCountryId = \NextDeveloper\Commons\Database\Models\Countries::where('id', $model->common_country_id)->first();
            
        return $this->buildPayload(
            [
            'id'  =>  $model->uuid,
            'name'  =>  $model->name,
            'slug'  =>  $model->slug,
            'is_public'  =>  $model->is_public,
            'is_active'  =>  $model->is_active,
            'maintenance_mode'  =>  $model->maintenance_mode,
            'geo_latitude'  =>  $model->geo_latitude,
            'geo_longitude'  =>  $model->geo_longitude,
            'tier_level'  =>  $model->tier_level,
            'total_capacity'  =>  $model->total_capacity,
            'guaranteed_uptime'  =>  $model->guaranteed_uptime,
            'is_carrier_neutral'  =>  $model->is_carrier_neutral,
            'power_source'  =>  $model->power_source,
            'ups'  =>  $model->ups,
            'cooling'  =>  $model->cooling,
            'city'  =>  $model->city,
            'iam_account_id'  =>  $iamAccountId ? $iamAccountId->uuid : null,
            'common_country_id'  =>  $commonCountryId ? $commonCountryId->uuid : null,
            'created_at'  =>  $model->created_at,
            'updated_at'  =>  $model->updated_at,
            'deleted_at'  =>  $model->deleted_at,
            ]
        );
    }
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n

















}
