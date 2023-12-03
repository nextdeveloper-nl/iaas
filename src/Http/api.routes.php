<?php

Route::prefix('iaas')->group(
    function () {
        Route::prefix('cloud-nodes')->group(
            function () {
                Route::get('/', 'CloudNodes\CloudNodesController@index');
                Route::get('/{iaas_cloud_nodes}', 'CloudNodes\CloudNodesController@show');
                Route::get('/{iaas_cloud_nodes}/{subObjects}', 'CloudNodes\CloudNodesController@subObjects');
                Route::post('/', 'CloudNodes\CloudNodesController@store');
                Route::patch('/{iaas_cloud_nodes}', 'CloudNodes\CloudNodesController@update');
                Route::delete('/{iaas_cloud_nodes}', 'CloudNodes\CloudNodesController@destroy');
            }
        );

        Route::prefix('compute-members')->group(
            function () {
                Route::get('/', 'ComputeMembers\ComputeMembersController@index');
                Route::get('/{iaas_compute_members}', 'ComputeMembers\ComputeMembersController@show');
                Route::get('/{iaas_compute_members}/{subObjects}', 'ComputeMembers\ComputeMembersController@subObjects');
                Route::post('/', 'ComputeMembers\ComputeMembersController@store');
                Route::patch('/{iaas_compute_members}', 'ComputeMembers\ComputeMembersController@update');
                Route::delete('/{iaas_compute_members}', 'ComputeMembers\ComputeMembersController@destroy');
            }
        );

        Route::prefix('compute-pools')->group(
            function () {
                Route::get('/', 'ComputePools\ComputePoolsController@index');
                Route::get('/{iaas_compute_pools}', 'ComputePools\ComputePoolsController@show');
                Route::get('/{iaas_compute_pools}/{subObjects}', 'ComputePools\ComputePoolsController@subObjects');
                Route::post('/', 'ComputePools\ComputePoolsController@store');
                Route::patch('/{iaas_compute_pools}', 'ComputePools\ComputePoolsController@update');
                Route::delete('/{iaas_compute_pools}', 'ComputePools\ComputePoolsController@destroy');
            }
        );

        Route::prefix('datacenters')->group(
            function () {
                Route::get('/', 'Datacenters\DatacentersController@index');
                Route::get('/{iaas_datacenters}', 'Datacenters\DatacentersController@show');
                Route::get('/{iaas_datacenters}/{subObjects}', 'Datacenters\DatacentersController@subObjects');
                Route::post('/', 'Datacenters\DatacentersController@store');
                Route::patch('/{iaas_datacenters}', 'Datacenters\DatacentersController@update');
                Route::delete('/{iaas_datacenters}', 'Datacenters\DatacentersController@destroy');
            }
        );

        Route::prefix('network-pools')->group(
            function () {
                Route::get('/', 'NetworkPools\NetworkPoolsController@index');
                Route::get('/{iaas_network_pools}', 'NetworkPools\NetworkPoolsController@show');
                Route::get('/{iaas_network_pools}/{subObjects}', 'NetworkPools\NetworkPoolsController@subObjects');
                Route::post('/', 'NetworkPools\NetworkPoolsController@store');
                Route::patch('/{iaas_network_pools}', 'NetworkPools\NetworkPoolsController@update');
                Route::delete('/{iaas_network_pools}', 'NetworkPools\NetworkPoolsController@destroy');
            }
        );

        Route::prefix('storage-pools')->group(
            function () {
                Route::get('/', 'StoragePools\StoragePoolsController@index');
                Route::get('/{iaas_storage_pools}', 'StoragePools\StoragePoolsController@show');
                Route::get('/{iaas_storage_pools}/{subObjects}', 'StoragePools\StoragePoolsController@subObjects');
                Route::post('/', 'StoragePools\StoragePoolsController@store');
                Route::patch('/{iaas_storage_pools}', 'StoragePools\StoragePoolsController@update');
                Route::delete('/{iaas_storage_pools}', 'StoragePools\StoragePoolsController@destroy');
            }
        );

        Route::prefix('storage-volumes')->group(
            function () {
                Route::get('/', 'StorageVolumes\StorageVolumesController@index');
                Route::get('/{iaas_storage_volumes}', 'StorageVolumes\StorageVolumesController@show');
                Route::get('/{iaas_storage_volumes}/{subObjects}', 'StorageVolumes\StorageVolumesController@subObjects');
                Route::post('/', 'StorageVolumes\StorageVolumesController@store');
                Route::patch('/{iaas_storage_volumes}', 'StorageVolumes\StorageVolumesController@update');
                Route::delete('/{iaas_storage_volumes}', 'StorageVolumes\StorageVolumesController@destroy');
            }
        );

        Route::prefix('virtual-machines')->group(
            function () {
                Route::get('/', 'VirtualMachines\VirtualMachinesController@index');
                Route::get('/{iaas_virtual_machines}', 'VirtualMachines\VirtualMachinesController@show');
                Route::get('/{iaas_virtual_machines}/{subObjects}', 'VirtualMachines\VirtualMachinesController@subObjects');
                Route::post('/', 'VirtualMachines\VirtualMachinesController@store');
                Route::patch('/{iaas_virtual_machines}', 'VirtualMachines\VirtualMachinesController@update');
                Route::delete('/{iaas_virtual_machines}', 'VirtualMachines\VirtualMachinesController@destroy');
            }
        );

        // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
    }
);






