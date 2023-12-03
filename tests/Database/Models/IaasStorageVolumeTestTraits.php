<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasStorageVolumeQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasStorageVolumeService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasStorageVolumeTestTraits
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

    public function test_http_iaasstoragevolume_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasstoragevolume',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasstoragevolume_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasstoragevolume', [
            'form_params'   =>  [
                'hypervisor_uuid'  =>  'a',
                'name'  =>  'a',
                'disk_physical_type'  =>  'a',
                'total_hdd'  =>  '1',
                'used_hdd'  =>  '1',
                'free_hdd'  =>  '1',
                'virtual_allocation'  =>  '1',
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
    public function test_iaasstoragevolume_model_get()
    {
        $result = AbstractIaasStorageVolumeService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasstoragevolume_get_all()
    {
        $result = AbstractIaasStorageVolumeService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasstoragevolume_get_paginated()
    {
        $result = AbstractIaasStorageVolumeService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasstoragevolume_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageVolume\IaasStorageVolumeRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolume_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageVolume\IaasStorageVolumeCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolume_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageVolume\IaasStorageVolumeCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolume_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageVolume\IaasStorageVolumeSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolume_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageVolume\IaasStorageVolumeSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolume_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageVolume\IaasStorageVolumeUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolume_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageVolume\IaasStorageVolumeUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolume_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageVolume\IaasStorageVolumeDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolume_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageVolume\IaasStorageVolumeDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolume_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageVolume\IaasStorageVolumeRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolume_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageVolume\IaasStorageVolumeRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragevolume_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolume::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageVolume\IaasStorageVolumeRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolume_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolume::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageVolume\IaasStorageVolumeCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolume_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolume::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageVolume\IaasStorageVolumeCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolume_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolume::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageVolume\IaasStorageVolumeSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolume_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolume::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageVolume\IaasStorageVolumeSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolume_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolume::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageVolume\IaasStorageVolumeUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolume_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolume::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageVolume\IaasStorageVolumeUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolume_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolume::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageVolume\IaasStorageVolumeDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolume_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolume::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageVolume\IaasStorageVolumeDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolume_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolume::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageVolume\IaasStorageVolumeRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolume_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolume::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageVolume\IaasStorageVolumeRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragevolume_event_hypervisor_uuid_filter()
    {
        try {
            $request = new Request(
                [
                'hypervisor_uuid'  =>  'a'
                ]
            );

            $filter = new IaasStorageVolumeQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolume::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragevolume_event_name_filter()
    {
        try {
            $request = new Request(
                [
                'name'  =>  'a'
                ]
            );

            $filter = new IaasStorageVolumeQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolume::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragevolume_event_disk_physical_type_filter()
    {
        try {
            $request = new Request(
                [
                'disk_physical_type'  =>  'a'
                ]
            );

            $filter = new IaasStorageVolumeQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolume::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragevolume_event_total_hdd_filter()
    {
        try {
            $request = new Request(
                [
                'total_hdd'  =>  '1'
                ]
            );

            $filter = new IaasStorageVolumeQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolume::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragevolume_event_used_hdd_filter()
    {
        try {
            $request = new Request(
                [
                'used_hdd'  =>  '1'
                ]
            );

            $filter = new IaasStorageVolumeQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolume::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragevolume_event_free_hdd_filter()
    {
        try {
            $request = new Request(
                [
                'free_hdd'  =>  '1'
                ]
            );

            $filter = new IaasStorageVolumeQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolume::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragevolume_event_virtual_allocation_filter()
    {
        try {
            $request = new Request(
                [
                'virtual_allocation'  =>  '1'
                ]
            );

            $filter = new IaasStorageVolumeQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolume::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragevolume_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasStorageVolumeQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolume::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragevolume_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasStorageVolumeQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolume::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragevolume_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasStorageVolumeQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolume::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragevolume_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStorageVolumeQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolume::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragevolume_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStorageVolumeQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolume::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragevolume_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStorageVolumeQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolume::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragevolume_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStorageVolumeQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolume::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragevolume_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStorageVolumeQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolume::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragevolume_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStorageVolumeQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolume::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n
}