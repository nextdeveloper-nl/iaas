<?php

Route::prefix('iaas')->group(function() {
Route::prefix('cloud-nodes')->group(function () {
        Route::get('/', 'IaasCloudNode\IaasCloudNodeController@index');
        Route::get('/{iaas_cloud_nodes}', 'IaasCloudNode\IaasCloudNodeController@show');
        Route::post('/', 'IaasCloudNode\IaasCloudNodeController@store');
        Route::patch('/{iaas_cloud_nodes}', 'IaasCloudNode\IaasCloudNodeController@update');
        Route::delete('/{iaas_cloud_nodes}', 'IaasCloudNode\IaasCloudNodeController@destroy');
    });

Route::prefix('compute-members')->group(function () {
        Route::get('/', 'IaasComputeMember\IaasComputeMemberController@index');
        Route::get('/{iaas_compute_members}', 'IaasComputeMember\IaasComputeMemberController@show');
        Route::post('/', 'IaasComputeMember\IaasComputeMemberController@store');
        Route::patch('/{iaas_compute_members}', 'IaasComputeMember\IaasComputeMemberController@update');
        Route::delete('/{iaas_compute_members}', 'IaasComputeMember\IaasComputeMemberController@destroy');
    });

Route::prefix('compute-pools')->group(function () {
        Route::get('/', 'IaasComputePool\IaasComputePoolController@index');
        Route::get('/{iaas_compute_pools}', 'IaasComputePool\IaasComputePoolController@show');
        Route::post('/', 'IaasComputePool\IaasComputePoolController@store');
        Route::patch('/{iaas_compute_pools}', 'IaasComputePool\IaasComputePoolController@update');
        Route::delete('/{iaas_compute_pools}', 'IaasComputePool\IaasComputePoolController@destroy');
    });

Route::prefix('datacenters')->group(function () {
        Route::get('/', 'IaasDatacenter\IaasDatacenterController@index');
        Route::get('/{iaas_datacenters}', 'IaasDatacenter\IaasDatacenterController@show');
        Route::post('/', 'IaasDatacenter\IaasDatacenterController@store');
        Route::patch('/{iaas_datacenters}', 'IaasDatacenter\IaasDatacenterController@update');
        Route::delete('/{iaas_datacenters}', 'IaasDatacenter\IaasDatacenterController@destroy');
    });

Route::prefix('network-pools')->group(function () {
        Route::get('/', 'IaasNetworkPool\IaasNetworkPoolController@index');
        Route::get('/{iaas_network_pools}', 'IaasNetworkPool\IaasNetworkPoolController@show');
        Route::post('/', 'IaasNetworkPool\IaasNetworkPoolController@store');
        Route::patch('/{iaas_network_pools}', 'IaasNetworkPool\IaasNetworkPoolController@update');
        Route::delete('/{iaas_network_pools}', 'IaasNetworkPool\IaasNetworkPoolController@destroy');
    });

Route::prefix('storage-pools')->group(function () {
        Route::get('/', 'IaasStoragePool\IaasStoragePoolController@index');
        Route::get('/{iaas_storage_pools}', 'IaasStoragePool\IaasStoragePoolController@show');
        Route::post('/', 'IaasStoragePool\IaasStoragePoolController@store');
        Route::patch('/{iaas_storage_pools}', 'IaasStoragePool\IaasStoragePoolController@update');
        Route::delete('/{iaas_storage_pools}', 'IaasStoragePool\IaasStoragePoolController@destroy');
    });

// EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE\n\n\n\n
});