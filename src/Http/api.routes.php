<?php

Route::prefix('iaas')->group(function() {
Route::prefix('datacenters')->group(function () {
        Route::get('/', 'IaasDatacenter\IaasDatacenterController@index');
        Route::get('/{iaas_datacenters}', 'IaasDatacenter\IaasDatacenterController@show');
        Route::post('/', 'IaasDatacenter\IaasDatacenterController@store');
        Route::patch('/{iaas_datacenters}', 'IaasDatacenter\IaasDatacenterController@update');
        Route::delete('/{iaas_datacenters}', 'IaasDatacenter\IaasDatacenterController@destroy');
    });

// EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
});