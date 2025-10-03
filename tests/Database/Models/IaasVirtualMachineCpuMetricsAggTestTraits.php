<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasVirtualMachineCpuMetricsAggQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasVirtualMachineCpuMetricsAggService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasVirtualMachineCpuMetricsAggTestTraits
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

    public function test_http_iaasvirtualmachinecpumetricsagg_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasvirtualmachinecpumetricsagg',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasvirtualmachinecpumetricsagg_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasvirtualmachinecpumetricsagg', [
            'form_params'   =>  [
                    'timestamp'  =>  now(),
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
    public function test_iaasvirtualmachinecpumetricsagg_model_get()
    {
        $result = AbstractIaasVirtualMachineCpuMetricsAggService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasvirtualmachinecpumetricsagg_get_all()
    {
        $result = AbstractIaasVirtualMachineCpuMetricsAggService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasvirtualmachinecpumetricsagg_get_paginated()
    {
        $result = AbstractIaasVirtualMachineCpuMetricsAggService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasvirtualmachinecpumetricsagg_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuMetricsAgg\IaasVirtualMachineCpuMetricsAggRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpumetricsagg_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuMetricsAgg\IaasVirtualMachineCpuMetricsAggCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpumetricsagg_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuMetricsAgg\IaasVirtualMachineCpuMetricsAggCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpumetricsagg_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuMetricsAgg\IaasVirtualMachineCpuMetricsAggSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpumetricsagg_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuMetricsAgg\IaasVirtualMachineCpuMetricsAggSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpumetricsagg_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuMetricsAgg\IaasVirtualMachineCpuMetricsAggUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpumetricsagg_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuMetricsAgg\IaasVirtualMachineCpuMetricsAggUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpumetricsagg_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuMetricsAgg\IaasVirtualMachineCpuMetricsAggDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpumetricsagg_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuMetricsAgg\IaasVirtualMachineCpuMetricsAggDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpumetricsagg_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuMetricsAgg\IaasVirtualMachineCpuMetricsAggRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpumetricsagg_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuMetricsAgg\IaasVirtualMachineCpuMetricsAggRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpumetricsagg_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuMetricsAgg::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuMetricsAgg\IaasVirtualMachineCpuMetricsAggRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpumetricsagg_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuMetricsAgg::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuMetricsAgg\IaasVirtualMachineCpuMetricsAggCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpumetricsagg_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuMetricsAgg::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuMetricsAgg\IaasVirtualMachineCpuMetricsAggCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpumetricsagg_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuMetricsAgg::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuMetricsAgg\IaasVirtualMachineCpuMetricsAggSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpumetricsagg_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuMetricsAgg::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuMetricsAgg\IaasVirtualMachineCpuMetricsAggSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpumetricsagg_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuMetricsAgg::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuMetricsAgg\IaasVirtualMachineCpuMetricsAggUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpumetricsagg_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuMetricsAgg::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuMetricsAgg\IaasVirtualMachineCpuMetricsAggUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpumetricsagg_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuMetricsAgg::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuMetricsAgg\IaasVirtualMachineCpuMetricsAggDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpumetricsagg_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuMetricsAgg::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuMetricsAgg\IaasVirtualMachineCpuMetricsAggDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpumetricsagg_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuMetricsAgg::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuMetricsAgg\IaasVirtualMachineCpuMetricsAggRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpumetricsagg_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuMetricsAgg::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuMetricsAgg\IaasVirtualMachineCpuMetricsAggRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpumetricsagg_event_timestamp_filter_start()
    {
        try {
            $request = new Request(
                [
                'timestampStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineCpuMetricsAggQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuMetricsAgg::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpumetricsagg_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineCpuMetricsAggQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuMetricsAgg::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpumetricsagg_event_timestamp_filter_end()
    {
        try {
            $request = new Request(
                [
                'timestampEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineCpuMetricsAggQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuMetricsAgg::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpumetricsagg_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineCpuMetricsAggQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuMetricsAgg::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpumetricsagg_event_timestamp_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'timestampStart'  =>  now(),
                'timestampEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineCpuMetricsAggQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuMetricsAgg::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpumetricsagg_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineCpuMetricsAggQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuMetricsAgg::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}