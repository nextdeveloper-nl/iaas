<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasVirtualDiskImageStatQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasVirtualDiskImageStatService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasVirtualDiskImageStatTestTraits
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

    public function test_http_iaasvirtualdiskimagestat_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasvirtualdiskimagestat',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasvirtualdiskimagestat_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasvirtualdiskimagestat', [
            'form_params'   =>  [
                'size'  =>  '1',
                'physical_utilisation'  =>  '1',
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
    public function test_iaasvirtualdiskimagestat_model_get()
    {
        $result = AbstractIaasVirtualDiskImageStatService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasvirtualdiskimagestat_get_all()
    {
        $result = AbstractIaasVirtualDiskImageStatService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasvirtualdiskimagestat_get_paginated()
    {
        $result = AbstractIaasVirtualDiskImageStatService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasvirtualdiskimagestat_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImageStat\IaasVirtualDiskImageStatRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimagestat_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImageStat\IaasVirtualDiskImageStatCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimagestat_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImageStat\IaasVirtualDiskImageStatCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimagestat_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImageStat\IaasVirtualDiskImageStatSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimagestat_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImageStat\IaasVirtualDiskImageStatSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimagestat_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImageStat\IaasVirtualDiskImageStatUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimagestat_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImageStat\IaasVirtualDiskImageStatUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimagestat_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImageStat\IaasVirtualDiskImageStatDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimagestat_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImageStat\IaasVirtualDiskImageStatDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimagestat_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImageStat\IaasVirtualDiskImageStatRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimagestat_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImageStat\IaasVirtualDiskImageStatRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualdiskimagestat_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImageStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImageStat\IaasVirtualDiskImageStatRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimagestat_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImageStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImageStat\IaasVirtualDiskImageStatCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimagestat_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImageStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImageStat\IaasVirtualDiskImageStatCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimagestat_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImageStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImageStat\IaasVirtualDiskImageStatSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimagestat_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImageStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImageStat\IaasVirtualDiskImageStatSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimagestat_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImageStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImageStat\IaasVirtualDiskImageStatUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimagestat_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImageStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImageStat\IaasVirtualDiskImageStatUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimagestat_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImageStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImageStat\IaasVirtualDiskImageStatDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimagestat_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImageStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImageStat\IaasVirtualDiskImageStatDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimagestat_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImageStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImageStat\IaasVirtualDiskImageStatRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualdiskimagestat_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImageStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualDiskImageStat\IaasVirtualDiskImageStatRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualdiskimagestat_event_size_filter()
    {
        try {
            $request = new Request(
                [
                'size'  =>  '1'
                ]
            );

            $filter = new IaasVirtualDiskImageStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImageStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualdiskimagestat_event_physical_utilisation_filter()
    {
        try {
            $request = new Request(
                [
                'physical_utilisation'  =>  '1'
                ]
            );

            $filter = new IaasVirtualDiskImageStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImageStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualdiskimagestat_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualDiskImageStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImageStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualdiskimagestat_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualDiskImageStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImageStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualdiskimagestat_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualDiskImageStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImageStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualdiskimagestat_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualDiskImageStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImageStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualdiskimagestat_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualDiskImageStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImageStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualdiskimagestat_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualDiskImageStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImageStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualdiskimagestat_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualDiskImageStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImageStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualdiskimagestat_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualDiskImageStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImageStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualdiskimagestat_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualDiskImageStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualDiskImageStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}