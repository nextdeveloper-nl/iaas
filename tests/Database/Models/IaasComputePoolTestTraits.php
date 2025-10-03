<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;
use NextDeveloper\IAAS\Database\Filters\IaasComputePoolQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasComputePoolService;
use Tests\TestCase;

trait IaasComputePoolTestTraits
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

    public function test_http_iaascomputepool_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaascomputepool',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaascomputepool_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaascomputepool', [
            'form_params'   =>  [
                'name'  =>  'a',
                'resource_validator'  =>  'a',
                'virtualization'  =>  'a',
                'provisioning_alg'  =>  'a',
                'pool_type'  =>  'a',
                'code_name'  =>  'a',
                'total_cpu'  =>  '1',
                'total_ram'  =>  '1',
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
    public function test_iaascomputepool_model_get()
    {
        $result = AbstractIaasComputePoolService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaascomputepool_get_all()
    {
        $result = AbstractIaasComputePoolService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaascomputepool_get_paginated()
    {
        $result = AbstractIaasComputePoolService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaascomputepool_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputePool\IaasComputePoolRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputepool_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputePool\IaasComputePoolCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputepool_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputePool\IaasComputePoolCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputepool_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputePool\IaasComputePoolSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputepool_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputePool\IaasComputePoolSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputepool_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputePool\IaasComputePoolUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputepool_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputePool\IaasComputePoolUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputepool_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputePool\IaasComputePoolDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputepool_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputePool\IaasComputePoolDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputepool_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputePool\IaasComputePoolRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputepool_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputePool\IaasComputePoolRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputepool_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputePool::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputePool\IaasComputePoolRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputepool_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputePool::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputePool\IaasComputePoolCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputepool_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputePool::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputePool\IaasComputePoolCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputepool_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputePool::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputePool\IaasComputePoolSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputepool_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputePool::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputePool\IaasComputePoolSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputepool_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputePool::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputePool\IaasComputePoolUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputepool_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputePool::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputePool\IaasComputePoolUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputepool_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputePool::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputePool\IaasComputePoolDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputepool_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputePool::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputePool\IaasComputePoolDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputepool_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputePool::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputePool\IaasComputePoolRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputepool_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputePool::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputePool\IaasComputePoolRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputepool_event_name_filter()
    {
        try {
            $request = new Request(
                [
                'name'  =>  'a'
                ]
            );

            $filter = new IaasComputePoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputePool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputepool_event_resource_validator_filter()
    {
        try {
            $request = new Request(
                [
                'resource_validator'  =>  'a'
                ]
            );

            $filter = new IaasComputePoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputePool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputepool_event_virtualization_filter()
    {
        try {
            $request = new Request(
                [
                'virtualization'  =>  'a'
                ]
            );

            $filter = new IaasComputePoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputePool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputepool_event_provisioning_alg_filter()
    {
        try {
            $request = new Request(
                [
                'provisioning_alg'  =>  'a'
                ]
            );

            $filter = new IaasComputePoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputePool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputepool_event_pool_type_filter()
    {
        try {
            $request = new Request(
                [
                'pool_type'  =>  'a'
                ]
            );

            $filter = new IaasComputePoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputePool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputepool_event_code_name_filter()
    {
        try {
            $request = new Request(
                [
                'code_name'  =>  'a'
                ]
            );

            $filter = new IaasComputePoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputePool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputepool_event_total_cpu_filter()
    {
        try {
            $request = new Request(
                [
                'total_cpu'  =>  '1'
                ]
            );

            $filter = new IaasComputePoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputePool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputepool_event_total_ram_filter()
    {
        try {
            $request = new Request(
                [
                'total_ram'  =>  '1'
                ]
            );

            $filter = new IaasComputePoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputePool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputepool_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasComputePoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputePool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputepool_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasComputePoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputePool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputepool_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasComputePoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputePool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputepool_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputePoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputePool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputepool_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputePoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputePool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputepool_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputePoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputePool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputepool_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputePoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputePool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputepool_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputePoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputePool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputepool_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputePoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputePool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE


}