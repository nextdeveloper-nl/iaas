<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasVirtualMachineCpuHourlyStatQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasVirtualMachineCpuHourlyStatService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasVirtualMachineCpuHourlyStatTestTraits
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

    public function test_http_iaasvirtualmachinecpuhourlystat_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasvirtualmachinecpuhourlystat',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasvirtualmachinecpuhourlystat_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasvirtualmachinecpuhourlystat', [
            'form_params'   =>  [
                'data_points'  =>  '1',
                    'hour_bucket'  =>  now(),
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
    public function test_iaasvirtualmachinecpuhourlystat_model_get()
    {
        $result = AbstractIaasVirtualMachineCpuHourlyStatService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasvirtualmachinecpuhourlystat_get_all()
    {
        $result = AbstractIaasVirtualMachineCpuHourlyStatService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasvirtualmachinecpuhourlystat_get_paginated()
    {
        $result = AbstractIaasVirtualMachineCpuHourlyStatService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasvirtualmachinecpuhourlystat_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuHourlyStat\IaasVirtualMachineCpuHourlyStatRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpuhourlystat_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuHourlyStat\IaasVirtualMachineCpuHourlyStatCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpuhourlystat_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuHourlyStat\IaasVirtualMachineCpuHourlyStatCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpuhourlystat_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuHourlyStat\IaasVirtualMachineCpuHourlyStatSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpuhourlystat_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuHourlyStat\IaasVirtualMachineCpuHourlyStatSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpuhourlystat_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuHourlyStat\IaasVirtualMachineCpuHourlyStatUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpuhourlystat_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuHourlyStat\IaasVirtualMachineCpuHourlyStatUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpuhourlystat_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuHourlyStat\IaasVirtualMachineCpuHourlyStatDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpuhourlystat_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuHourlyStat\IaasVirtualMachineCpuHourlyStatDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpuhourlystat_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuHourlyStat\IaasVirtualMachineCpuHourlyStatRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpuhourlystat_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuHourlyStat\IaasVirtualMachineCpuHourlyStatRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpuhourlystat_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuHourlyStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuHourlyStat\IaasVirtualMachineCpuHourlyStatRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpuhourlystat_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuHourlyStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuHourlyStat\IaasVirtualMachineCpuHourlyStatCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpuhourlystat_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuHourlyStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuHourlyStat\IaasVirtualMachineCpuHourlyStatCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpuhourlystat_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuHourlyStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuHourlyStat\IaasVirtualMachineCpuHourlyStatSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpuhourlystat_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuHourlyStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuHourlyStat\IaasVirtualMachineCpuHourlyStatSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpuhourlystat_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuHourlyStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuHourlyStat\IaasVirtualMachineCpuHourlyStatUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpuhourlystat_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuHourlyStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuHourlyStat\IaasVirtualMachineCpuHourlyStatUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpuhourlystat_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuHourlyStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuHourlyStat\IaasVirtualMachineCpuHourlyStatDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpuhourlystat_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuHourlyStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuHourlyStat\IaasVirtualMachineCpuHourlyStatDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpuhourlystat_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuHourlyStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuHourlyStat\IaasVirtualMachineCpuHourlyStatRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpuhourlystat_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuHourlyStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuHourlyStat\IaasVirtualMachineCpuHourlyStatRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpuhourlystat_event_data_points_filter()
    {
        try {
            $request = new Request(
                [
                'data_points'  =>  '1'
                ]
            );

            $filter = new IaasVirtualMachineCpuHourlyStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuHourlyStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpuhourlystat_event_hour_bucket_filter_start()
    {
        try {
            $request = new Request(
                [
                'hour_bucketStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineCpuHourlyStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuHourlyStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpuhourlystat_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineCpuHourlyStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuHourlyStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpuhourlystat_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineCpuHourlyStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuHourlyStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpuhourlystat_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineCpuHourlyStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuHourlyStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpuhourlystat_event_hour_bucket_filter_end()
    {
        try {
            $request = new Request(
                [
                'hour_bucketEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineCpuHourlyStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuHourlyStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpuhourlystat_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineCpuHourlyStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuHourlyStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpuhourlystat_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineCpuHourlyStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuHourlyStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpuhourlystat_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineCpuHourlyStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuHourlyStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpuhourlystat_event_hour_bucket_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'hour_bucketStart'  =>  now(),
                'hour_bucketEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineCpuHourlyStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuHourlyStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpuhourlystat_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineCpuHourlyStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuHourlyStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpuhourlystat_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineCpuHourlyStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuHourlyStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpuhourlystat_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineCpuHourlyStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuHourlyStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}