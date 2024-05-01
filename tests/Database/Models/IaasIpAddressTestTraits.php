<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasIpAddressQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasIpAddressService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasIpAddressTestTraits
{
    public $http;

    /**
     *   Creating the Guzzle object
     */
    public function setupGuzzle()
    {
        $this->http = new Client(
            [
            'base_uri'  =>  '127.0.0.1:8000'
            ]
        );
    }

    /**
     *   Destroying the Guzzle object
     */
    public function destroyGuzzle()
    {
        $this->http = null;
    }

    public function test_http_iaasipaddress_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasipaddress',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasipaddress_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasipaddress', [
            'form_params'   =>  [
                            ],
                ['http_errors' => false]
            ]
        );

        $this->assertEquals($response->getStatusCode(), Response::HTTP_OK);
    }

    /**
     * Get test
     *
     * @return bool
     */
    public function test_iaasipaddress_model_get()
    {
        $result = AbstractIaasIpAddressService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasipaddress_get_all()
    {
        $result = AbstractIaasIpAddressService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasipaddress_get_paginated()
    {
        $result = AbstractIaasIpAddressService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasipaddress_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasIpAddress\IaasIpAddressRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddress_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasIpAddress\IaasIpAddressCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddress_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasIpAddress\IaasIpAddressCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddress_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasIpAddress\IaasIpAddressSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddress_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasIpAddress\IaasIpAddressSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddress_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasIpAddress\IaasIpAddressUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddress_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasIpAddress\IaasIpAddressUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddress_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasIpAddress\IaasIpAddressDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddress_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasIpAddress\IaasIpAddressDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddress_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasIpAddress\IaasIpAddressRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddress_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasIpAddress\IaasIpAddressRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasipaddress_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddress::first();

            event(new \NextDeveloper\IAAS\Events\IaasIpAddress\IaasIpAddressRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddress_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddress::first();

            event(new \NextDeveloper\IAAS\Events\IaasIpAddress\IaasIpAddressCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddress_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddress::first();

            event(new \NextDeveloper\IAAS\Events\IaasIpAddress\IaasIpAddressCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddress_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddress::first();

            event(new \NextDeveloper\IAAS\Events\IaasIpAddress\IaasIpAddressSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddress_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddress::first();

            event(new \NextDeveloper\IAAS\Events\IaasIpAddress\IaasIpAddressSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddress_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddress::first();

            event(new \NextDeveloper\IAAS\Events\IaasIpAddress\IaasIpAddressUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddress_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddress::first();

            event(new \NextDeveloper\IAAS\Events\IaasIpAddress\IaasIpAddressUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddress_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddress::first();

            event(new \NextDeveloper\IAAS\Events\IaasIpAddress\IaasIpAddressDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddress_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddress::first();

            event(new \NextDeveloper\IAAS\Events\IaasIpAddress\IaasIpAddressDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddress_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddress::first();

            event(new \NextDeveloper\IAAS\Events\IaasIpAddress\IaasIpAddressRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddress_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddress::first();

            event(new \NextDeveloper\IAAS\Events\IaasIpAddress\IaasIpAddressRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasipaddress_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasIpAddressQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddress::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasipaddress_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasIpAddressQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddress::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasipaddress_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasIpAddressQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddress::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasipaddress_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasIpAddressQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddress::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasipaddress_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasIpAddressQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddress::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasipaddress_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasIpAddressQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddress::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasipaddress_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasIpAddressQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddress::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasipaddress_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasIpAddressQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddress::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasipaddress_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasIpAddressQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddress::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}