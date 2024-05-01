<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasIpAddressHistoryQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasIpAddressHistoryService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasIpAddressHistoryTestTraits
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

    public function test_http_iaasipaddresshistory_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasipaddresshistory',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasipaddresshistory_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasipaddresshistory', [
            'form_params'   =>  [
                'body'  =>  'a',
                'hash'  =>  'a',
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
    public function test_iaasipaddresshistory_model_get()
    {
        $result = AbstractIaasIpAddressHistoryService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasipaddresshistory_get_all()
    {
        $result = AbstractIaasIpAddressHistoryService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasipaddresshistory_get_paginated()
    {
        $result = AbstractIaasIpAddressHistoryService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasipaddresshistory_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasIpAddressHistory\IaasIpAddressHistoryRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddresshistory_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasIpAddressHistory\IaasIpAddressHistoryCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddresshistory_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasIpAddressHistory\IaasIpAddressHistoryCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddresshistory_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasIpAddressHistory\IaasIpAddressHistorySavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddresshistory_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasIpAddressHistory\IaasIpAddressHistorySavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddresshistory_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasIpAddressHistory\IaasIpAddressHistoryUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddresshistory_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasIpAddressHistory\IaasIpAddressHistoryUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddresshistory_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasIpAddressHistory\IaasIpAddressHistoryDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddresshistory_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasIpAddressHistory\IaasIpAddressHistoryDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddresshistory_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasIpAddressHistory\IaasIpAddressHistoryRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddresshistory_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasIpAddressHistory\IaasIpAddressHistoryRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasipaddresshistory_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddressHistory::first();

            event(new \NextDeveloper\IAAS\Events\IaasIpAddressHistory\IaasIpAddressHistoryRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddresshistory_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddressHistory::first();

            event(new \NextDeveloper\IAAS\Events\IaasIpAddressHistory\IaasIpAddressHistoryCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddresshistory_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddressHistory::first();

            event(new \NextDeveloper\IAAS\Events\IaasIpAddressHistory\IaasIpAddressHistoryCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddresshistory_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddressHistory::first();

            event(new \NextDeveloper\IAAS\Events\IaasIpAddressHistory\IaasIpAddressHistorySavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddresshistory_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddressHistory::first();

            event(new \NextDeveloper\IAAS\Events\IaasIpAddressHistory\IaasIpAddressHistorySavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddresshistory_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddressHistory::first();

            event(new \NextDeveloper\IAAS\Events\IaasIpAddressHistory\IaasIpAddressHistoryUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddresshistory_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddressHistory::first();

            event(new \NextDeveloper\IAAS\Events\IaasIpAddressHistory\IaasIpAddressHistoryUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddresshistory_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddressHistory::first();

            event(new \NextDeveloper\IAAS\Events\IaasIpAddressHistory\IaasIpAddressHistoryDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddresshistory_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddressHistory::first();

            event(new \NextDeveloper\IAAS\Events\IaasIpAddressHistory\IaasIpAddressHistoryDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddresshistory_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddressHistory::first();

            event(new \NextDeveloper\IAAS\Events\IaasIpAddressHistory\IaasIpAddressHistoryRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasipaddresshistory_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddressHistory::first();

            event(new \NextDeveloper\IAAS\Events\IaasIpAddressHistory\IaasIpAddressHistoryRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasipaddresshistory_event_body_filter()
    {
        try {
            $request = new Request(
                [
                'body'  =>  'a'
                ]
            );

            $filter = new IaasIpAddressHistoryQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddressHistory::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasipaddresshistory_event_hash_filter()
    {
        try {
            $request = new Request(
                [
                'hash'  =>  'a'
                ]
            );

            $filter = new IaasIpAddressHistoryQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddressHistory::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasipaddresshistory_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasIpAddressHistoryQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddressHistory::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasipaddresshistory_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasIpAddressHistoryQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddressHistory::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasipaddresshistory_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasIpAddressHistoryQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddressHistory::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasipaddresshistory_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasIpAddressHistoryQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddressHistory::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasipaddresshistory_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasIpAddressHistoryQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddressHistory::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasipaddresshistory_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasIpAddressHistoryQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddressHistory::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasipaddresshistory_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasIpAddressHistoryQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddressHistory::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasipaddresshistory_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasIpAddressHistoryQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddressHistory::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasipaddresshistory_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasIpAddressHistoryQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasIpAddressHistory::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}