<?php

Route::prefix('iaas')->group(
    function () {
        Route::prefix('cloud-nodes')->group(
            function () {
                Route::get('/', 'CloudNodes\CloudNodesController@index');
                Route::get('/{iaas_cloud_nodes}', 'CloudNodes\CloudNodesController@show');
                Route::post('/', 'CloudNodes\CloudNodesController@store');
                Route::patch('/{iaas_cloud_nodes}', 'CloudNodes\CloudNodesController@update');
                Route::delete('/{iaas_cloud_nodes}', 'CloudNodes\CloudNodesController@destroy');
            }
        );

        Route::prefix('compute-members')->group(
            function () {
                Route::get('/', 'ComputeMembers\ComputeMembersController@index');
                Route::get('/{iaas_compute_members}', 'ComputeMembers\ComputeMembersController@show');
                Route::post('/', 'ComputeMembers\ComputeMembersController@store');
                Route::patch('/{iaas_compute_members}', 'ComputeMembers\ComputeMembersController@update');
                Route::delete('/{iaas_compute_members}', 'ComputeMembers\ComputeMembersController@destroy');
            }
        );

        Route::prefix('compute-pools')->group(
            function () {
                Route::get('/', 'ComputePools\ComputePoolsController@index');
                Route::get('/{iaas_compute_pools}', 'ComputePools\ComputePoolsController@show');
                Route::post('/', 'ComputePools\ComputePoolsController@store');
                Route::patch('/{iaas_compute_pools}', 'ComputePools\ComputePoolsController@update');
                Route::delete('/{iaas_compute_pools}', 'ComputePools\ComputePoolsController@destroy');
            }
        );

        Route::prefix('datacenters')->group(
            function () {
                Route::get('/', 'Datacenters\DatacentersController@index');
                Route::get('/{iaas_datacenters}', 'Datacenters\DatacentersController@show');
                Route::post('/', 'Datacenters\DatacentersController@store');
                Route::patch('/{iaas_datacenters}', 'Datacenters\DatacentersController@update');
                Route::delete('/{iaas_datacenters}', 'Datacenters\DatacentersController@destroy');
            }
        );

        Route::prefix('network-pools')->group(
            function () {
                Route::get('/', 'NetworkPools\NetworkPoolsController@index');
                Route::get('/{iaas_network_pools}', 'NetworkPools\NetworkPoolsController@show');
                Route::post('/', 'NetworkPools\NetworkPoolsController@store');
                Route::patch('/{iaas_network_pools}', 'NetworkPools\NetworkPoolsController@update');
                Route::delete('/{iaas_network_pools}', 'NetworkPools\NetworkPoolsController@destroy');
            }
        );

        Route::prefix('storage-pools')->group(
            function () {
                Route::get('/', 'StoragePools\StoragePoolsController@index');
                Route::get('/{iaas_storage_pools}', 'StoragePools\StoragePoolsController@show');
                Route::post('/', 'StoragePools\StoragePoolsController@store');
                Route::patch('/{iaas_storage_pools}', 'StoragePools\StoragePoolsController@update');
                Route::delete('/{iaas_storage_pools}', 'StoragePools\StoragePoolsController@destroy');
            }
        );

        Route::prefix('storage-volumes')->group(
            function () {
                Route::get('/', 'StorageVolumes\StorageVolumesController@index');
                Route::get('/{iaas_storage_volumes}', 'StorageVolumes\StorageVolumesController@show');
                Route::post('/', 'StorageVolumes\StorageVolumesController@store');
                Route::patch('/{iaas_storage_volumes}', 'StorageVolumes\StorageVolumesController@update');
                Route::delete('/{iaas_storage_volumes}', 'StorageVolumes\StorageVolumesController@destroy');
            }
        );

        Route::prefix('virtual-machines')->group(
            function () {
                Route::get('/', 'VirtualMachines\VirtualMachinesController@index');
                Route::get('/{iaas_virtual_machines}', 'VirtualMachines\VirtualMachinesController@show');
                Route::post('/', 'VirtualMachines\VirtualMachinesController@store');
                Route::patch('/{iaas_virtual_machines}', 'VirtualMachines\VirtualMachinesController@update');
                Route::delete('/{iaas_virtual_machines}', 'VirtualMachines\VirtualMachinesController@destroy');
            }
        );

        // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n
    }
);



