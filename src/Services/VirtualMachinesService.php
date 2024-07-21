<?php

namespace NextDeveloper\IAAS\Services;

use NextDeveloper\IAAS\Database\Models\CloudNodes;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractVirtualMachinesService;

/**
 * This class is responsible from managing the data for VirtualMachines
 *
 * Class VirtualMachinesService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class VirtualMachinesService extends AbstractVirtualMachinesService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    public static function create(array $data)
    {
        // Modifying the data before creating the record
        $data['cpu']    =   $data['ram'] / 2;

        if($data['ram'] > 16) {
            $data['cpu']    =   8;
        }

        //  Multiplying ram by 1024 to convert it to MB
        if($data['ram'] < 1024)
            $data['ram']    =   $data['ram'] * 1024;

        //  Finging and attaching cloud node id
        if(array_key_exists('iaas_compute_pool_id', $data)) {
            $computePool = ComputePools::where('uuid', $data['iaas_compute_pool_id'])->first();
            $cloudNode = CloudNodes::where('id', $computePool->iaas_cloud_node_id)->first();

            $data['iaas_cloud_node_id'] = $cloudNode->id;
        }

        return parent::create($data);
    }
}
