<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasStoragePoolQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasStoragePoolService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasStoragePoolTestTraits
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

    public function test_http_iaasstoragepool_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasstoragepool',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasstoragepool_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasstoragepool', [
            'form_params'   =>  [
                'name'  =>  'a',
                'gb_per_hour_price'  =>  '1',
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
    public function test_iaasstoragepool_model_get()
    {
        $result = AbstractIaasStoragePoolService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasstoragepool_get_all()
    {
        $result = AbstractIaasStoragePoolService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasstoragepool_get_paginated()
    {
        $result = AbstractIaasStoragePoolService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasstoragepool_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStoragePool\IaasStoragePoolRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragepool_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStoragePool\IaasStoragePoolCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragepool_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStoragePool\IaasStoragePoolCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragepool_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStoragePool\IaasStoragePoolSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragepool_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStoragePool\IaasStoragePoolSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragepool_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStoragePool\IaasStoragePoolUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragepool_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStoragePool\IaasStoragePoolUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragepool_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStoragePool\IaasStoragePoolDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragepool_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStoragePool\IaasStoragePoolDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragepool_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStoragePool\IaasStoragePoolRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragepool_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStoragePool\IaasStoragePoolRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragepool_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStoragePool::first();

            event(new \NextDeveloper\IAAS\Events\IaasStoragePool\IaasStoragePoolRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragepool_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStoragePool::first();

            event(new \NextDeveloper\IAAS\Events\IaasStoragePool\IaasStoragePoolCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragepool_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStoragePool::first();

            event(new \NextDeveloper\IAAS\Events\IaasStoragePool\IaasStoragePoolCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragepool_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStoragePool::first();

            event(new \NextDeveloper\IAAS\Events\IaasStoragePool\IaasStoragePoolSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragepool_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStoragePool::first();

            event(new \NextDeveloper\IAAS\Events\IaasStoragePool\IaasStoragePoolSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragepool_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStoragePool::first();

            event(new \NextDeveloper\IAAS\Events\IaasStoragePool\IaasStoragePoolUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragepool_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStoragePool::first();

            event(new \NextDeveloper\IAAS\Events\IaasStoragePool\IaasStoragePoolUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragepool_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStoragePool::first();

            event(new \NextDeveloper\IAAS\Events\IaasStoragePool\IaasStoragePoolDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragepool_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStoragePool::first();

            event(new \NextDeveloper\IAAS\Events\IaasStoragePool\IaasStoragePoolDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragepool_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStoragePool::first();

            event(new \NextDeveloper\IAAS\Events\IaasStoragePool\IaasStoragePoolRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragepool_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStoragePool::first();

            event(new \NextDeveloper\IAAS\Events\IaasStoragePool\IaasStoragePoolRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragepool_event_name_filter()
    {
        try {
            $request = new Request(
                [
                'name'  =>  'a'
                ]
            );

            $filter = new IaasStoragePoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStoragePool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragepool_event_gb_per_hour_price_filter()
    {
        try {
            $request = new Request(
                [
                'gb_per_hour_price'  =>  '1'
                ]
            );

            $filter = new IaasStoragePoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStoragePool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragepool_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasStoragePoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStoragePool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragepool_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasStoragePoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStoragePool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragepool_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasStoragePoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStoragePool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragepool_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStoragePoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStoragePool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragepool_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStoragePoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStoragePool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragepool_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStoragePoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStoragePool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragepool_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStoragePoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStoragePool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragepool_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStoragePoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStoragePool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragepool_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStoragePoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStoragePool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n
}