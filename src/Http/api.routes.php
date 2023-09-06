<?php

Route::prefix('iaas')->group(function() {
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

// EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE\n\n
});