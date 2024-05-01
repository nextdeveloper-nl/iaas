<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasStorageMemberDeviceQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasStorageMemberDeviceService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasStorageMemberDeviceTestTraits
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

    public function test_http_iaasstoragememberdevice_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasstoragememberdevice',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasstoragememberdevice_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasstoragememberdevice', [
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
    public function test_iaasstoragememberdevice_model_get()
    {
        $result = AbstractIaasStorageMemberDeviceService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasstoragememberdevice_get_all()
    {
        $result = AbstractIaasStorageMemberDeviceService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasstoragememberdevice_get_paginated()
    {
        $result = AbstractIaasStorageMemberDeviceService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasstoragememberdevice_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberDevice\IaasStorageMemberDeviceRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberdevice_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberDevice\IaasStorageMemberDeviceCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberdevice_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberDevice\IaasStorageMemberDeviceCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberdevice_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberDevice\IaasStorageMemberDeviceSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberdevice_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberDevice\IaasStorageMemberDeviceSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberdevice_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberDevice\IaasStorageMemberDeviceUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberdevice_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberDevice\IaasStorageMemberDeviceUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberdevice_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberDevice\IaasStorageMemberDeviceDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberdevice_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberDevice\IaasStorageMemberDeviceDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberdevice_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberDevice\IaasStorageMemberDeviceRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberdevice_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberDevice\IaasStorageMemberDeviceRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragememberdevice_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberDevice\IaasStorageMemberDeviceRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberdevice_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberDevice\IaasStorageMemberDeviceCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberdevice_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberDevice\IaasStorageMemberDeviceCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberdevice_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberDevice\IaasStorageMemberDeviceSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberdevice_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberDevice\IaasStorageMemberDeviceSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberdevice_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberDevice\IaasStorageMemberDeviceUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberdevice_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberDevice\IaasStorageMemberDeviceUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberdevice_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberDevice\IaasStorageMemberDeviceDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberdevice_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberDevice\IaasStorageMemberDeviceDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberdevice_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberDevice\IaasStorageMemberDeviceRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberdevice_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberDevice::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberDevice\IaasStorageMemberDeviceRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragememberdevice_event_name_filter()
    {
        try {
            $request = new Request(
                [
                'name'  =>  'a'
                ]
            );

            $filter = new IaasStorageMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragememberdevice_event_device_identification_filter()
    {
        try {
            $request = new Request(
                [
                'device_identification'  =>  'a'
                ]
            );

            $filter = new IaasStorageMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragememberdevice_event_device_type_filter()
    {
        try {
            $request = new Request(
                [
                'device_type'  =>  'a'
                ]
            );

            $filter = new IaasStorageMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragememberdevice_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasStorageMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragememberdevice_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasStorageMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragememberdevice_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasStorageMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragememberdevice_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStorageMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragememberdevice_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStorageMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragememberdevice_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStorageMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragememberdevice_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStorageMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragememberdevice_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStorageMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragememberdevice_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStorageMemberDeviceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberDevice::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}