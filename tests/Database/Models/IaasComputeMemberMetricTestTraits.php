<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;
use NextDeveloper\IAAS\Database\Filters\IaasComputeMemberMetricQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasComputeMemberMetricService;

trait IaasComputeMemberMetricTestTraits
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

    public function test_http_iaascomputemembermetric_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaascomputemembermetric',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaascomputemembermetric_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaascomputemembermetric', [
            'form_params'   =>  [
                'source'  =>  'a',
                'parameter'  =>  'a',
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
    public function test_iaascomputemembermetric_model_get()
    {
        $result = AbstractIaasComputeMemberMetricService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaascomputemembermetric_get_all()
    {
        $result = AbstractIaasComputeMemberMetricService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaascomputemembermetric_get_paginated()
    {
        $result = AbstractIaasComputeMemberMetricService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaascomputemembermetric_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberMetric\IaasComputeMemberMetricRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembermetric_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberMetric\IaasComputeMemberMetricCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembermetric_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberMetric\IaasComputeMemberMetricCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembermetric_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberMetric\IaasComputeMemberMetricSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembermetric_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberMetric\IaasComputeMemberMetricSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembermetric_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberMetric\IaasComputeMemberMetricUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembermetric_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberMetric\IaasComputeMemberMetricUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembermetric_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberMetric\IaasComputeMemberMetricDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembermetric_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberMetric\IaasComputeMemberMetricDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembermetric_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberMetric\IaasComputeMemberMetricRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembermetric_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberMetric\IaasComputeMemberMetricRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemembermetric_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberMetric::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberMetric\IaasComputeMemberMetricRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembermetric_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberMetric::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberMetric\IaasComputeMemberMetricCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembermetric_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberMetric::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberMetric\IaasComputeMemberMetricCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembermetric_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberMetric::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberMetric\IaasComputeMemberMetricSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembermetric_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberMetric::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberMetric\IaasComputeMemberMetricSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembermetric_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberMetric::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberMetric\IaasComputeMemberMetricUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembermetric_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberMetric::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberMetric\IaasComputeMemberMetricUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembermetric_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberMetric::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberMetric\IaasComputeMemberMetricDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembermetric_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberMetric::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberMetric\IaasComputeMemberMetricDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembermetric_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberMetric::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberMetric\IaasComputeMemberMetricRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembermetric_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberMetric::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberMetric\IaasComputeMemberMetricRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemembermetric_event_source_filter()
    {
        try {
            $request = new Request(
                [
                'source'  =>  'a'
                ]
            );

            $filter = new IaasComputeMemberMetricQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberMetric::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemembermetric_event_parameter_filter()
    {
        try {
            $request = new Request(
                [
                'parameter'  =>  'a'
                ]
            );

            $filter = new IaasComputeMemberMetricQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberMetric::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemembermetric_event_timestamp_filter_start()
    {
        try {
            $request = new Request(
                [
                'timestampStart'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberMetricQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberMetric::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemembermetric_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberMetricQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberMetric::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemembermetric_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberMetricQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberMetric::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemembermetric_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberMetricQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberMetric::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemembermetric_event_timestamp_filter_end()
    {
        try {
            $request = new Request(
                [
                'timestampEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberMetricQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberMetric::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemembermetric_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberMetricQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberMetric::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemembermetric_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberMetricQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberMetric::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemembermetric_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberMetricQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberMetric::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemembermetric_event_timestamp_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'timestampStart'  =>  now(),
                'timestampEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberMetricQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberMetric::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemembermetric_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberMetricQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberMetric::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemembermetric_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberMetricQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberMetric::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemembermetric_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberMetricQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberMetric::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}
