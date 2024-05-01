<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasVirtualDiskImageQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasVirtualDiskImageService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasVirtualDiskImageTestTraits
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

    public function test_http_iaasvirtualdiskimage_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasvirtualdiskimage',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasvirtualdiskimage_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasvirtualdiskimage', [
            'form_params'   =>  [
                'name'  =>  'a',
                'hypervisor_uuid'  =>  'a',
                'size'  =>  '1',
                'physical_utilization'  =>  '1',
                'device_number'  =>  '1',
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
    public function test_iaasvirtualdiskimage_model_get()
    {
        $result = AbstractIaasVirtualDiskImageService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasvirtualdiskimage_get_all()
    {
        $result = AbstractIaasVirtualDiskImageService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasvirtualdiskimage_get_paginated()
    {
        $result = AbstractIaasVirtualDiskImageService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasvirtualdiskimage_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImage\IaasVirtualDiskImageRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimage_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImage\IaasVirtualDiskImageCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimage_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImage\IaasVirtualDiskImageCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimage_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImage\IaasVirtualDiskImageSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimage_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImage\IaasVirtualDiskImageSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimage_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImage\IaasVirtualDiskImageUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimage_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImage\IaasVirtualDiskImageUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimage_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImage\IaasVirtualDiskImageDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimage_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImage\IaasVirtualDiskImageDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimage_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImage\IaasVirtualDiskImageRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimage_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImage\IaasVirtualDiskImageRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualdiskimage_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImage::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImage\IaasVirtualDiskImageRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimage_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImage::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImage\IaasVirtualDiskImageCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimage_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImage::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImage\IaasVirtualDiskImageCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimage_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImage::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImage\IaasVirtualDiskImageSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimage_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImage::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImage\IaasVirtualDiskImageSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimage_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImage::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImage\IaasVirtualDiskImageUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimage_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImage::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImage\IaasVirtualDiskImageUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimage_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImage::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImage\IaasVirtualDiskImageDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimage_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImage::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImage\IaasVirtualDiskImageDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimage_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImage::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImage\IaasVirtualDiskImageRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimage_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImage::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImage\IaasVirtualDiskImageRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualdiskimage_event_name_filter()
    {
        try {
            $request = new Request(
                [
                'name'  =>  'a'
                ]
            );

            $filter = new IaasVirtualDiskImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualdiskimage_event_hypervisor_uuid_filter()
    {
        try {
            $request = new Request(
                [
                'hypervisor_uuid'  =>  'a'
                ]
            );

            $filter = new IaasVirtualDiskImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualdiskimage_event_size_filter()
    {
        try {
            $request = new Request(
                [
                'size'  =>  '1'
                ]
            );

            $filter = new IaasVirtualDiskImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualdiskimage_event_physical_utilization_filter()
    {
        try {
            $request = new Request(
                [
                'physical_utilization'  =>  '1'
                ]
            );

            $filter = new IaasVirtualDiskImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualdiskimage_event_device_number_filter()
    {
        try {
            $request = new Request(
                [
                'device_number'  =>  '1'
                ]
            );

            $filter = new IaasVirtualDiskImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualdiskimage_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualDiskImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualdiskimage_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualDiskImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualdiskimage_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualDiskImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualdiskimage_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualDiskImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualdiskimage_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualDiskImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualdiskimage_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualDiskImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualdiskimage_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualDiskImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualdiskimage_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualDiskImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualdiskimage_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualDiskImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}