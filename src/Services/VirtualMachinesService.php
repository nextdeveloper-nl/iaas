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
        if($data['ram'] > 2)
            $data['cpu']    =   $data['ram'] / 2;
        else
            $data['cpu']    =   2;

        if($data['ram'] > 32) {
            $data['cpu']    =   16;
        }

        //  So with this setup, we set our maximum available ram to 2048 GB
        if($data['ram'] < 2048)
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
