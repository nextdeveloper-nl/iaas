<?php

Route::prefix('iaas')->group(
    function () {
        Route::prefix('network-pool-stats')->group(
            function () {
                Route::get('/', 'NetworkPoolStats\NetworkPoolStatsController@index');
                Route::get('/actions', 'NetworkPoolStats\NetworkPoolStatsController@getActions');

                Route::get('{iaas_network_pool_stats}/tags ', 'NetworkPoolStats\NetworkPoolStatsController@tags');
                Route::post('{iaas_network_pool_stats}/tags ', 'NetworkPoolStats\NetworkPoolStatsController@saveTags');
                Route::get('{iaas_network_pool_stats}/addresses ', 'NetworkPoolStats\NetworkPoolStatsController@addresses');
                Route::post('{iaas_network_pool_stats}/addresses ', 'NetworkPoolStats\NetworkPoolStatsController@saveAddresses');

                Route::get('/{iaas_network_pool_stats}/{subObjects}', 'NetworkPoolStats\NetworkPoolStatsController@relatedObjects');
                Route::get('/{iaas_network_pool_stats}', 'NetworkPoolStats\NetworkPoolStatsController@show');

                Route::post('/', 'NetworkPoolStats\NetworkPoolStatsController@store');
                Route::post('/{iaas_network_pool_stats}/do/{action}', 'NetworkPoolStats\NetworkPoolStatsController@doAction');

                Route::patch('/{iaas_network_pool_stats}', 'NetworkPoolStats\NetworkPoolStatsController@update');
                Route::delete('/{iaas_network_pool_stats}', 'NetworkPoolStats\NetworkPoolStatsController@destroy');
            }
        );

        Route::prefix('network-members-interfaces')->group(
            function () {
                Route::get('/', 'NetworkMembersInterfaces\NetworkMembersInterfacesController@index');
                Route::get('/actions', 'NetworkMembersInterfaces\NetworkMembersInterfacesController@getActions');

                Route::get('{iaas_network_members_interfaces}/tags ', 'NetworkMembersInterfaces\NetworkMembersInterfacesController@tags');
                Route::post('{iaas_network_members_interfaces}/tags ', 'NetworkMembersInterfaces\NetworkMembersInterfacesController@saveTags');
                Route::get('{iaas_network_members_interfaces}/addresses ', 'NetworkMembersInterfaces\NetworkMembersInterfacesController@addresses');
                Route::post('{iaas_network_members_interfaces}/addresses ', 'NetworkMembersInterfaces\NetworkMembersInterfacesController@saveAddresses');

                Route::get('/{iaas_network_members_interfaces}/{subObjects}', 'NetworkMembersInterfaces\NetworkMembersInterfacesController@relatedObjects');
                Route::get('/{iaas_network_members_interfaces}', 'NetworkMembersInterfaces\NetworkMembersInterfacesController@show');

                Route::post('/', 'NetworkMembersInterfaces\NetworkMembersInterfacesController@store');
                Route::post('/{iaas_network_members_interfaces}/do/{action}', 'NetworkMembersInterfaces\NetworkMembersInterfacesController@doAction');

                Route::patch('/{iaas_network_members_interfaces}', 'NetworkMembersInterfaces\NetworkMembersInterfacesController@update');
                Route::delete('/{iaas_network_members_interfaces}', 'NetworkMembersInterfaces\NetworkMembersInterfacesController@destroy');
            }
        );

        Route::prefix('virtual-disk-images')->group(
            function () {
                Route::get('/', 'VirtualDiskImages\VirtualDiskImagesController@index');
                Route::get('/actions', 'VirtualDiskImages\VirtualDiskImagesController@getActions');

                Route::get('{iaas_virtual_disk_images}/tags ', 'VirtualDiskImages\VirtualDiskImagesController@tags');
                Route::post('{iaas_virtual_disk_images}/tags ', 'VirtualDiskImages\VirtualDiskImagesController@saveTags');
                Route::get('{iaas_virtual_disk_images}/addresses ', 'VirtualDiskImages\VirtualDiskImagesController@addresses');
                Route::post('{iaas_virtual_disk_images}/addresses ', 'VirtualDiskImages\VirtualDiskImagesController@saveAddresses');

                Route::get('/{iaas_virtual_disk_images}/{subObjects}', 'VirtualDiskImages\VirtualDiskImagesController@relatedObjects');
                Route::get('/{iaas_virtual_disk_images}', 'VirtualDiskImages\VirtualDiskImagesController@show');

                Route::post('/', 'VirtualDiskImages\VirtualDiskImagesController@store');
                Route::post('/{iaas_virtual_disk_images}/do/{action}', 'VirtualDiskImages\VirtualDiskImagesController@doAction');

                Route::patch('/{iaas_virtual_disk_images}', 'VirtualDiskImages\VirtualDiskImagesController@update');
                Route::delete('/{iaas_virtual_disk_images}', 'VirtualDiskImages\VirtualDiskImagesController@destroy');
            }
        );

        Route::prefix('account-stats')->group(
            function () {
                Route::get('/', 'AccountStats\AccountStatsController@index');
                Route::get('/actions', 'AccountStats\AccountStatsController@getActions');

                Route::get('{iaas_account_stats}/tags ', 'AccountStats\AccountStatsController@tags');
                Route::post('{iaas_account_stats}/tags ', 'AccountStats\AccountStatsController@saveTags');
                Route::get('{iaas_account_stats}/addresses ', 'AccountStats\AccountStatsController@addresses');
                Route::post('{iaas_account_stats}/addresses ', 'AccountStats\AccountStatsController@saveAddresses');

                Route::get('/{iaas_account_stats}/{subObjects}', 'AccountStats\AccountStatsController@relatedObjects');
                Route::get('/{iaas_account_stats}', 'AccountStats\AccountStatsController@show');

                Route::post('/', 'AccountStats\AccountStatsController@store');
                Route::post('/{iaas_account_stats}/do/{action}', 'AccountStats\AccountStatsController@doAction');

                Route::patch('/{iaas_account_stats}', 'AccountStats\AccountStatsController@update');
                Route::delete('/{iaas_account_stats}', 'AccountStats\AccountStatsController@destroy');
            }
        );

        Route::prefix('networks')->group(
            function () {
                Route::get('/', 'Networks\NetworksController@index');
                Route::get('/actions', 'Networks\NetworksController@getActions');

                Route::get('{iaas_networks}/tags ', 'Networks\NetworksController@tags');
                Route::post('{iaas_networks}/tags ', 'Networks\NetworksController@saveTags');
                Route::get('{iaas_networks}/addresses ', 'Networks\NetworksController@addresses');
                Route::post('{iaas_networks}/addresses ', 'Networks\NetworksController@saveAddresses');

                Route::get('/{iaas_networks}/{subObjects}', 'Networks\NetworksController@relatedObjects');
                Route::get('/{iaas_networks}', 'Networks\NetworksController@show');

                Route::post('/', 'Networks\NetworksController@store');
                Route::post('/{iaas_networks}/do/{action}', 'Networks\NetworksController@doAction');

                Route::patch('/{iaas_networks}', 'Networks\NetworksController@update');
                Route::delete('/{iaas_networks}', 'Networks\NetworksController@destroy');
            }
        );

        Route::prefix('virtual-network-cards')->group(
            function () {
                Route::get('/', 'VirtualNetworkCards\VirtualNetworkCardsController@index');
                Route::get('/actions', 'VirtualNetworkCards\VirtualNetworkCardsController@getActions');

                Route::get('{iaas_virtual_network_cards}/tags ', 'VirtualNetworkCards\VirtualNetworkCardsController@tags');
                Route::post('{iaas_virtual_network_cards}/tags ', 'VirtualNetworkCards\VirtualNetworkCardsController@saveTags');
                Route::get('{iaas_virtual_network_cards}/addresses ', 'VirtualNetworkCards\VirtualNetworkCardsController@addresses');
                Route::post('{iaas_virtual_network_cards}/addresses ', 'VirtualNetworkCards\VirtualNetworkCardsController@saveAddresses');

                Route::get('/{iaas_virtual_network_cards}/{subObjects}', 'VirtualNetworkCards\VirtualNetworkCardsController@relatedObjects');
                Route::get('/{iaas_virtual_network_cards}', 'VirtualNetworkCards\VirtualNetworkCardsController@show');

                Route::post('/', 'VirtualNetworkCards\VirtualNetworkCardsController@store');
                Route::post('/{iaas_virtual_network_cards}/do/{action}', 'VirtualNetworkCards\VirtualNetworkCardsController@doAction');

                Route::patch('/{iaas_virtual_network_cards}', 'VirtualNetworkCards\VirtualNetworkCardsController@update');
                Route::delete('/{iaas_virtual_network_cards}', 'VirtualNetworkCards\VirtualNetworkCardsController@destroy');
            }
        );

        Route::prefix('ip-addresses')->group(
            function () {
                Route::get('/', 'IpAddresses\IpAddressesController@index');
                Route::get('/actions', 'IpAddresses\IpAddressesController@getActions');

                Route::get('{iaas_ip_addresses}/tags ', 'IpAddresses\IpAddressesController@tags');
                Route::post('{iaas_ip_addresses}/tags ', 'IpAddresses\IpAddressesController@saveTags');
                Route::get('{iaas_ip_addresses}/addresses ', 'IpAddresses\IpAddressesController@addresses');
                Route::post('{iaas_ip_addresses}/addresses ', 'IpAddresses\IpAddressesController@saveAddresses');

                Route::get('/{iaas_ip_addresses}/{subObjects}', 'IpAddresses\IpAddressesController@relatedObjects');
                Route::get('/{iaas_ip_addresses}', 'IpAddresses\IpAddressesController@show');

                Route::post('/', 'IpAddresses\IpAddressesController@store');
                Route::post('/{iaas_ip_addresses}/do/{action}', 'IpAddresses\IpAddressesController@doAction');

                Route::patch('/{iaas_ip_addresses}', 'IpAddresses\IpAddressesController@update');
                Route::delete('/{iaas_ip_addresses}', 'IpAddresses\IpAddressesController@destroy');
            }
        );

        Route::prefix('virtual-machine-stats')->group(
            function () {
                Route::get('/', 'VirtualMachineStats\VirtualMachineStatsController@index');
                Route::get('/actions', 'VirtualMachineStats\VirtualMachineStatsController@getActions');

                Route::get('{iaas_virtual_machine_stats}/tags ', 'VirtualMachineStats\VirtualMachineStatsController@tags');
                Route::post('{iaas_virtual_machine_stats}/tags ', 'VirtualMachineStats\VirtualMachineStatsController@saveTags');
                Route::get('{iaas_virtual_machine_stats}/addresses ', 'VirtualMachineStats\VirtualMachineStatsController@addresses');
                Route::post('{iaas_virtual_machine_stats}/addresses ', 'VirtualMachineStats\VirtualMachineStatsController@saveAddresses');

                Route::get('/{iaas_virtual_machine_stats}/{subObjects}', 'VirtualMachineStats\VirtualMachineStatsController@relatedObjects');
                Route::get('/{iaas_virtual_machine_stats}', 'VirtualMachineStats\VirtualMachineStatsController@show');

                Route::post('/', 'VirtualMachineStats\VirtualMachineStatsController@store');
                Route::post('/{iaas_virtual_machine_stats}/do/{action}', 'VirtualMachineStats\VirtualMachineStatsController@doAction');

                Route::patch('/{iaas_virtual_machine_stats}', 'VirtualMachineStats\VirtualMachineStatsController@update');
                Route::delete('/{iaas_virtual_machine_stats}', 'VirtualMachineStats\VirtualMachineStatsController@destroy');
            }
        );

        Route::prefix('storage-member-stats')->group(
            function () {
                Route::get('/', 'StorageMemberStats\StorageMemberStatsController@index');
                Route::get('/actions', 'StorageMemberStats\StorageMemberStatsController@getActions');

                Route::get('{iaas_storage_member_stats}/tags ', 'StorageMemberStats\StorageMemberStatsController@tags');
                Route::post('{iaas_storage_member_stats}/tags ', 'StorageMemberStats\StorageMemberStatsController@saveTags');
                Route::get('{iaas_storage_member_stats}/addresses ', 'StorageMemberStats\StorageMemberStatsController@addresses');
                Route::post('{iaas_storage_member_stats}/addresses ', 'StorageMemberStats\StorageMemberStatsController@saveAddresses');

                Route::get('/{iaas_storage_member_stats}/{subObjects}', 'StorageMemberStats\StorageMemberStatsController@relatedObjects');
                Route::get('/{iaas_storage_member_stats}', 'StorageMemberStats\StorageMemberStatsController@show');

                Route::post('/', 'StorageMemberStats\StorageMemberStatsController@store');
                Route::post('/{iaas_storage_member_stats}/do/{action}', 'StorageMemberStats\StorageMemberStatsController@doAction');

                Route::patch('/{iaas_storage_member_stats}', 'StorageMemberStats\StorageMemberStatsController@update');
                Route::delete('/{iaas_storage_member_stats}', 'StorageMemberStats\StorageMemberStatsController@destroy');
            }
        );

        Route::prefix('compute-members')->group(
            function () {
                Route::get('/', 'ComputeMembers\ComputeMembersController@index');
                Route::get('/actions', 'ComputeMembers\ComputeMembersController@getActions');

                Route::get('{iaas_compute_members}/tags ', 'ComputeMembers\ComputeMembersController@tags');
                Route::post('{iaas_compute_members}/tags ', 'ComputeMembers\ComputeMembersController@saveTags');
                Route::get('{iaas_compute_members}/addresses ', 'ComputeMembers\ComputeMembersController@addresses');
                Route::post('{iaas_compute_members}/addresses ', 'ComputeMembers\ComputeMembersController@saveAddresses');

                Route::get('/{iaas_compute_members}/{subObjects}', 'ComputeMembers\ComputeMembersController@relatedObjects');
                Route::get('/{iaas_compute_members}', 'ComputeMembers\ComputeMembersController@show');

                Route::post('/', 'ComputeMembers\ComputeMembersController@store');
                Route::post('/{iaas_compute_members}/do/{action}', 'ComputeMembers\ComputeMembersController@doAction');

                Route::patch('/{iaas_compute_members}', 'ComputeMembers\ComputeMembersController@update');
                Route::delete('/{iaas_compute_members}', 'ComputeMembers\ComputeMembersController@destroy');
            }
        );

        Route::prefix('compute-member-stats')->group(
            function () {
                Route::get('/', 'ComputeMemberStats\ComputeMemberStatsController@index');
                Route::get('/actions', 'ComputeMemberStats\ComputeMemberStatsController@getActions');

                Route::get('{iaas_compute_member_stats}/tags ', 'ComputeMemberStats\ComputeMemberStatsController@tags');
                Route::post('{iaas_compute_member_stats}/tags ', 'ComputeMemberStats\ComputeMemberStatsController@saveTags');
                Route::get('{iaas_compute_member_stats}/addresses ', 'ComputeMemberStats\ComputeMemberStatsController@addresses');
                Route::post('{iaas_compute_member_stats}/addresses ', 'ComputeMemberStats\ComputeMemberStatsController@saveAddresses');

                Route::get('/{iaas_compute_member_stats}/{subObjects}', 'ComputeMemberStats\ComputeMemberStatsController@relatedObjects');
                Route::get('/{iaas_compute_member_stats}', 'ComputeMemberStats\ComputeMemberStatsController@show');

                Route::post('/', 'ComputeMemberStats\ComputeMemberStatsController@store');
                Route::post('/{iaas_compute_member_stats}/do/{action}', 'ComputeMemberStats\ComputeMemberStatsController@doAction');

                Route::patch('/{iaas_compute_member_stats}', 'ComputeMemberStats\ComputeMemberStatsController@update');
                Route::delete('/{iaas_compute_member_stats}', 'ComputeMemberStats\ComputeMemberStatsController@destroy');
            }
        );

        Route::prefix('network-stats')->group(
            function () {
                Route::get('/', 'NetworkStats\NetworkStatsController@index');
                Route::get('/actions', 'NetworkStats\NetworkStatsController@getActions');

                Route::get('{iaas_network_stats}/tags ', 'NetworkStats\NetworkStatsController@tags');
                Route::post('{iaas_network_stats}/tags ', 'NetworkStats\NetworkStatsController@saveTags');
                Route::get('{iaas_network_stats}/addresses ', 'NetworkStats\NetworkStatsController@addresses');
                Route::post('{iaas_network_stats}/addresses ', 'NetworkStats\NetworkStatsController@saveAddresses');

                Route::get('/{iaas_network_stats}/{subObjects}', 'NetworkStats\NetworkStatsController@relatedObjects');
                Route::get('/{iaas_network_stats}', 'NetworkStats\NetworkStatsController@show');

                Route::post('/', 'NetworkStats\NetworkStatsController@store');
                Route::post('/{iaas_network_stats}/do/{action}', 'NetworkStats\NetworkStatsController@doAction');

                Route::patch('/{iaas_network_stats}', 'NetworkStats\NetworkStatsController@update');
                Route::delete('/{iaas_network_stats}', 'NetworkStats\NetworkStatsController@destroy');
            }
        );

        Route::prefix('storage-volume-stats')->group(
            function () {
                Route::get('/', 'StorageVolumeStats\StorageVolumeStatsController@index');
                Route::get('/actions', 'StorageVolumeStats\StorageVolumeStatsController@getActions');

                Route::get('{iaas_storage_volume_stats}/tags ', 'StorageVolumeStats\StorageVolumeStatsController@tags');
                Route::post('{iaas_storage_volume_stats}/tags ', 'StorageVolumeStats\StorageVolumeStatsController@saveTags');
                Route::get('{iaas_storage_volume_stats}/addresses ', 'StorageVolumeStats\StorageVolumeStatsController@addresses');
                Route::post('{iaas_storage_volume_stats}/addresses ', 'StorageVolumeStats\StorageVolumeStatsController@saveAddresses');

                Route::get('/{iaas_storage_volume_stats}/{subObjects}', 'StorageVolumeStats\StorageVolumeStatsController@relatedObjects');
                Route::get('/{iaas_storage_volume_stats}', 'StorageVolumeStats\StorageVolumeStatsController@show');

                Route::post('/', 'StorageVolumeStats\StorageVolumeStatsController@store');
                Route::post('/{iaas_storage_volume_stats}/do/{action}', 'StorageVolumeStats\StorageVolumeStatsController@doAction');

                Route::patch('/{iaas_storage_volume_stats}', 'StorageVolumeStats\StorageVolumeStatsController@update');
                Route::delete('/{iaas_storage_volume_stats}', 'StorageVolumeStats\StorageVolumeStatsController@destroy');
            }
        );

        Route::prefix('virtual-network-card-stats')->group(
            function () {
                Route::get('/', 'VirtualNetworkCardStats\VirtualNetworkCardStatsController@index');
                Route::get('/actions', 'VirtualNetworkCardStats\VirtualNetworkCardStatsController@getActions');

                Route::get('{iaas_virtual_network_card_stats}/tags ', 'VirtualNetworkCardStats\VirtualNetworkCardStatsController@tags');
                Route::post('{iaas_virtual_network_card_stats}/tags ', 'VirtualNetworkCardStats\VirtualNetworkCardStatsController@saveTags');
                Route::get('{iaas_virtual_network_card_stats}/addresses ', 'VirtualNetworkCardStats\VirtualNetworkCardStatsController@addresses');
                Route::post('{iaas_virtual_network_card_stats}/addresses ', 'VirtualNetworkCardStats\VirtualNetworkCardStatsController@saveAddresses');

                Route::get('/{iaas_virtual_network_card_stats}/{subObjects}', 'VirtualNetworkCardStats\VirtualNetworkCardStatsController@relatedObjects');
                Route::get('/{iaas_virtual_network_card_stats}', 'VirtualNetworkCardStats\VirtualNetworkCardStatsController@show');

                Route::post('/', 'VirtualNetworkCardStats\VirtualNetworkCardStatsController@store');
                Route::post('/{iaas_virtual_network_card_stats}/do/{action}', 'VirtualNetworkCardStats\VirtualNetworkCardStatsController@doAction');

                Route::patch('/{iaas_virtual_network_card_stats}', 'VirtualNetworkCardStats\VirtualNetworkCardStatsController@update');
                Route::delete('/{iaas_virtual_network_card_stats}', 'VirtualNetworkCardStats\VirtualNetworkCardStatsController@destroy');
            }
        );

        Route::prefix('compute-member-storage-volumes')->group(
            function () {
                Route::get('/', 'ComputeMemberStorageVolumes\ComputeMemberStorageVolumesController@index');
                Route::get('/actions', 'ComputeMemberStorageVolumes\ComputeMemberStorageVolumesController@getActions');

                Route::get('{icmsv}/tags ', 'ComputeMemberStorageVolumes\ComputeMemberStorageVolumesController@tags');
                Route::post('{icmsv}/tags ', 'ComputeMemberStorageVolumes\ComputeMemberStorageVolumesController@saveTags');
                Route::get('{icmsv}/addresses ', 'ComputeMemberStorageVolumes\ComputeMemberStorageVolumesController@addresses');
                Route::post('{icmsv}/addresses ', 'ComputeMemberStorageVolumes\ComputeMemberStorageVolumesController@saveAddresses');

                Route::get('/{icmsv}/{subObjects}', 'ComputeMemberStorageVolumes\ComputeMemberStorageVolumesController@relatedObjects');
                Route::get('/{icmsv}', 'ComputeMemberStorageVolumes\ComputeMemberStorageVolumesController@show');

                Route::post('/', 'ComputeMemberStorageVolumes\ComputeMemberStorageVolumesController@store');
                Route::post('/{icmsv}/do/{action}', 'ComputeMemberStorageVolumes\ComputeMemberStorageVolumesController@doAction');

                Route::patch('/{icmsv}', 'ComputeMemberStorageVolumes\ComputeMemberStorageVolumesController@update');
                Route::delete('/{icmsv}', 'ComputeMemberStorageVolumes\ComputeMemberStorageVolumesController@destroy');
            }
        );

        Route::prefix('compute-member-devices')->group(
            function () {
                Route::get('/', 'ComputeMemberDevices\ComputeMemberDevicesController@index');
                Route::get('/actions', 'ComputeMemberDevices\ComputeMemberDevicesController@getActions');

                Route::get('{iaas_compute_member_devices}/tags ', 'ComputeMemberDevices\ComputeMemberDevicesController@tags');
                Route::post('{iaas_compute_member_devices}/tags ', 'ComputeMemberDevices\ComputeMemberDevicesController@saveTags');
                Route::get('{iaas_compute_member_devices}/addresses ', 'ComputeMemberDevices\ComputeMemberDevicesController@addresses');
                Route::post('{iaas_compute_member_devices}/addresses ', 'ComputeMemberDevices\ComputeMemberDevicesController@saveAddresses');

                Route::get('/{iaas_compute_member_devices}/{subObjects}', 'ComputeMemberDevices\ComputeMemberDevicesController@relatedObjects');
                Route::get('/{iaas_compute_member_devices}', 'ComputeMemberDevices\ComputeMemberDevicesController@show');

                Route::post('/', 'ComputeMemberDevices\ComputeMemberDevicesController@store');
                Route::post('/{iaas_compute_member_devices}/do/{action}', 'ComputeMemberDevices\ComputeMemberDevicesController@doAction');

                Route::patch('/{iaas_compute_member_devices}', 'ComputeMemberDevices\ComputeMemberDevicesController@update');
                Route::delete('/{iaas_compute_member_devices}', 'ComputeMemberDevices\ComputeMemberDevicesController@destroy');
            }
        );

        Route::prefix('compute-member-events')->group(
            function () {
                Route::get('/', 'ComputeMemberEvents\ComputeMemberEventsController@index');
                Route::get('/actions', 'ComputeMemberEvents\ComputeMemberEventsController@getActions');

                Route::get('{iaas_compute_member_events}/tags ', 'ComputeMemberEvents\ComputeMemberEventsController@tags');
                Route::post('{iaas_compute_member_events}/tags ', 'ComputeMemberEvents\ComputeMemberEventsController@saveTags');
                Route::get('{iaas_compute_member_events}/addresses ', 'ComputeMemberEvents\ComputeMemberEventsController@addresses');
                Route::post('{iaas_compute_member_events}/addresses ', 'ComputeMemberEvents\ComputeMemberEventsController@saveAddresses');

                Route::get('/{iaas_compute_member_events}/{subObjects}', 'ComputeMemberEvents\ComputeMemberEventsController@relatedObjects');
                Route::get('/{iaas_compute_member_events}', 'ComputeMemberEvents\ComputeMemberEventsController@show');

                Route::post('/', 'ComputeMemberEvents\ComputeMemberEventsController@store');
                Route::post('/{iaas_compute_member_events}/do/{action}', 'ComputeMemberEvents\ComputeMemberEventsController@doAction');

                Route::patch('/{iaas_compute_member_events}', 'ComputeMemberEvents\ComputeMemberEventsController@update');
                Route::delete('/{iaas_compute_member_events}', 'ComputeMemberEvents\ComputeMemberEventsController@destroy');
            }
        );

        Route::prefix('compute-member-network-interfaces')->group(
            function () {
                Route::get('/', 'ComputeMemberNetworkInterfaces\ComputeMemberNetworkInterfacesController@index');
                Route::get('/actions', 'ComputeMemberNetworkInterfaces\ComputeMemberNetworkInterfacesController@getActions');

                Route::get('{icmni}/tags ', 'ComputeMemberNetworkInterfaces\ComputeMemberNetworkInterfacesController@tags');
                Route::post('{icmni}/tags ', 'ComputeMemberNetworkInterfaces\ComputeMemberNetworkInterfacesController@saveTags');
                Route::get('{icmni}/addresses ', 'ComputeMemberNetworkInterfaces\ComputeMemberNetworkInterfacesController@addresses');
                Route::post('{icmni}/addresses ', 'ComputeMemberNetworkInterfaces\ComputeMemberNetworkInterfacesController@saveAddresses');

                Route::get('/{icmni}/{subObjects}', 'ComputeMemberNetworkInterfaces\ComputeMemberNetworkInterfacesController@relatedObjects');
                Route::get('/{icmni}', 'ComputeMemberNetworkInterfaces\ComputeMemberNetworkInterfacesController@show');

                Route::post('/', 'ComputeMemberNetworkInterfaces\ComputeMemberNetworkInterfacesController@store');
                Route::post('/{icmni}/do/{action}', 'ComputeMemberNetworkInterfaces\ComputeMemberNetworkInterfacesController@doAction');

                Route::patch('/{icmni}', 'ComputeMemberNetworkInterfaces\ComputeMemberNetworkInterfacesController@update');
                Route::delete('/{icmni}', 'ComputeMemberNetworkInterfaces\ComputeMemberNetworkInterfacesController@destroy');
            }
        );

        Route::prefix('network-pools')->group(
            function () {
                Route::get('/', 'NetworkPools\NetworkPoolsController@index');
                Route::get('/actions', 'NetworkPools\NetworkPoolsController@getActions');

                Route::get('{iaas_network_pools}/tags ', 'NetworkPools\NetworkPoolsController@tags');
                Route::post('{iaas_network_pools}/tags ', 'NetworkPools\NetworkPoolsController@saveTags');
                Route::get('{iaas_network_pools}/addresses ', 'NetworkPools\NetworkPoolsController@addresses');
                Route::post('{iaas_network_pools}/addresses ', 'NetworkPools\NetworkPoolsController@saveAddresses');

                Route::get('/{iaas_network_pools}/{subObjects}', 'NetworkPools\NetworkPoolsController@relatedObjects');
                Route::get('/{iaas_network_pools}', 'NetworkPools\NetworkPoolsController@show');

                Route::post('/', 'NetworkPools\NetworkPoolsController@store');
                Route::post('/{iaas_network_pools}/do/{action}', 'NetworkPools\NetworkPoolsController@doAction');

                Route::patch('/{iaas_network_pools}', 'NetworkPools\NetworkPoolsController@update');
                Route::delete('/{iaas_network_pools}', 'NetworkPools\NetworkPoolsController@destroy');
            }
        );

        Route::prefix('datacenters')->group(
            function () {
                Route::get('/', 'Datacenters\DatacentersController@index');
                Route::get('/actions', 'Datacenters\DatacentersController@getActions');

                Route::get('{iaas_datacenters}/tags ', 'Datacenters\DatacentersController@tags');
                Route::post('{iaas_datacenters}/tags ', 'Datacenters\DatacentersController@saveTags');
                Route::get('{iaas_datacenters}/addresses ', 'Datacenters\DatacentersController@addresses');
                Route::post('{iaas_datacenters}/addresses ', 'Datacenters\DatacentersController@saveAddresses');

                Route::get('/{iaas_datacenters}/{subObjects}', 'Datacenters\DatacentersController@relatedObjects');
                Route::get('/{iaas_datacenters}', 'Datacenters\DatacentersController@show');

                Route::post('/', 'Datacenters\DatacentersController@store');
                Route::post('/{iaas_datacenters}/do/{action}', 'Datacenters\DatacentersController@doAction');

                Route::patch('/{iaas_datacenters}', 'Datacenters\DatacentersController@update');
                Route::delete('/{iaas_datacenters}', 'Datacenters\DatacentersController@destroy');
            }
        );

        Route::prefix('compute-pools')->group(
            function () {
                Route::get('/', 'ComputePools\ComputePoolsController@index');
                Route::get('/actions', 'ComputePools\ComputePoolsController@getActions');

                Route::get('{iaas_compute_pools}/tags ', 'ComputePools\ComputePoolsController@tags');
                Route::post('{iaas_compute_pools}/tags ', 'ComputePools\ComputePoolsController@saveTags');
                Route::get('{iaas_compute_pools}/addresses ', 'ComputePools\ComputePoolsController@addresses');
                Route::post('{iaas_compute_pools}/addresses ', 'ComputePools\ComputePoolsController@saveAddresses');

                Route::get('/{iaas_compute_pools}/{subObjects}', 'ComputePools\ComputePoolsController@relatedObjects');
                Route::get('/{iaas_compute_pools}', 'ComputePools\ComputePoolsController@show');

                Route::post('/', 'ComputePools\ComputePoolsController@store');
                Route::post('/{iaas_compute_pools}/do/{action}', 'ComputePools\ComputePoolsController@doAction');

                Route::patch('/{iaas_compute_pools}', 'ComputePools\ComputePoolsController@update');
                Route::delete('/{iaas_compute_pools}', 'ComputePools\ComputePoolsController@destroy');
            }
        );

        Route::prefix('virtual-machines')->group(
            function () {
                Route::get('/', 'VirtualMachines\VirtualMachinesController@index');
                Route::get('/actions', 'VirtualMachines\VirtualMachinesController@getActions');

                Route::get('{iaas_virtual_machines}/tags ', 'VirtualMachines\VirtualMachinesController@tags');
                Route::post('{iaas_virtual_machines}/tags ', 'VirtualMachines\VirtualMachinesController@saveTags');
                Route::get('{iaas_virtual_machines}/addresses ', 'VirtualMachines\VirtualMachinesController@addresses');
                Route::post('{iaas_virtual_machines}/addresses ', 'VirtualMachines\VirtualMachinesController@saveAddresses');

                Route::get('/{iaas_virtual_machines}/{subObjects}', 'VirtualMachines\VirtualMachinesController@relatedObjects');
                Route::get('/{iaas_virtual_machines}', 'VirtualMachines\VirtualMachinesController@show');

                Route::post('/', 'VirtualMachines\VirtualMachinesController@store');
                Route::post('/{iaas_virtual_machines}/do/{action}', 'VirtualMachines\VirtualMachinesController@doAction');

                Route::patch('/{iaas_virtual_machines}', 'VirtualMachines\VirtualMachinesController@update');
                Route::delete('/{iaas_virtual_machines}', 'VirtualMachines\VirtualMachinesController@destroy');
            }
        );

        Route::prefix('storage-volumes')->group(
            function () {
                Route::get('/', 'StorageVolumes\StorageVolumesController@index');
                Route::get('/actions', 'StorageVolumes\StorageVolumesController@getActions');

                Route::get('{iaas_storage_volumes}/tags ', 'StorageVolumes\StorageVolumesController@tags');
                Route::post('{iaas_storage_volumes}/tags ', 'StorageVolumes\StorageVolumesController@saveTags');
                Route::get('{iaas_storage_volumes}/addresses ', 'StorageVolumes\StorageVolumesController@addresses');
                Route::post('{iaas_storage_volumes}/addresses ', 'StorageVolumes\StorageVolumesController@saveAddresses');

                Route::get('/{iaas_storage_volumes}/{subObjects}', 'StorageVolumes\StorageVolumesController@relatedObjects');
                Route::get('/{iaas_storage_volumes}', 'StorageVolumes\StorageVolumesController@show');

                Route::post('/', 'StorageVolumes\StorageVolumesController@store');
                Route::post('/{iaas_storage_volumes}/do/{action}', 'StorageVolumes\StorageVolumesController@doAction');

                Route::patch('/{iaas_storage_volumes}', 'StorageVolumes\StorageVolumesController@update');
                Route::delete('/{iaas_storage_volumes}', 'StorageVolumes\StorageVolumesController@destroy');
            }
        );

        Route::prefix('network-member-devices')->group(
            function () {
                Route::get('/', 'NetworkMemberDevices\NetworkMemberDevicesController@index');
                Route::get('/actions', 'NetworkMemberDevices\NetworkMemberDevicesController@getActions');

                Route::get('{iaas_network_member_devices}/tags ', 'NetworkMemberDevices\NetworkMemberDevicesController@tags');
                Route::post('{iaas_network_member_devices}/tags ', 'NetworkMemberDevices\NetworkMemberDevicesController@saveTags');
                Route::get('{iaas_network_member_devices}/addresses ', 'NetworkMemberDevices\NetworkMemberDevicesController@addresses');
                Route::post('{iaas_network_member_devices}/addresses ', 'NetworkMemberDevices\NetworkMemberDevicesController@saveAddresses');

                Route::get('/{iaas_network_member_devices}/{subObjects}', 'NetworkMemberDevices\NetworkMemberDevicesController@relatedObjects');
                Route::get('/{iaas_network_member_devices}', 'NetworkMemberDevices\NetworkMemberDevicesController@show');

                Route::post('/', 'NetworkMemberDevices\NetworkMemberDevicesController@store');
                Route::post('/{iaas_network_member_devices}/do/{action}', 'NetworkMemberDevices\NetworkMemberDevicesController@doAction');

                Route::patch('/{iaas_network_member_devices}', 'NetworkMemberDevices\NetworkMemberDevicesController@update');
                Route::delete('/{iaas_network_member_devices}', 'NetworkMemberDevices\NetworkMemberDevicesController@destroy');
            }
        );

        Route::prefix('ansible-servers')->group(
            function () {
                Route::get('/', 'AnsibleServers\AnsibleServersController@index');
                Route::get('/actions', 'AnsibleServers\AnsibleServersController@getActions');

                Route::get('{iaas_ansible_servers}/tags ', 'AnsibleServers\AnsibleServersController@tags');
                Route::post('{iaas_ansible_servers}/tags ', 'AnsibleServers\AnsibleServersController@saveTags');
                Route::get('{iaas_ansible_servers}/addresses ', 'AnsibleServers\AnsibleServersController@addresses');
                Route::post('{iaas_ansible_servers}/addresses ', 'AnsibleServers\AnsibleServersController@saveAddresses');

                Route::get('/{iaas_ansible_servers}/{subObjects}', 'AnsibleServers\AnsibleServersController@relatedObjects');
                Route::get('/{iaas_ansible_servers}', 'AnsibleServers\AnsibleServersController@show');

                Route::post('/', 'AnsibleServers\AnsibleServersController@store');
                Route::post('/{iaas_ansible_servers}/do/{action}', 'AnsibleServers\AnsibleServersController@doAction');

                Route::patch('/{iaas_ansible_servers}', 'AnsibleServers\AnsibleServersController@update');
                Route::delete('/{iaas_ansible_servers}', 'AnsibleServers\AnsibleServersController@destroy');
            }
        );

        Route::prefix('ansible-roles')->group(
            function () {
                Route::get('/', 'AnsibleRoles\AnsibleRolesController@index');
                Route::get('/actions', 'AnsibleRoles\AnsibleRolesController@getActions');

                Route::get('{iaas_ansible_roles}/tags ', 'AnsibleRoles\AnsibleRolesController@tags');
                Route::post('{iaas_ansible_roles}/tags ', 'AnsibleRoles\AnsibleRolesController@saveTags');
                Route::get('{iaas_ansible_roles}/addresses ', 'AnsibleRoles\AnsibleRolesController@addresses');
                Route::post('{iaas_ansible_roles}/addresses ', 'AnsibleRoles\AnsibleRolesController@saveAddresses');

                Route::get('/{iaas_ansible_roles}/{subObjects}', 'AnsibleRoles\AnsibleRolesController@relatedObjects');
                Route::get('/{iaas_ansible_roles}', 'AnsibleRoles\AnsibleRolesController@show');

                Route::post('/', 'AnsibleRoles\AnsibleRolesController@store');
                Route::post('/{iaas_ansible_roles}/do/{action}', 'AnsibleRoles\AnsibleRolesController@doAction');

                Route::patch('/{iaas_ansible_roles}', 'AnsibleRoles\AnsibleRolesController@update');
                Route::delete('/{iaas_ansible_roles}', 'AnsibleRoles\AnsibleRolesController@destroy');
            }
        );

        Route::prefix('storage-member-devices')->group(
            function () {
                Route::get('/', 'StorageMemberDevices\StorageMemberDevicesController@index');
                Route::get('/actions', 'StorageMemberDevices\StorageMemberDevicesController@getActions');

                Route::get('{iaas_storage_member_devices}/tags ', 'StorageMemberDevices\StorageMemberDevicesController@tags');
                Route::post('{iaas_storage_member_devices}/tags ', 'StorageMemberDevices\StorageMemberDevicesController@saveTags');
                Route::get('{iaas_storage_member_devices}/addresses ', 'StorageMemberDevices\StorageMemberDevicesController@addresses');
                Route::post('{iaas_storage_member_devices}/addresses ', 'StorageMemberDevices\StorageMemberDevicesController@saveAddresses');

                Route::get('/{iaas_storage_member_devices}/{subObjects}', 'StorageMemberDevices\StorageMemberDevicesController@relatedObjects');
                Route::get('/{iaas_storage_member_devices}', 'StorageMemberDevices\StorageMemberDevicesController@show');

                Route::post('/', 'StorageMemberDevices\StorageMemberDevicesController@store');
                Route::post('/{iaas_storage_member_devices}/do/{action}', 'StorageMemberDevices\StorageMemberDevicesController@doAction');

                Route::patch('/{iaas_storage_member_devices}', 'StorageMemberDevices\StorageMemberDevicesController@update');
                Route::delete('/{iaas_storage_member_devices}', 'StorageMemberDevices\StorageMemberDevicesController@destroy');
            }
        );

        Route::prefix('network-members')->group(
            function () {
                Route::get('/', 'NetworkMembers\NetworkMembersController@index');
                Route::get('/actions', 'NetworkMembers\NetworkMembersController@getActions');

                Route::get('{iaas_network_members}/tags ', 'NetworkMembers\NetworkMembersController@tags');
                Route::post('{iaas_network_members}/tags ', 'NetworkMembers\NetworkMembersController@saveTags');
                Route::get('{iaas_network_members}/addresses ', 'NetworkMembers\NetworkMembersController@addresses');
                Route::post('{iaas_network_members}/addresses ', 'NetworkMembers\NetworkMembersController@saveAddresses');

                Route::get('/{iaas_network_members}/{subObjects}', 'NetworkMembers\NetworkMembersController@relatedObjects');
                Route::get('/{iaas_network_members}', 'NetworkMembers\NetworkMembersController@show');

                Route::post('/', 'NetworkMembers\NetworkMembersController@store');
                Route::post('/{iaas_network_members}/do/{action}', 'NetworkMembers\NetworkMembersController@doAction');

                Route::patch('/{iaas_network_members}', 'NetworkMembers\NetworkMembersController@update');
                Route::delete('/{iaas_network_members}', 'NetworkMembers\NetworkMembersController@destroy');
            }
        );

        Route::prefix('ansible-system-playbook-executions')->group(
            function () {
                Route::get('/', 'AnsibleSystemPlaybookExecutions\AnsibleSystemPlaybookExecutionsController@index');
                Route::get('/actions', 'AnsibleSystemPlaybookExecutions\AnsibleSystemPlaybookExecutionsController@getActions');

                Route::get('{iaspe}/tags ', 'AnsibleSystemPlaybookExecutions\AnsibleSystemPlaybookExecutionsController@tags');
                Route::post('{iaspe}/tags ', 'AnsibleSystemPlaybookExecutions\AnsibleSystemPlaybookExecutionsController@saveTags');
                Route::get('{iaspe}/addresses ', 'AnsibleSystemPlaybookExecutions\AnsibleSystemPlaybookExecutionsController@addresses');
                Route::post('{iaspe}/addresses ', 'AnsibleSystemPlaybookExecutions\AnsibleSystemPlaybookExecutionsController@saveAddresses');

                Route::get('/{iaspe}/{subObjects}', 'AnsibleSystemPlaybookExecutions\AnsibleSystemPlaybookExecutionsController@relatedObjects');
                Route::get('/{iaspe}', 'AnsibleSystemPlaybookExecutions\AnsibleSystemPlaybookExecutionsController@show');

                Route::post('/', 'AnsibleSystemPlaybookExecutions\AnsibleSystemPlaybookExecutionsController@store');
                Route::post('/{iaspe}/do/{action}', 'AnsibleSystemPlaybookExecutions\AnsibleSystemPlaybookExecutionsController@doAction');

                Route::patch('/{iaspe}', 'AnsibleSystemPlaybookExecutions\AnsibleSystemPlaybookExecutionsController@update');
                Route::delete('/{iaspe}', 'AnsibleSystemPlaybookExecutions\AnsibleSystemPlaybookExecutionsController@destroy');
            }
        );

        Route::prefix('ansible-playbooks')->group(
            function () {
                Route::get('/', 'AnsiblePlaybooks\AnsiblePlaybooksController@index');
                Route::get('/actions', 'AnsiblePlaybooks\AnsiblePlaybooksController@getActions');

                Route::get('{iaas_ansible_playbooks}/tags ', 'AnsiblePlaybooks\AnsiblePlaybooksController@tags');
                Route::post('{iaas_ansible_playbooks}/tags ', 'AnsiblePlaybooks\AnsiblePlaybooksController@saveTags');
                Route::get('{iaas_ansible_playbooks}/addresses ', 'AnsiblePlaybooks\AnsiblePlaybooksController@addresses');
                Route::post('{iaas_ansible_playbooks}/addresses ', 'AnsiblePlaybooks\AnsiblePlaybooksController@saveAddresses');

                Route::get('/{iaas_ansible_playbooks}/{subObjects}', 'AnsiblePlaybooks\AnsiblePlaybooksController@relatedObjects');
                Route::get('/{iaas_ansible_playbooks}', 'AnsiblePlaybooks\AnsiblePlaybooksController@show');

                Route::post('/', 'AnsiblePlaybooks\AnsiblePlaybooksController@store');
                Route::post('/{iaas_ansible_playbooks}/do/{action}', 'AnsiblePlaybooks\AnsiblePlaybooksController@doAction');

                Route::patch('/{iaas_ansible_playbooks}', 'AnsiblePlaybooks\AnsiblePlaybooksController@update');
                Route::delete('/{iaas_ansible_playbooks}', 'AnsiblePlaybooks\AnsiblePlaybooksController@destroy');
            }
        );

        Route::prefix('ansible-system-plays')->group(
            function () {
                Route::get('/', 'AnsibleSystemPlays\AnsibleSystemPlaysController@index');
                Route::get('/actions', 'AnsibleSystemPlays\AnsibleSystemPlaysController@getActions');

                Route::get('{iaas_ansible_system_plays}/tags ', 'AnsibleSystemPlays\AnsibleSystemPlaysController@tags');
                Route::post('{iaas_ansible_system_plays}/tags ', 'AnsibleSystemPlays\AnsibleSystemPlaysController@saveTags');
                Route::get('{iaas_ansible_system_plays}/addresses ', 'AnsibleSystemPlays\AnsibleSystemPlaysController@addresses');
                Route::post('{iaas_ansible_system_plays}/addresses ', 'AnsibleSystemPlays\AnsibleSystemPlaysController@saveAddresses');

                Route::get('/{iaas_ansible_system_plays}/{subObjects}', 'AnsibleSystemPlays\AnsibleSystemPlaysController@relatedObjects');
                Route::get('/{iaas_ansible_system_plays}', 'AnsibleSystemPlays\AnsibleSystemPlaysController@show');

                Route::post('/', 'AnsibleSystemPlays\AnsibleSystemPlaysController@store');
                Route::post('/{iaas_ansible_system_plays}/do/{action}', 'AnsibleSystemPlays\AnsibleSystemPlaysController@doAction');

                Route::patch('/{iaas_ansible_system_plays}', 'AnsibleSystemPlays\AnsibleSystemPlaysController@update');
                Route::delete('/{iaas_ansible_system_plays}', 'AnsibleSystemPlays\AnsibleSystemPlaysController@destroy');
            }
        );

        Route::prefix('ansible-system-playbooks')->group(
            function () {
                Route::get('/', 'AnsibleSystemPlaybooks\AnsibleSystemPlaybooksController@index');
                Route::get('/actions', 'AnsibleSystemPlaybooks\AnsibleSystemPlaybooksController@getActions');

                Route::get('{iaas_ansible_system_playbooks}/tags ', 'AnsibleSystemPlaybooks\AnsibleSystemPlaybooksController@tags');
                Route::post('{iaas_ansible_system_playbooks}/tags ', 'AnsibleSystemPlaybooks\AnsibleSystemPlaybooksController@saveTags');
                Route::get('{iaas_ansible_system_playbooks}/addresses ', 'AnsibleSystemPlaybooks\AnsibleSystemPlaybooksController@addresses');
                Route::post('{iaas_ansible_system_playbooks}/addresses ', 'AnsibleSystemPlaybooks\AnsibleSystemPlaybooksController@saveAddresses');

                Route::get('/{iaas_ansible_system_playbooks}/{subObjects}', 'AnsibleSystemPlaybooks\AnsibleSystemPlaybooksController@relatedObjects');
                Route::get('/{iaas_ansible_system_playbooks}', 'AnsibleSystemPlaybooks\AnsibleSystemPlaybooksController@show');

                Route::post('/', 'AnsibleSystemPlaybooks\AnsibleSystemPlaybooksController@store');
                Route::post('/{iaas_ansible_system_playbooks}/do/{action}', 'AnsibleSystemPlaybooks\AnsibleSystemPlaybooksController@doAction');

                Route::patch('/{iaas_ansible_system_playbooks}', 'AnsibleSystemPlaybooks\AnsibleSystemPlaybooksController@update');
                Route::delete('/{iaas_ansible_system_playbooks}', 'AnsibleSystemPlaybooks\AnsibleSystemPlaybooksController@destroy');
            }
        );

        Route::prefix('gateways')->group(
            function () {
                Route::get('/', 'Gateways\GatewaysController@index');
                Route::get('/actions', 'Gateways\GatewaysController@getActions');

                Route::get('{iaas_gateways}/tags ', 'Gateways\GatewaysController@tags');
                Route::post('{iaas_gateways}/tags ', 'Gateways\GatewaysController@saveTags');
                Route::get('{iaas_gateways}/addresses ', 'Gateways\GatewaysController@addresses');
                Route::post('{iaas_gateways}/addresses ', 'Gateways\GatewaysController@saveAddresses');

                Route::get('/{iaas_gateways}/{subObjects}', 'Gateways\GatewaysController@relatedObjects');
                Route::get('/{iaas_gateways}', 'Gateways\GatewaysController@show');

                Route::post('/', 'Gateways\GatewaysController@store');
                Route::post('/{iaas_gateways}/do/{action}', 'Gateways\GatewaysController@doAction');

                Route::patch('/{iaas_gateways}', 'Gateways\GatewaysController@update');
                Route::delete('/{iaas_gateways}', 'Gateways\GatewaysController@destroy');
            }
        );

        Route::prefix('cloud-nodes')->group(
            function () {
                Route::get('/', 'CloudNodes\CloudNodesController@index');
                Route::get('/actions', 'CloudNodes\CloudNodesController@getActions');

                Route::get('{iaas_cloud_nodes}/tags ', 'CloudNodes\CloudNodesController@tags');
                Route::post('{iaas_cloud_nodes}/tags ', 'CloudNodes\CloudNodesController@saveTags');
                Route::get('{iaas_cloud_nodes}/addresses ', 'CloudNodes\CloudNodesController@addresses');
                Route::post('{iaas_cloud_nodes}/addresses ', 'CloudNodes\CloudNodesController@saveAddresses');

                Route::get('/{iaas_cloud_nodes}/{subObjects}', 'CloudNodes\CloudNodesController@relatedObjects');
                Route::get('/{iaas_cloud_nodes}', 'CloudNodes\CloudNodesController@show');

                Route::post('/', 'CloudNodes\CloudNodesController@store');
                Route::post('/{iaas_cloud_nodes}/do/{action}', 'CloudNodes\CloudNodesController@doAction');

                Route::patch('/{iaas_cloud_nodes}', 'CloudNodes\CloudNodesController@update');
                Route::delete('/{iaas_cloud_nodes}', 'CloudNodes\CloudNodesController@destroy');
            }
        );

        Route::prefix('storage-pools')->group(
            function () {
                Route::get('/', 'StoragePools\StoragePoolsController@index');
                Route::get('/actions', 'StoragePools\StoragePoolsController@getActions');

                Route::get('{iaas_storage_pools}/tags ', 'StoragePools\StoragePoolsController@tags');
                Route::post('{iaas_storage_pools}/tags ', 'StoragePools\StoragePoolsController@saveTags');
                Route::get('{iaas_storage_pools}/addresses ', 'StoragePools\StoragePoolsController@addresses');
                Route::post('{iaas_storage_pools}/addresses ', 'StoragePools\StoragePoolsController@saveAddresses');

                Route::get('/{iaas_storage_pools}/{subObjects}', 'StoragePools\StoragePoolsController@relatedObjects');
                Route::get('/{iaas_storage_pools}', 'StoragePools\StoragePoolsController@show');

                Route::post('/', 'StoragePools\StoragePoolsController@store');
                Route::post('/{iaas_storage_pools}/do/{action}', 'StoragePools\StoragePoolsController@doAction');

                Route::patch('/{iaas_storage_pools}', 'StoragePools\StoragePoolsController@update');
                Route::delete('/{iaas_storage_pools}', 'StoragePools\StoragePoolsController@destroy');
            }
        );

        Route::prefix('ansible-playbook-ansible-role')->group(
            function () {
                Route::get('/', 'AnsiblePlaybookAnsibleRole\AnsiblePlaybookAnsibleRoleController@index');
                Route::get('/actions', 'AnsiblePlaybookAnsibleRole\AnsiblePlaybookAnsibleRoleController@getActions');

                Route::get('{iapar}/tags ', 'AnsiblePlaybookAnsibleRole\AnsiblePlaybookAnsibleRoleController@tags');
                Route::post('{iapar}/tags ', 'AnsiblePlaybookAnsibleRole\AnsiblePlaybookAnsibleRoleController@saveTags');
                Route::get('{iapar}/addresses ', 'AnsiblePlaybookAnsibleRole\AnsiblePlaybookAnsibleRoleController@addresses');
                Route::post('{iapar}/addresses ', 'AnsiblePlaybookAnsibleRole\AnsiblePlaybookAnsibleRoleController@saveAddresses');

                Route::get('/{iapar}/{subObjects}', 'AnsiblePlaybookAnsibleRole\AnsiblePlaybookAnsibleRoleController@relatedObjects');
                Route::get('/{iapar}', 'AnsiblePlaybookAnsibleRole\AnsiblePlaybookAnsibleRoleController@show');

                Route::post('/', 'AnsiblePlaybookAnsibleRole\AnsiblePlaybookAnsibleRoleController@store');
                Route::post('/{iapar}/do/{action}', 'AnsiblePlaybookAnsibleRole\AnsiblePlaybookAnsibleRoleController@doAction');

                Route::patch('/{iapar}', 'AnsiblePlaybookAnsibleRole\AnsiblePlaybookAnsibleRoleController@update');
                Route::delete('/{iapar}', 'AnsiblePlaybookAnsibleRole\AnsiblePlaybookAnsibleRoleController@destroy');
            }
        );

        Route::prefix('storage-members')->group(
            function () {
                Route::get('/', 'StorageMembers\StorageMembersController@index');
                Route::get('/actions', 'StorageMembers\StorageMembersController@getActions');

                Route::get('{iaas_storage_members}/tags ', 'StorageMembers\StorageMembersController@tags');
                Route::post('{iaas_storage_members}/tags ', 'StorageMembers\StorageMembersController@saveTags');
                Route::get('{iaas_storage_members}/addresses ', 'StorageMembers\StorageMembersController@addresses');
                Route::post('{iaas_storage_members}/addresses ', 'StorageMembers\StorageMembersController@saveAddresses');

                Route::get('/{iaas_storage_members}/{subObjects}', 'StorageMembers\StorageMembersController@relatedObjects');
                Route::get('/{iaas_storage_members}', 'StorageMembers\StorageMembersController@show');

                Route::post('/', 'StorageMembers\StorageMembersController@store');
                Route::post('/{iaas_storage_members}/do/{action}', 'StorageMembers\StorageMembersController@doAction');

                Route::patch('/{iaas_storage_members}', 'StorageMembers\StorageMembersController@update');
                Route::delete('/{iaas_storage_members}', 'StorageMembers\StorageMembersController@destroy');
            }
        );

        Route::prefix('ip-address-history')->group(
            function () {
                Route::get('/', 'IpAddressHistory\IpAddressHistoryController@index');
                Route::get('/actions', 'IpAddressHistory\IpAddressHistoryController@getActions');

                Route::get('{iaas_ip_address_history}/tags ', 'IpAddressHistory\IpAddressHistoryController@tags');
                Route::post('{iaas_ip_address_history}/tags ', 'IpAddressHistory\IpAddressHistoryController@saveTags');
                Route::get('{iaas_ip_address_history}/addresses ', 'IpAddressHistory\IpAddressHistoryController@addresses');
                Route::post('{iaas_ip_address_history}/addresses ', 'IpAddressHistory\IpAddressHistoryController@saveAddresses');

                Route::get('/{iaas_ip_address_history}/{subObjects}', 'IpAddressHistory\IpAddressHistoryController@relatedObjects');
                Route::get('/{iaas_ip_address_history}', 'IpAddressHistory\IpAddressHistoryController@show');

                Route::post('/', 'IpAddressHistory\IpAddressHistoryController@store');
                Route::post('/{iaas_ip_address_history}/do/{action}', 'IpAddressHistory\IpAddressHistoryController@doAction');

                Route::patch('/{iaas_ip_address_history}', 'IpAddressHistory\IpAddressHistoryController@update');
                Route::delete('/{iaas_ip_address_history}', 'IpAddressHistory\IpAddressHistoryController@destroy');
            }
        );

        Route::prefix('dhcp-servers')->group(
            function () {
                Route::get('/', 'DhcpServers\DhcpServersController@index');
                Route::get('/actions', 'DhcpServers\DhcpServersController@getActions');

                Route::get('{iaas_dhcp_servers}/tags ', 'DhcpServers\DhcpServersController@tags');
                Route::post('{iaas_dhcp_servers}/tags ', 'DhcpServers\DhcpServersController@saveTags');
                Route::get('{iaas_dhcp_servers}/addresses ', 'DhcpServers\DhcpServersController@addresses');
                Route::post('{iaas_dhcp_servers}/addresses ', 'DhcpServers\DhcpServersController@saveAddresses');

                Route::get('/{iaas_dhcp_servers}/{subObjects}', 'DhcpServers\DhcpServersController@relatedObjects');
                Route::get('/{iaas_dhcp_servers}', 'DhcpServers\DhcpServersController@show');

                Route::post('/', 'DhcpServers\DhcpServersController@store');
                Route::post('/{iaas_dhcp_servers}/do/{action}', 'DhcpServers\DhcpServersController@doAction');

                Route::patch('/{iaas_dhcp_servers}', 'DhcpServers\DhcpServersController@update');
                Route::delete('/{iaas_dhcp_servers}', 'DhcpServers\DhcpServersController@destroy');
            }
        );

        Route::prefix('repositories')->group(
            function () {
                Route::get('/', 'Repositories\RepositoriesController@index');
                Route::get('/actions', 'Repositories\RepositoriesController@getActions');

                Route::get('{iaas_repositories}/tags ', 'Repositories\RepositoriesController@tags');
                Route::post('{iaas_repositories}/tags ', 'Repositories\RepositoriesController@saveTags');
                Route::get('{iaas_repositories}/addresses ', 'Repositories\RepositoriesController@addresses');
                Route::post('{iaas_repositories}/addresses ', 'Repositories\RepositoriesController@saveAddresses');

                Route::get('/{iaas_repositories}/{subObjects}', 'Repositories\RepositoriesController@relatedObjects');
                Route::get('/{iaas_repositories}', 'Repositories\RepositoriesController@show');

                Route::post('/', 'Repositories\RepositoriesController@store');
                Route::post('/{iaas_repositories}/do/{action}', 'Repositories\RepositoriesController@doAction');

                Route::patch('/{iaas_repositories}', 'Repositories\RepositoriesController@update');
                Route::delete('/{iaas_repositories}', 'Repositories\RepositoriesController@destroy');
            }
        );

        Route::prefix('repository-images')->group(
            function () {
                Route::get('/', 'RepositoryImages\RepositoryImagesController@index');
                Route::get('/actions', 'RepositoryImages\RepositoryImagesController@getActions');

                Route::get('{iaas_repository_images}/tags ', 'RepositoryImages\RepositoryImagesController@tags');
                Route::post('{iaas_repository_images}/tags ', 'RepositoryImages\RepositoryImagesController@saveTags');
                Route::get('{iaas_repository_images}/addresses ', 'RepositoryImages\RepositoryImagesController@addresses');
                Route::post('{iaas_repository_images}/addresses ', 'RepositoryImages\RepositoryImagesController@saveAddresses');

                Route::get('/{iaas_repository_images}/{subObjects}', 'RepositoryImages\RepositoryImagesController@relatedObjects');
                Route::get('/{iaas_repository_images}', 'RepositoryImages\RepositoryImagesController@show');

                Route::post('/', 'RepositoryImages\RepositoryImagesController@store');
                Route::post('/{iaas_repository_images}/do/{action}', 'RepositoryImages\RepositoryImagesController@doAction');

                Route::patch('/{iaas_repository_images}', 'RepositoryImages\RepositoryImagesController@update');
                Route::delete('/{iaas_repository_images}', 'RepositoryImages\RepositoryImagesController@destroy');
            }
        );

        Route::prefix('accounts')->group(
            function () {
                Route::get('/', 'Accounts\AccountsController@index');
                Route::get('/actions', 'Accounts\AccountsController@getActions');

                Route::get('{iaas_accounts}/tags ', 'Accounts\AccountsController@tags');
                Route::post('{iaas_accounts}/tags ', 'Accounts\AccountsController@saveTags');
                Route::get('{iaas_accounts}/addresses ', 'Accounts\AccountsController@addresses');
                Route::post('{iaas_accounts}/addresses ', 'Accounts\AccountsController@saveAddresses');

                Route::get('/{iaas_accounts}/{subObjects}', 'Accounts\AccountsController@relatedObjects');
                Route::get('/{iaas_accounts}', 'Accounts\AccountsController@show');

                Route::post('/', 'Accounts\AccountsController@store');
                Route::post('/{iaas_accounts}/do/{action}', 'Accounts\AccountsController@doAction');

                Route::patch('/{iaas_accounts}', 'Accounts\AccountsController@update');
                Route::delete('/{iaas_accounts}', 'Accounts\AccountsController@destroy');
            }
        );

        Route::prefix('ansible-playbook-executions')->group(
            function () {
                Route::get('/', 'AnsiblePlaybookExecutions\AnsiblePlaybookExecutionsController@index');
                Route::get('/actions', 'AnsiblePlaybookExecutions\AnsiblePlaybookExecutionsController@getActions');

                Route::get('{iaas_ansible_playbook_executions}/tags ', 'AnsiblePlaybookExecutions\AnsiblePlaybookExecutionsController@tags');
                Route::post('{iaas_ansible_playbook_executions}/tags ', 'AnsiblePlaybookExecutions\AnsiblePlaybookExecutionsController@saveTags');
                Route::get('{iaas_ansible_playbook_executions}/addresses ', 'AnsiblePlaybookExecutions\AnsiblePlaybookExecutionsController@addresses');
                Route::post('{iaas_ansible_playbook_executions}/addresses ', 'AnsiblePlaybookExecutions\AnsiblePlaybookExecutionsController@saveAddresses');

                Route::get('/{iaas_ansible_playbook_executions}/{subObjects}', 'AnsiblePlaybookExecutions\AnsiblePlaybookExecutionsController@relatedObjects');
                Route::get('/{iaas_ansible_playbook_executions}', 'AnsiblePlaybookExecutions\AnsiblePlaybookExecutionsController@show');

                Route::post('/', 'AnsiblePlaybookExecutions\AnsiblePlaybookExecutionsController@store');
                Route::post('/{iaas_ansible_playbook_executions}/do/{action}', 'AnsiblePlaybookExecutions\AnsiblePlaybookExecutionsController@doAction');

                Route::patch('/{iaas_ansible_playbook_executions}', 'AnsiblePlaybookExecutions\AnsiblePlaybookExecutionsController@update');
                Route::delete('/{iaas_ansible_playbook_executions}', 'AnsiblePlaybookExecutions\AnsiblePlaybookExecutionsController@destroy');
            }
        );

        Route::prefix('compute-member-metrics')->group(
            function () {
                Route::get('/', 'ComputeMemberMetrics\ComputeMemberMetricsController@index');
                Route::get('/actions', 'ComputeMemberMetrics\ComputeMemberMetricsController@getActions');

                Route::get('{iaas_compute_member_metrics}/tags ', 'ComputeMemberMetrics\ComputeMemberMetricsController@tags');
                Route::post('{iaas_compute_member_metrics}/tags ', 'ComputeMemberMetrics\ComputeMemberMetricsController@saveTags');
                Route::get('{iaas_compute_member_metrics}/addresses ', 'ComputeMemberMetrics\ComputeMemberMetricsController@addresses');
                Route::post('{iaas_compute_member_metrics}/addresses ', 'ComputeMemberMetrics\ComputeMemberMetricsController@saveAddresses');

                Route::get('/{iaas_compute_member_metrics}/{subObjects}', 'ComputeMemberMetrics\ComputeMemberMetricsController@relatedObjects');
                Route::get('/{iaas_compute_member_metrics}', 'ComputeMemberMetrics\ComputeMemberMetricsController@show');

                Route::post('/', 'ComputeMemberMetrics\ComputeMemberMetricsController@store');
                Route::post('/{iaas_compute_member_metrics}/do/{action}', 'ComputeMemberMetrics\ComputeMemberMetricsController@doAction');

                Route::patch('/{iaas_compute_member_metrics}', 'ComputeMemberMetrics\ComputeMemberMetricsController@update');
                Route::delete('/{iaas_compute_member_metrics}', 'ComputeMemberMetrics\ComputeMemberMetricsController@destroy');
            }
        );

        Route::prefix('virtual-machine-metrics')->group(
            function () {
                Route::get('/', 'VirtualMachineMetrics\VirtualMachineMetricsController@index');
                Route::get('/actions', 'VirtualMachineMetrics\VirtualMachineMetricsController@getActions');

                Route::get('{iaas_virtual_machine_metrics}/tags ', 'VirtualMachineMetrics\VirtualMachineMetricsController@tags');
                Route::post('{iaas_virtual_machine_metrics}/tags ', 'VirtualMachineMetrics\VirtualMachineMetricsController@saveTags');
                Route::get('{iaas_virtual_machine_metrics}/addresses ', 'VirtualMachineMetrics\VirtualMachineMetricsController@addresses');
                Route::post('{iaas_virtual_machine_metrics}/addresses ', 'VirtualMachineMetrics\VirtualMachineMetricsController@saveAddresses');

                Route::get('/{iaas_virtual_machine_metrics}/{subObjects}', 'VirtualMachineMetrics\VirtualMachineMetricsController@relatedObjects');
                Route::get('/{iaas_virtual_machine_metrics}', 'VirtualMachineMetrics\VirtualMachineMetricsController@show');

                Route::post('/', 'VirtualMachineMetrics\VirtualMachineMetricsController@store');
                Route::post('/{iaas_virtual_machine_metrics}/do/{action}', 'VirtualMachineMetrics\VirtualMachineMetricsController@doAction');

                Route::patch('/{iaas_virtual_machine_metrics}', 'VirtualMachineMetrics\VirtualMachineMetricsController@update');
                Route::delete('/{iaas_virtual_machine_metrics}', 'VirtualMachineMetrics\VirtualMachineMetricsController@destroy');
            }
        );

        Route::prefix('virtual-machine-backups')->group(
            function () {
                Route::get('/', 'VirtualMachineBackups\VirtualMachineBackupsController@index');
                Route::get('/actions', 'VirtualMachineBackups\VirtualMachineBackupsController@getActions');

                Route::get('{iaas_virtual_machine_backups}/tags ', 'VirtualMachineBackups\VirtualMachineBackupsController@tags');
                Route::post('{iaas_virtual_machine_backups}/tags ', 'VirtualMachineBackups\VirtualMachineBackupsController@saveTags');
                Route::get('{iaas_virtual_machine_backups}/addresses ', 'VirtualMachineBackups\VirtualMachineBackupsController@addresses');
                Route::post('{iaas_virtual_machine_backups}/addresses ', 'VirtualMachineBackups\VirtualMachineBackupsController@saveAddresses');

                Route::get('/{iaas_virtual_machine_backups}/{subObjects}', 'VirtualMachineBackups\VirtualMachineBackupsController@relatedObjects');
                Route::get('/{iaas_virtual_machine_backups}', 'VirtualMachineBackups\VirtualMachineBackupsController@show');

                Route::post('/', 'VirtualMachineBackups\VirtualMachineBackupsController@store');
                Route::post('/{iaas_virtual_machine_backups}/do/{action}', 'VirtualMachineBackups\VirtualMachineBackupsController@doAction');

                Route::patch('/{iaas_virtual_machine_backups}', 'VirtualMachineBackups\VirtualMachineBackupsController@update');
                Route::delete('/{iaas_virtual_machine_backups}', 'VirtualMachineBackups\VirtualMachineBackupsController@destroy');
            }
        );

        Route::prefix('virtual-disk-image-stats')->group(
            function () {
                Route::get('/', 'VirtualDiskImageStats\VirtualDiskImageStatsController@index');
                Route::get('/actions', 'VirtualDiskImageStats\VirtualDiskImageStatsController@getActions');

                Route::get('{iaas_virtual_disk_image_stats}/tags ', 'VirtualDiskImageStats\VirtualDiskImageStatsController@tags');
                Route::post('{iaas_virtual_disk_image_stats}/tags ', 'VirtualDiskImageStats\VirtualDiskImageStatsController@saveTags');
                Route::get('{iaas_virtual_disk_image_stats}/addresses ', 'VirtualDiskImageStats\VirtualDiskImageStatsController@addresses');
                Route::post('{iaas_virtual_disk_image_stats}/addresses ', 'VirtualDiskImageStats\VirtualDiskImageStatsController@saveAddresses');

                Route::get('/{iaas_virtual_disk_image_stats}/{subObjects}', 'VirtualDiskImageStats\VirtualDiskImageStatsController@relatedObjects');
                Route::get('/{iaas_virtual_disk_image_stats}', 'VirtualDiskImageStats\VirtualDiskImageStatsController@show');

                Route::post('/', 'VirtualDiskImageStats\VirtualDiskImageStatsController@store');
                Route::post('/{iaas_virtual_disk_image_stats}/do/{action}', 'VirtualDiskImageStats\VirtualDiskImageStatsController@doAction');

                Route::patch('/{iaas_virtual_disk_image_stats}', 'VirtualDiskImageStats\VirtualDiskImageStatsController@update');
                Route::delete('/{iaas_virtual_disk_image_stats}', 'VirtualDiskImageStats\VirtualDiskImageStatsController@destroy');
            }
        );

        Route::prefix('licences')->group(
            function () {
                Route::get('/', 'Licences\LicencesController@index');
                Route::get('/actions', 'Licences\LicencesController@getActions');

                Route::get('{iaas_licences}/tags ', 'Licences\LicencesController@tags');
                Route::post('{iaas_licences}/tags ', 'Licences\LicencesController@saveTags');
                Route::get('{iaas_licences}/addresses ', 'Licences\LicencesController@addresses');
                Route::post('{iaas_licences}/addresses ', 'Licences\LicencesController@saveAddresses');

                Route::get('/{iaas_licences}/{subObjects}', 'Licences\LicencesController@relatedObjects');
                Route::get('/{iaas_licences}', 'Licences\LicencesController@show');

                Route::post('/', 'Licences\LicencesController@store');
                Route::post('/{iaas_licences}/do/{action}', 'Licences\LicencesController@doAction');

                Route::patch('/{iaas_licences}', 'Licences\LicencesController@update');
                Route::delete('/{iaas_licences}', 'Licences\LicencesController@destroy');
            }
        );

        Route::prefix('networks-perspective')->group(
            function () {
                Route::get('/', 'NetworksPerspective\NetworksPerspectiveController@index');
                Route::get('/actions', 'NetworksPerspective\NetworksPerspectiveController@getActions');

                Route::get('{iaas_networks_perspective}/tags ', 'NetworksPerspective\NetworksPerspectiveController@tags');
                Route::post('{iaas_networks_perspective}/tags ', 'NetworksPerspective\NetworksPerspectiveController@saveTags');
                Route::get('{iaas_networks_perspective}/addresses ', 'NetworksPerspective\NetworksPerspectiveController@addresses');
                Route::post('{iaas_networks_perspective}/addresses ', 'NetworksPerspective\NetworksPerspectiveController@saveAddresses');

                Route::get('/{iaas_networks_perspective}/{subObjects}', 'NetworksPerspective\NetworksPerspectiveController@relatedObjects');
                Route::get('/{iaas_networks_perspective}', 'NetworksPerspective\NetworksPerspectiveController@show');

                Route::post('/', 'NetworksPerspective\NetworksPerspectiveController@store');
                Route::post('/{iaas_networks_perspective}/do/{action}', 'NetworksPerspective\NetworksPerspectiveController@doAction');

                Route::patch('/{iaas_networks_perspective}', 'NetworksPerspective\NetworksPerspectiveController@update');
                Route::delete('/{iaas_networks_perspective}', 'NetworksPerspective\NetworksPerspectiveController@destroy');
            }
        );

        Route::prefix('repository-images-perspective')->group(
            function () {
                Route::get('/', 'RepositoryImagesPerspective\RepositoryImagesPerspectiveController@index');
                Route::get('/actions', 'RepositoryImagesPerspective\RepositoryImagesPerspectiveController@getActions');

                Route::get('{irip}/tags ', 'RepositoryImagesPerspective\RepositoryImagesPerspectiveController@tags');
                Route::post('{irip}/tags ', 'RepositoryImagesPerspective\RepositoryImagesPerspectiveController@saveTags');
                Route::get('{irip}/addresses ', 'RepositoryImagesPerspective\RepositoryImagesPerspectiveController@addresses');
                Route::post('{irip}/addresses ', 'RepositoryImagesPerspective\RepositoryImagesPerspectiveController@saveAddresses');

                Route::get('/{irip}/{subObjects}', 'RepositoryImagesPerspective\RepositoryImagesPerspectiveController@relatedObjects');
                Route::get('/{irip}', 'RepositoryImagesPerspective\RepositoryImagesPerspectiveController@show');

                Route::post('/', 'RepositoryImagesPerspective\RepositoryImagesPerspectiveController@store');
                Route::post('/{irip}/do/{action}', 'RepositoryImagesPerspective\RepositoryImagesPerspectiveController@doAction');

                Route::patch('/{irip}', 'RepositoryImagesPerspective\RepositoryImagesPerspectiveController@update');
                Route::delete('/{irip}', 'RepositoryImagesPerspective\RepositoryImagesPerspectiveController@destroy');
            }
        );

        Route::prefix('repositories-perspective')->group(
            function () {
                Route::get('/', 'RepositoriesPerspective\RepositoriesPerspectiveController@index');
                Route::get('/actions', 'RepositoriesPerspective\RepositoriesPerspectiveController@getActions');

                Route::get('{iaas_repositories_perspective}/tags ', 'RepositoriesPerspective\RepositoriesPerspectiveController@tags');
                Route::post('{iaas_repositories_perspective}/tags ', 'RepositoriesPerspective\RepositoriesPerspectiveController@saveTags');
                Route::get('{iaas_repositories_perspective}/addresses ', 'RepositoriesPerspective\RepositoriesPerspectiveController@addresses');
                Route::post('{iaas_repositories_perspective}/addresses ', 'RepositoriesPerspective\RepositoriesPerspectiveController@saveAddresses');

                Route::get('/{iaas_repositories_perspective}/{subObjects}', 'RepositoriesPerspective\RepositoriesPerspectiveController@relatedObjects');
                Route::get('/{iaas_repositories_perspective}', 'RepositoriesPerspective\RepositoriesPerspectiveController@show');

                Route::post('/', 'RepositoriesPerspective\RepositoriesPerspectiveController@store');
                Route::post('/{iaas_repositories_perspective}/do/{action}', 'RepositoriesPerspective\RepositoriesPerspectiveController@doAction');

                Route::patch('/{iaas_repositories_perspective}', 'RepositoriesPerspective\RepositoriesPerspectiveController@update');
                Route::delete('/{iaas_repositories_perspective}', 'RepositoriesPerspective\RepositoriesPerspectiveController@destroy');
            }
        );

        Route::prefix('compute-pools-perspective')->group(
            function () {
                Route::get('/', 'ComputePoolsPerspective\ComputePoolsPerspectiveController@index');
                Route::get('/actions', 'ComputePoolsPerspective\ComputePoolsPerspectiveController@getActions');

                Route::get('{iaas_compute_pools_perspective}/tags ', 'ComputePoolsPerspective\ComputePoolsPerspectiveController@tags');
                Route::post('{iaas_compute_pools_perspective}/tags ', 'ComputePoolsPerspective\ComputePoolsPerspectiveController@saveTags');
                Route::get('{iaas_compute_pools_perspective}/addresses ', 'ComputePoolsPerspective\ComputePoolsPerspectiveController@addresses');
                Route::post('{iaas_compute_pools_perspective}/addresses ', 'ComputePoolsPerspective\ComputePoolsPerspectiveController@saveAddresses');

                Route::get('/{iaas_compute_pools_perspective}/{subObjects}', 'ComputePoolsPerspective\ComputePoolsPerspectiveController@relatedObjects');
                Route::get('/{iaas_compute_pools_perspective}', 'ComputePoolsPerspective\ComputePoolsPerspectiveController@show');

                Route::post('/', 'ComputePoolsPerspective\ComputePoolsPerspectiveController@store');
                Route::post('/{iaas_compute_pools_perspective}/do/{action}', 'ComputePoolsPerspective\ComputePoolsPerspectiveController@doAction');

                Route::patch('/{iaas_compute_pools_perspective}', 'ComputePoolsPerspective\ComputePoolsPerspectiveController@update');
                Route::delete('/{iaas_compute_pools_perspective}', 'ComputePoolsPerspective\ComputePoolsPerspectiveController@destroy');
            }
        );

        Route::prefix('storage-pools-perspective')->group(
            function () {
                Route::get('/', 'StoragePoolsPerspective\StoragePoolsPerspectiveController@index');
                Route::get('/actions', 'StoragePoolsPerspective\StoragePoolsPerspectiveController@getActions');

                Route::get('{iaas_storage_pools_perspective}/tags ', 'StoragePoolsPerspective\StoragePoolsPerspectiveController@tags');
                Route::post('{iaas_storage_pools_perspective}/tags ', 'StoragePoolsPerspective\StoragePoolsPerspectiveController@saveTags');
                Route::get('{iaas_storage_pools_perspective}/addresses ', 'StoragePoolsPerspective\StoragePoolsPerspectiveController@addresses');
                Route::post('{iaas_storage_pools_perspective}/addresses ', 'StoragePoolsPerspective\StoragePoolsPerspectiveController@saveAddresses');

                Route::get('/{iaas_storage_pools_perspective}/{subObjects}', 'StoragePoolsPerspective\StoragePoolsPerspectiveController@relatedObjects');
                Route::get('/{iaas_storage_pools_perspective}', 'StoragePoolsPerspective\StoragePoolsPerspectiveController@show');

                Route::post('/', 'StoragePoolsPerspective\StoragePoolsPerspectiveController@store');
                Route::post('/{iaas_storage_pools_perspective}/do/{action}', 'StoragePoolsPerspective\StoragePoolsPerspectiveController@doAction');

                Route::patch('/{iaas_storage_pools_perspective}', 'StoragePoolsPerspective\StoragePoolsPerspectiveController@update');
                Route::delete('/{iaas_storage_pools_perspective}', 'StoragePoolsPerspective\StoragePoolsPerspectiveController@destroy');
            }
        );

        Route::prefix('network-members-perspective')->group(
            function () {
                Route::get('/', 'NetworkMembersPerspective\NetworkMembersPerspectiveController@index');
                Route::get('/actions', 'NetworkMembersPerspective\NetworkMembersPerspectiveController@getActions');

                Route::get('{iaas_network_members_perspective}/tags ', 'NetworkMembersPerspective\NetworkMembersPerspectiveController@tags');
                Route::post('{iaas_network_members_perspective}/tags ', 'NetworkMembersPerspective\NetworkMembersPerspectiveController@saveTags');
                Route::get('{iaas_network_members_perspective}/addresses ', 'NetworkMembersPerspective\NetworkMembersPerspectiveController@addresses');
                Route::post('{iaas_network_members_perspective}/addresses ', 'NetworkMembersPerspective\NetworkMembersPerspectiveController@saveAddresses');

                Route::get('/{iaas_network_members_perspective}/{subObjects}', 'NetworkMembersPerspective\NetworkMembersPerspectiveController@relatedObjects');
                Route::get('/{iaas_network_members_perspective}', 'NetworkMembersPerspective\NetworkMembersPerspectiveController@show');

                Route::post('/', 'NetworkMembersPerspective\NetworkMembersPerspectiveController@store');
                Route::post('/{iaas_network_members_perspective}/do/{action}', 'NetworkMembersPerspective\NetworkMembersPerspectiveController@doAction');

                Route::patch('/{iaas_network_members_perspective}', 'NetworkMembersPerspective\NetworkMembersPerspectiveController@update');
                Route::delete('/{iaas_network_members_perspective}', 'NetworkMembersPerspective\NetworkMembersPerspectiveController@destroy');
            }
        );

        Route::prefix('cloud-nodes-perspective')->group(
            function () {
                Route::get('/', 'CloudNodesPerspective\CloudNodesPerspectiveController@index');
                Route::get('/actions', 'CloudNodesPerspective\CloudNodesPerspectiveController@getActions');

                Route::get('{iaas_cloud_nodes_perspective}/tags ', 'CloudNodesPerspective\CloudNodesPerspectiveController@tags');
                Route::post('{iaas_cloud_nodes_perspective}/tags ', 'CloudNodesPerspective\CloudNodesPerspectiveController@saveTags');
                Route::get('{iaas_cloud_nodes_perspective}/addresses ', 'CloudNodesPerspective\CloudNodesPerspectiveController@addresses');
                Route::post('{iaas_cloud_nodes_perspective}/addresses ', 'CloudNodesPerspective\CloudNodesPerspectiveController@saveAddresses');

                Route::get('/{iaas_cloud_nodes_perspective}/{subObjects}', 'CloudNodesPerspective\CloudNodesPerspectiveController@relatedObjects');
                Route::get('/{iaas_cloud_nodes_perspective}', 'CloudNodesPerspective\CloudNodesPerspectiveController@show');

                Route::post('/', 'CloudNodesPerspective\CloudNodesPerspectiveController@store');
                Route::post('/{iaas_cloud_nodes_perspective}/do/{action}', 'CloudNodesPerspective\CloudNodesPerspectiveController@doAction');

                Route::patch('/{iaas_cloud_nodes_perspective}', 'CloudNodesPerspective\CloudNodesPerspectiveController@update');
                Route::delete('/{iaas_cloud_nodes_perspective}', 'CloudNodesPerspective\CloudNodesPerspectiveController@destroy');
            }
        );

        Route::prefix('network-pools-perspective')->group(
            function () {
                Route::get('/', 'NetworkPoolsPerspective\NetworkPoolsPerspectiveController@index');
                Route::get('/actions', 'NetworkPoolsPerspective\NetworkPoolsPerspectiveController@getActions');

                Route::get('{iaas_network_pools_perspective}/tags ', 'NetworkPoolsPerspective\NetworkPoolsPerspectiveController@tags');
                Route::post('{iaas_network_pools_perspective}/tags ', 'NetworkPoolsPerspective\NetworkPoolsPerspectiveController@saveTags');
                Route::get('{iaas_network_pools_perspective}/addresses ', 'NetworkPoolsPerspective\NetworkPoolsPerspectiveController@addresses');
                Route::post('{iaas_network_pools_perspective}/addresses ', 'NetworkPoolsPerspective\NetworkPoolsPerspectiveController@saveAddresses');

                Route::get('/{iaas_network_pools_perspective}/{subObjects}', 'NetworkPoolsPerspective\NetworkPoolsPerspectiveController@relatedObjects');
                Route::get('/{iaas_network_pools_perspective}', 'NetworkPoolsPerspective\NetworkPoolsPerspectiveController@show');

                Route::post('/', 'NetworkPoolsPerspective\NetworkPoolsPerspectiveController@store');
                Route::post('/{iaas_network_pools_perspective}/do/{action}', 'NetworkPoolsPerspective\NetworkPoolsPerspectiveController@doAction');

                Route::patch('/{iaas_network_pools_perspective}', 'NetworkPoolsPerspective\NetworkPoolsPerspectiveController@update');
                Route::delete('/{iaas_network_pools_perspective}', 'NetworkPoolsPerspective\NetworkPoolsPerspectiveController@destroy');
            }
        );

        Route::prefix('compute-member-storage-volumes-perspective')->group(
            function () {
                Route::get('/', 'ComputeMemberStorageVolumesPerspective\ComputeMemberStorageVolumesPerspectiveController@index');
                Route::get('/actions', 'ComputeMemberStorageVolumesPerspective\ComputeMemberStorageVolumesPerspectiveController@getActions');

                Route::get('{icmsvp}/tags ', 'ComputeMemberStorageVolumesPerspective\ComputeMemberStorageVolumesPerspectiveController@tags');
                Route::post('{icmsvp}/tags ', 'ComputeMemberStorageVolumesPerspective\ComputeMemberStorageVolumesPerspectiveController@saveTags');
                Route::get('{icmsvp}/addresses ', 'ComputeMemberStorageVolumesPerspective\ComputeMemberStorageVolumesPerspectiveController@addresses');
                Route::post('{icmsvp}/addresses ', 'ComputeMemberStorageVolumesPerspective\ComputeMemberStorageVolumesPerspectiveController@saveAddresses');

                Route::get('/{icmsvp}/{subObjects}', 'ComputeMemberStorageVolumesPerspective\ComputeMemberStorageVolumesPerspectiveController@relatedObjects');
                Route::get('/{icmsvp}', 'ComputeMemberStorageVolumesPerspective\ComputeMemberStorageVolumesPerspectiveController@show');

                Route::post('/', 'ComputeMemberStorageVolumesPerspective\ComputeMemberStorageVolumesPerspectiveController@store');
                Route::post('/{icmsvp}/do/{action}', 'ComputeMemberStorageVolumesPerspective\ComputeMemberStorageVolumesPerspectiveController@doAction');

                Route::patch('/{icmsvp}', 'ComputeMemberStorageVolumesPerspective\ComputeMemberStorageVolumesPerspectiveController@update');
                Route::delete('/{icmsvp}', 'ComputeMemberStorageVolumesPerspective\ComputeMemberStorageVolumesPerspectiveController@destroy');
            }
        );

        Route::prefix('storage-volumes-perspective')->group(
            function () {
                Route::get('/', 'StorageVolumesPerspective\StorageVolumesPerspectiveController@index');
                Route::get('/actions', 'StorageVolumesPerspective\StorageVolumesPerspectiveController@getActions');

                Route::get('{iaas_storage_volumes_perspective}/tags ', 'StorageVolumesPerspective\StorageVolumesPerspectiveController@tags');
                Route::post('{iaas_storage_volumes_perspective}/tags ', 'StorageVolumesPerspective\StorageVolumesPerspectiveController@saveTags');
                Route::get('{iaas_storage_volumes_perspective}/addresses ', 'StorageVolumesPerspective\StorageVolumesPerspectiveController@addresses');
                Route::post('{iaas_storage_volumes_perspective}/addresses ', 'StorageVolumesPerspective\StorageVolumesPerspectiveController@saveAddresses');

                Route::get('/{iaas_storage_volumes_perspective}/{subObjects}', 'StorageVolumesPerspective\StorageVolumesPerspectiveController@relatedObjects');
                Route::get('/{iaas_storage_volumes_perspective}', 'StorageVolumesPerspective\StorageVolumesPerspectiveController@show');

                Route::post('/', 'StorageVolumesPerspective\StorageVolumesPerspectiveController@store');
                Route::post('/{iaas_storage_volumes_perspective}/do/{action}', 'StorageVolumesPerspective\StorageVolumesPerspectiveController@doAction');

                Route::patch('/{iaas_storage_volumes_perspective}', 'StorageVolumesPerspective\StorageVolumesPerspectiveController@update');
                Route::delete('/{iaas_storage_volumes_perspective}', 'StorageVolumesPerspective\StorageVolumesPerspectiveController@destroy');
            }
        );

        Route::prefix('virtual-network-cards-perspective')->group(
            function () {
                Route::get('/', 'VirtualNetworkCardsPerspective\VirtualNetworkCardsPerspectiveController@index');
                Route::get('/actions', 'VirtualNetworkCardsPerspective\VirtualNetworkCardsPerspectiveController@getActions');

                Route::get('{ivncp}/tags ', 'VirtualNetworkCardsPerspective\VirtualNetworkCardsPerspectiveController@tags');
                Route::post('{ivncp}/tags ', 'VirtualNetworkCardsPerspective\VirtualNetworkCardsPerspectiveController@saveTags');
                Route::get('{ivncp}/addresses ', 'VirtualNetworkCardsPerspective\VirtualNetworkCardsPerspectiveController@addresses');
                Route::post('{ivncp}/addresses ', 'VirtualNetworkCardsPerspective\VirtualNetworkCardsPerspectiveController@saveAddresses');

                Route::get('/{ivncp}/{subObjects}', 'VirtualNetworkCardsPerspective\VirtualNetworkCardsPerspectiveController@relatedObjects');
                Route::get('/{ivncp}', 'VirtualNetworkCardsPerspective\VirtualNetworkCardsPerspectiveController@show');

                Route::post('/', 'VirtualNetworkCardsPerspective\VirtualNetworkCardsPerspectiveController@store');
                Route::post('/{ivncp}/do/{action}', 'VirtualNetworkCardsPerspective\VirtualNetworkCardsPerspectiveController@doAction');

                Route::patch('/{ivncp}', 'VirtualNetworkCardsPerspective\VirtualNetworkCardsPerspectiveController@update');
                Route::delete('/{ivncp}', 'VirtualNetworkCardsPerspective\VirtualNetworkCardsPerspectiveController@destroy');
            }
        );

        Route::prefix('storage-members-perspective')->group(
            function () {
                Route::get('/', 'StorageMembersPerspective\StorageMembersPerspectiveController@index');
                Route::get('/actions', 'StorageMembersPerspective\StorageMembersPerspectiveController@getActions');

                Route::get('{iaas_storage_members_perspective}/tags ', 'StorageMembersPerspective\StorageMembersPerspectiveController@tags');
                Route::post('{iaas_storage_members_perspective}/tags ', 'StorageMembersPerspective\StorageMembersPerspectiveController@saveTags');
                Route::get('{iaas_storage_members_perspective}/addresses ', 'StorageMembersPerspective\StorageMembersPerspectiveController@addresses');
                Route::post('{iaas_storage_members_perspective}/addresses ', 'StorageMembersPerspective\StorageMembersPerspectiveController@saveAddresses');

                Route::get('/{iaas_storage_members_perspective}/{subObjects}', 'StorageMembersPerspective\StorageMembersPerspectiveController@relatedObjects');
                Route::get('/{iaas_storage_members_perspective}', 'StorageMembersPerspective\StorageMembersPerspectiveController@show');

                Route::post('/', 'StorageMembersPerspective\StorageMembersPerspectiveController@store');
                Route::post('/{iaas_storage_members_perspective}/do/{action}', 'StorageMembersPerspective\StorageMembersPerspectiveController@doAction');

                Route::patch('/{iaas_storage_members_perspective}', 'StorageMembersPerspective\StorageMembersPerspectiveController@update');
                Route::delete('/{iaas_storage_members_perspective}', 'StorageMembersPerspective\StorageMembersPerspectiveController@destroy');
            }
        );

        Route::prefix('datacenters-perspective')->group(
            function () {
                Route::get('/', 'DatacentersPerspective\DatacentersPerspectiveController@index');
                Route::get('/actions', 'DatacentersPerspective\DatacentersPerspectiveController@getActions');

                Route::get('{iaas_datacenters_perspective}/tags ', 'DatacentersPerspective\DatacentersPerspectiveController@tags');
                Route::post('{iaas_datacenters_perspective}/tags ', 'DatacentersPerspective\DatacentersPerspectiveController@saveTags');
                Route::get('{iaas_datacenters_perspective}/addresses ', 'DatacentersPerspective\DatacentersPerspectiveController@addresses');
                Route::post('{iaas_datacenters_perspective}/addresses ', 'DatacentersPerspective\DatacentersPerspectiveController@saveAddresses');

                Route::get('/{iaas_datacenters_perspective}/{subObjects}', 'DatacentersPerspective\DatacentersPerspectiveController@relatedObjects');
                Route::get('/{iaas_datacenters_perspective}', 'DatacentersPerspective\DatacentersPerspectiveController@show');

                Route::post('/', 'DatacentersPerspective\DatacentersPerspectiveController@store');
                Route::post('/{iaas_datacenters_perspective}/do/{action}', 'DatacentersPerspective\DatacentersPerspectiveController@doAction');

                Route::patch('/{iaas_datacenters_perspective}', 'DatacentersPerspective\DatacentersPerspectiveController@update');
                Route::delete('/{iaas_datacenters_perspective}', 'DatacentersPerspective\DatacentersPerspectiveController@destroy');
            }
        );

        Route::prefix('compute-members-perspective')->group(
            function () {
                Route::get('/', 'ComputeMembersPerspective\ComputeMembersPerspectiveController@index');
                Route::get('/actions', 'ComputeMembersPerspective\ComputeMembersPerspectiveController@getActions');

                Route::get('{iaas_compute_members_perspective}/tags ', 'ComputeMembersPerspective\ComputeMembersPerspectiveController@tags');
                Route::post('{iaas_compute_members_perspective}/tags ', 'ComputeMembersPerspective\ComputeMembersPerspectiveController@saveTags');
                Route::get('{iaas_compute_members_perspective}/addresses ', 'ComputeMembersPerspective\ComputeMembersPerspectiveController@addresses');
                Route::post('{iaas_compute_members_perspective}/addresses ', 'ComputeMembersPerspective\ComputeMembersPerspectiveController@saveAddresses');

                Route::get('/{iaas_compute_members_perspective}/{subObjects}', 'ComputeMembersPerspective\ComputeMembersPerspectiveController@relatedObjects');
                Route::get('/{iaas_compute_members_perspective}', 'ComputeMembersPerspective\ComputeMembersPerspectiveController@show');

                Route::post('/', 'ComputeMembersPerspective\ComputeMembersPerspectiveController@store');
                Route::post('/{iaas_compute_members_perspective}/do/{action}', 'ComputeMembersPerspective\ComputeMembersPerspectiveController@doAction');

                Route::patch('/{iaas_compute_members_perspective}', 'ComputeMembersPerspective\ComputeMembersPerspectiveController@update');
                Route::delete('/{iaas_compute_members_perspective}', 'ComputeMembersPerspective\ComputeMembersPerspectiveController@destroy');
            }
        );

        Route::prefix('virtual-machines-perspective')->group(
            function () {
                Route::get('/', 'VirtualMachinesPerspective\VirtualMachinesPerspectiveController@index');
                Route::get('/actions', 'VirtualMachinesPerspective\VirtualMachinesPerspectiveController@getActions');

                Route::get('{ivmp}/tags ', 'VirtualMachinesPerspective\VirtualMachinesPerspectiveController@tags');
                Route::post('{ivmp}/tags ', 'VirtualMachinesPerspective\VirtualMachinesPerspectiveController@saveTags');
                Route::get('{ivmp}/addresses ', 'VirtualMachinesPerspective\VirtualMachinesPerspectiveController@addresses');
                Route::post('{ivmp}/addresses ', 'VirtualMachinesPerspective\VirtualMachinesPerspectiveController@saveAddresses');

                Route::get('/{ivmp}/{subObjects}', 'VirtualMachinesPerspective\VirtualMachinesPerspectiveController@relatedObjects');
                Route::get('/{ivmp}', 'VirtualMachinesPerspective\VirtualMachinesPerspectiveController@show');

                Route::post('/', 'VirtualMachinesPerspective\VirtualMachinesPerspectiveController@store');
                Route::post('/{ivmp}/do/{action}', 'VirtualMachinesPerspective\VirtualMachinesPerspectiveController@doAction');

                Route::patch('/{ivmp}', 'VirtualMachinesPerspective\VirtualMachinesPerspectiveController@update');
                Route::delete('/{ivmp}', 'VirtualMachinesPerspective\VirtualMachinesPerspectiveController@destroy');
            }
        );

        // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

        Route::get('/configurations/dhcp-servers/{server}', [\NextDeveloper\IAAS\Http\Controllers\DhcpServers\DhcpServersConfigurationController::class, 'show']);
        Route::get('/console/{iaas_virtual_machines}', [\NextDeveloper\IAAS\Http\Controllers\VirtualMachines\VirtualMachinesConsoleController::class, 'getConsoleData']);

        Route::get('/metrics/{uuid}', [\NextDeveloper\IAAS\Http\Controllers\VirtualMachines\VirtualMachinesMetricsController::class, 'index']);
        Route::get('/metrics/{uuid}/{metric}', [\NextDeveloper\IAAS\Http\Controllers\VirtualMachines\VirtualMachinesMetricsController::class, 'index']);

        Route::post('/events', [\NextDeveloper\IAAS\Http\Controllers\ComputeMembers\ComputeMemberEventsController::class, 'store']);
    }
);












