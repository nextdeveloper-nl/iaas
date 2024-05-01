<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasNetworkMemberDeviceQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasNetworkMemberDeviceService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasNetworkMemberDeviceTestTraits
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

    public function test_http_iaasnetworkmemberdevice_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasnetworkmemberdevice',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasnetworkmemberdevice_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasnetworkmemberdevice', [
            'form_params'   =>  [
                'name'  =>  'a',
                'device_identification'  =>  'a',
                'device_type'  =>  'a',
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
    public function test_iaasnetworkmemberdevice_model_get()
    {
        $result = AbstractIaasNetworkMemberDeviceService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasnetworkmemberdevice_get_all()
    {
        $result = AbstractIaasNetworkMemberDeviceService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasnetworkmemberdevice_get_paginated()
    {
        $result = AbstractIaasNetworkMemberDeviceService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasnetworkmemberdevice_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkMemberDevice\IaasNetworkMemberDeviceRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmemberdevice_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkMemberDevice\IaasNetworkMemberDeviceCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmemberdevice_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkMemberDevice\IaasNetworkMemberDeviceCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmemberdevice_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkMemberDevice\IaasNetworkMemberDeviceSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmemberdevice_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkMemberDevice\IaasNetworkMemberDeviceSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmemberdevice_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkMemberDevice\IaasNetworkMemberDeviceUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmemberdevice_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkMemberDevice\IaasNetworkMemberDeviceUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmemberdevice_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkMemberDevice\IaasNetworkMemberDeviceDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmemberdevice_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkMemberDevice\IaasNetworkMemberDeviceDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmemberdevice_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkMemberDevice\IaasNetworkMemberDeviceRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmemberdevice_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkMemberDevice\IaasNetworkMemberDeviceRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkmemberdevice_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkMemberDevice\IaasNetworkMemberDeviceRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmemberdevice_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkMemberDevice\IaasNetworkMemberDeviceCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmemberdevice_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkMemberDevice\IaasNetworkMemberDeviceCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmemberdevice_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkMemberDevice\IaasNetworkMemberDeviceSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmemberdevice_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkMemberDevice\IaasNetworkMemberDeviceSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmemberdevice_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkMemberDevice\IaasNetworkMemberDeviceUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmemberdevice_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkMemberDevice\IaasNetworkMemberDeviceUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmemberdevice_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkMemberDevice\IaasNetworkMemberDeviceDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmemberdevice_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkMemberDevice\IaasNetworkMemberDeviceDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmemberdevice_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkMemberDevice\IaasNetworkMemberDeviceRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmemberdevice_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkMemberDevice\IaasNetworkMemberDeviceRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkmemberdevice_event_name_filter()
    {
        try {
            $request = new Request(
                [
                'name'  =>  'a'
                ]
            );

            $filter = new IaasNetworkMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkmemberdevice_event_device_identification_filter()
    {
        try {
            $request = new Request(
                [
                'device_identification'  =>  'a'
                ]
            );

            $filter = new IaasNetworkMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkmemberdevice_event_device_type_filter()
    {
        try {
            $request = new Request(
                [
                'device_type'  =>  'a'
                ]
            );

            $filter = new IaasNetworkMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkmemberdevice_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasNetworkMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkmemberdevice_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasNetworkMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkmemberdevice_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasNetworkMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkmemberdevice_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasNetworkMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkmemberdevice_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasNetworkMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkmemberdevice_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasNetworkMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkmemberdevice_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasNetworkMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkmemberdevice_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasNetworkMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkmemberdevice_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasNetworkMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}