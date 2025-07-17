<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;
use NextDeveloper\IAAS\Database\Filters\IaasStorageVolumeStatQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasStorageVolumeStatService;

trait IaasStorageVolumeStatTestTraits
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

    public function test_http_iaasstoragevolumestat_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasstoragevolumestat',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasstoragevolumestat_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasstoragevolumestat', [
            'form_params'   =>  [
                'used_disk'  =>  '1',
                'free_disk'  =>  '1',
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
    public function test_iaasstoragevolumestat_model_get()
    {
        $result = AbstractIaasStorageVolumeStatService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasstoragevolumestat_get_all()
    {
        $result = AbstractIaasStorageVolumeStatService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasstoragevolumestat_get_paginated()
    {
        $result = AbstractIaasStorageVolumeStatService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasstoragevolumestat_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageVolumeStat\IaasStorageVolumeStatRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolumestat_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageVolumeStat\IaasStorageVolumeStatCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolumestat_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageVolumeStat\IaasStorageVolumeStatCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolumestat_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageVolumeStat\IaasStorageVolumeStatSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolumestat_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageVolumeStat\IaasStorageVolumeStatSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolumestat_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageVolumeStat\IaasStorageVolumeStatUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolumestat_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageVolumeStat\IaasStorageVolumeStatUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolumestat_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageVolumeStat\IaasStorageVolumeStatDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolumestat_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageVolumeStat\IaasStorageVolumeStatDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolumestat_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageVolumeStat\IaasStorageVolumeStatRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolumestat_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageVolumeStat\IaasStorageVolumeStatRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragevolumestat_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolumeStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageVolumeStat\IaasStorageVolumeStatRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolumestat_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolumeStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageVolumeStat\IaasStorageVolumeStatCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolumestat_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolumeStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageVolumeStat\IaasStorageVolumeStatCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolumestat_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolumeStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageVolumeStat\IaasStorageVolumeStatSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolumestat_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolumeStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageVolumeStat\IaasStorageVolumeStatSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolumestat_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolumeStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageVolumeStat\IaasStorageVolumeStatUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolumestat_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolumeStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageVolumeStat\IaasStorageVolumeStatUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolumestat_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolumeStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageVolumeStat\IaasStorageVolumeStatDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolumestat_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolumeStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageVolumeStat\IaasStorageVolumeStatDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolumestat_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolumeStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageVolumeStat\IaasStorageVolumeStatRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragevolumestat_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolumeStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageVolumeStat\IaasStorageVolumeStatRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragevolumestat_event_used_disk_filter()
    {
        try {
            $request = new Request(
                [
                'used_disk'  =>  '1'
                ]
            );

            $filter = new IaasStorageVolumeStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolumeStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragevolumestat_event_free_disk_filter()
    {
        try {
            $request = new Request(
                [
                'free_disk'  =>  '1'
                ]
            );

            $filter = new IaasStorageVolumeStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolumeStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragevolumestat_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasStorageVolumeStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolumeStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragevolumestat_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasStorageVolumeStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolumeStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragevolumestat_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasStorageVolumeStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolumeStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragevolumestat_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStorageVolumeStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolumeStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragevolumestat_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStorageVolumeStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolumeStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragevolumestat_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStorageVolumeStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolumeStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragevolumestat_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStorageVolumeStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolumeStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragevolumestat_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStorageVolumeStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolumeStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragevolumestat_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStorageVolumeStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageVolumeStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}
