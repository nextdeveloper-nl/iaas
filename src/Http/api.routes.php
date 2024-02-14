<?php

Route::prefix('iaas')->group(
    function () {
        Route::prefix('compute-members')->group(
            function () {
                Route::get('/', 'ComputeMembers\ComputeMembersController@index');

                Route::get('{iaas_compute_members}/tags ', 'ComputeMembers\ComputeMembersController@tags');
                Route::post('{iaas_compute_members}/tags ', 'ComputeMembers\ComputeMembersController@saveTags');
                Route::get('{iaas_compute_members}/addresses ', 'ComputeMembers\ComputeMembersController@addresses');
                Route::post('{iaas_compute_members}/addresses ', 'ComputeMembers\ComputeMembersController@saveAddresses');

                Route::get('/{iaas_compute_members}/{subObjects}', 'ComputeMembers\ComputeMembersController@relatedObjects');
                Route::get('/{iaas_compute_members}', 'ComputeMembers\ComputeMembersController@show');

                Route::post('/', 'ComputeMembers\ComputeMembersController@store');
                Route::patch('/{iaas_compute_members}', 'ComputeMembers\ComputeMembersController@update');
                Route::delete('/{iaas_compute_members}', 'ComputeMembers\ComputeMembersController@destroy');
            }
        );

        Route::prefix('compute-pools')->group(
            function () {
                Route::get('/', 'ComputePools\ComputePoolsController@index');

                Route::get('{iaas_compute_pools}/tags ', 'ComputePools\ComputePoolsController@tags');
                Route::post('{iaas_compute_pools}/tags ', 'ComputePools\ComputePoolsController@saveTags');
                Route::get('{iaas_compute_pools}/addresses ', 'ComputePools\ComputePoolsController@addresses');
                Route::post('{iaas_compute_pools}/addresses ', 'ComputePools\ComputePoolsController@saveAddresses');

                Route::get('/{iaas_compute_pools}/{subObjects}', 'ComputePools\ComputePoolsController@relatedObjects');
                Route::get('/{iaas_compute_pools}', 'ComputePools\ComputePoolsController@show');

                Route::post('/', 'ComputePools\ComputePoolsController@store');
                Route::patch('/{iaas_compute_pools}', 'ComputePools\ComputePoolsController@update');
                Route::delete('/{iaas_compute_pools}', 'ComputePools\ComputePoolsController@destroy');
            }
        );

        Route::prefix('cloud-nodes')->group(
            function () {
                Route::get('/', 'CloudNodes\CloudNodesController@index');

                Route::get('{iaas_cloud_nodes}/tags ', 'CloudNodes\CloudNodesController@tags');
                Route::post('{iaas_cloud_nodes}/tags ', 'CloudNodes\CloudNodesController@saveTags');
                Route::get('{iaas_cloud_nodes}/addresses ', 'CloudNodes\CloudNodesController@addresses');
                Route::post('{iaas_cloud_nodes}/addresses ', 'CloudNodes\CloudNodesController@saveAddresses');

                Route::get('/{iaas_cloud_nodes}/{subObjects}', 'CloudNodes\CloudNodesController@relatedObjects');
                Route::get('/{iaas_cloud_nodes}', 'CloudNodes\CloudNodesController@show');

                Route::post('/', 'CloudNodes\CloudNodesController@store');
                Route::patch('/{iaas_cloud_nodes}', 'CloudNodes\CloudNodesController@update');
                Route::delete('/{iaas_cloud_nodes}', 'CloudNodes\CloudNodesController@destroy');
            }
        );

        Route::prefix('network-pools')->group(
            function () {
                Route::get('/', 'NetworkPools\NetworkPoolsController@index');

                Route::get('{iaas_network_pools}/tags ', 'NetworkPools\NetworkPoolsController@tags');
                Route::post('{iaas_network_pools}/tags ', 'NetworkPools\NetworkPoolsController@saveTags');
                Route::get('{iaas_network_pools}/addresses ', 'NetworkPools\NetworkPoolsController@addresses');
                Route::post('{iaas_network_pools}/addresses ', 'NetworkPools\NetworkPoolsController@saveAddresses');

                Route::get('/{iaas_network_pools}/{subObjects}', 'NetworkPools\NetworkPoolsController@relatedObjects');
                Route::get('/{iaas_network_pools}', 'NetworkPools\NetworkPoolsController@show');

                Route::post('/', 'NetworkPools\NetworkPoolsController@store');
                Route::patch('/{iaas_network_pools}', 'NetworkPools\NetworkPoolsController@update');
                Route::delete('/{iaas_network_pools}', 'NetworkPools\NetworkPoolsController@destroy');
            }
        );

        Route::prefix('storage-members')->group(
            function () {
                Route::get('/', 'StorageMembers\StorageMembersController@index');

                Route::get('{iaas_storage_members}/tags ', 'StorageMembers\StorageMembersController@tags');
                Route::post('{iaas_storage_members}/tags ', 'StorageMembers\StorageMembersController@saveTags');
                Route::get('{iaas_storage_members}/addresses ', 'StorageMembers\StorageMembersController@addresses');
                Route::post('{iaas_storage_members}/addresses ', 'StorageMembers\StorageMembersController@saveAddresses');

                Route::get('/{iaas_storage_members}/{subObjects}', 'StorageMembers\StorageMembersController@relatedObjects');
                Route::get('/{iaas_storage_members}', 'StorageMembers\StorageMembersController@show');

                Route::post('/', 'StorageMembers\StorageMembersController@store');
                Route::patch('/{iaas_storage_members}', 'StorageMembers\StorageMembersController@update');
                Route::delete('/{iaas_storage_members}', 'StorageMembers\StorageMembersController@destroy');
            }
        );

        Route::prefix('storage-pools')->group(
            function () {
                Route::get('/', 'StoragePools\StoragePoolsController@index');

                Route::get('{iaas_storage_pools}/tags ', 'StoragePools\StoragePoolsController@tags');
                Route::post('{iaas_storage_pools}/tags ', 'StoragePools\StoragePoolsController@saveTags');
                Route::get('{iaas_storage_pools}/addresses ', 'StoragePools\StoragePoolsController@addresses');
                Route::post('{iaas_storage_pools}/addresses ', 'StoragePools\StoragePoolsController@saveAddresses');

                Route::get('/{iaas_storage_pools}/{subObjects}', 'StoragePools\StoragePoolsController@relatedObjects');
                Route::get('/{iaas_storage_pools}', 'StoragePools\StoragePoolsController@show');

                Route::post('/', 'StoragePools\StoragePoolsController@store');
                Route::patch('/{iaas_storage_pools}', 'StoragePools\StoragePoolsController@update');
                Route::delete('/{iaas_storage_pools}', 'StoragePools\StoragePoolsController@destroy');
            }
        );

        Route::prefix('virtual-machines')->group(
            function () {
                Route::get('/', 'VirtualMachines\VirtualMachinesController@index');

                Route::get('{iaas_virtual_machines}/tags ', 'VirtualMachines\VirtualMachinesController@tags');
                Route::post('{iaas_virtual_machines}/tags ', 'VirtualMachines\VirtualMachinesController@saveTags');
                Route::get('{iaas_virtual_machines}/addresses ', 'VirtualMachines\VirtualMachinesController@addresses');
                Route::post('{iaas_virtual_machines}/addresses ', 'VirtualMachines\VirtualMachinesController@saveAddresses');

                Route::get('/{iaas_virtual_machines}/{subObjects}', 'VirtualMachines\VirtualMachinesController@relatedObjects');
                Route::get('/{iaas_virtual_machines}', 'VirtualMachines\VirtualMachinesController@show');

                Route::post('/', 'VirtualMachines\VirtualMachinesController@store');
                Route::patch('/{iaas_virtual_machines}', 'VirtualMachines\VirtualMachinesController@update');
                Route::delete('/{iaas_virtual_machines}', 'VirtualMachines\VirtualMachinesController@destroy');
            }
        );

        Route::prefix('storage-volumes')->group(
            function () {
                Route::get('/', 'StorageVolumes\StorageVolumesController@index');

                Route::get('{iaas_storage_volumes}/tags ', 'StorageVolumes\StorageVolumesController@tags');
                Route::post('{iaas_storage_volumes}/tags ', 'StorageVolumes\StorageVolumesController@saveTags');
                Route::get('{iaas_storage_volumes}/addresses ', 'StorageVolumes\StorageVolumesController@addresses');
                Route::post('{iaas_storage_volumes}/addresses ', 'StorageVolumes\StorageVolumesController@saveAddresses');

                Route::get('/{iaas_storage_volumes}/{subObjects}', 'StorageVolumes\StorageVolumesController@relatedObjects');
                Route::get('/{iaas_storage_volumes}', 'StorageVolumes\StorageVolumesController@show');

                Route::post('/', 'StorageVolumes\StorageVolumesController@store');
                Route::patch('/{iaas_storage_volumes}', 'StorageVolumes\StorageVolumesController@update');
                Route::delete('/{iaas_storage_volumes}', 'StorageVolumes\StorageVolumesController@destroy');
            }
        );

        Route::prefix('datacenters')->group(
            function () {
                Route::get('/', 'Datacenters\DatacentersController@index');

                Route::get('{iaas_datacenters}/tags ', 'Datacenters\DatacentersController@tags');
                Route::post('{iaas_datacenters}/tags ', 'Datacenters\DatacentersController@saveTags');
                Route::get('{iaas_datacenters}/addresses ', 'Datacenters\DatacentersController@addresses');
                Route::post('{iaas_datacenters}/addresses ', 'Datacenters\DatacentersController@saveAddresses');

                Route::get('/{iaas_datacenters}/{subObjects}', 'Datacenters\DatacentersController@relatedObjects');
                Route::get('/{iaas_datacenters}', 'Datacenters\DatacentersController@show');

                Route::post('/', 'Datacenters\DatacentersController@store');
                Route::patch('/{iaas_datacenters}', 'Datacenters\DatacentersController@update');
                Route::delete('/{iaas_datacenters}', 'Datacenters\DatacentersController@destroy');
            }
        );

        // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE


















































































    }
);

















