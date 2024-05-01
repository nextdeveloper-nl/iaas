<?php

namespace NextDeveloper\IAAS\Services;

use Exceptions\DuplicateObject;
use Illuminate\Support\Str;
use NextDeveloper\IAAS\Database\Models\Datacenters;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractDatacentersService;

/**
 * This class is responsible from managing the data for Datacenters
 *
 * Class DatacentersService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class DatacentersService extends AbstractDatacentersService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    public static function create($data)
    {
        $datacenter = Datacenters::withoutGlobalScopes()
            ->where('name', $data['name'])
            ->first();

        if($datacenter) {
            throw new DuplicateObject('Datacenter already exists. Please provide a different name.');
        }

        if(!array_key_exists('slug', $data)) {
            $data['slug'] = Str::slug($data['name']);
        }

        if($data['slug'] == '')
        {
            $data['slug'] = Str::slug($data['name']);
        }

        return parent::create($data);
    }
}
