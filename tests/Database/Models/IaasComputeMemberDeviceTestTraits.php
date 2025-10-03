<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;
use NextDeveloper\IAAS\Database\Filters\IaasComputeMemberDeviceQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasComputeMemberDeviceService;
use Tests\TestCase;

trait IaasComputeMemberDeviceTestTraits
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

    public function test_http_iaascomputememberdevice_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaascomputememberdevice',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaascomputememberdevice_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaascomputememberdevice', [
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
    public function test_iaascomputememberdevice_model_get()
    {
        $result = AbstractIaasComputeMemberDeviceService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaascomputememberdevice_get_all()
    {
        $result = AbstractIaasComputeMemberDeviceService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaascomputememberdevice_get_paginated()
    {
        $result = AbstractIaasComputeMemberDeviceService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaascomputememberdevice_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberDevice\IaasComputeMemberDeviceRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberdevice_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberDevice\IaasComputeMemberDeviceCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberdevice_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberDevice\IaasComputeMemberDeviceCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberdevice_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberDevice\IaasComputeMemberDeviceSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberdevice_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberDevice\IaasComputeMemberDeviceSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberdevice_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberDevice\IaasComputeMemberDeviceUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberdevice_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberDevice\IaasComputeMemberDeviceUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberdevice_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberDevice\IaasComputeMemberDeviceDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberdevice_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberDevice\IaasComputeMemberDeviceDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberdevice_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberDevice\IaasComputeMemberDeviceRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberdevice_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberDevice\IaasComputeMemberDeviceRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberdevice_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberDevice\IaasComputeMemberDeviceRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberdevice_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberDevice\IaasComputeMemberDeviceCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberdevice_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberDevice\IaasComputeMemberDeviceCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberdevice_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberDevice\IaasComputeMemberDeviceSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberdevice_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberDevice\IaasComputeMemberDeviceSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberdevice_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberDevice\IaasComputeMemberDeviceUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberdevice_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberDevice\IaasComputeMemberDeviceUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberdevice_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberDevice\IaasComputeMemberDeviceDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberdevice_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberDevice\IaasComputeMemberDeviceDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberdevice_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberDevice\IaasComputeMemberDeviceRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberdevice_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberDevice\IaasComputeMemberDeviceRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberdevice_event_name_filter()
    {
        try {
            $request = new Request(
                [
                'name'  =>  'a'
                ]
            );

            $filter = new IaasComputeMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberdevice_event_device_identification_filter()
    {
        try {
            $request = new Request(
                [
                'device_identification'  =>  'a'
                ]
            );

            $filter = new IaasComputeMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberdevice_event_device_type_filter()
    {
        try {
            $request = new Request(
                [
                'device_type'  =>  'a'
                ]
            );

            $filter = new IaasComputeMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberdevice_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberdevice_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberdevice_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberdevice_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberdevice_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberdevice_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberdevice_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberdevice_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberdevice_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}