<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasNetworkPoolStatQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasNetworkPoolStatService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasNetworkPoolStatTestTraits
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

    public function test_http_iaasnetworkpoolstat_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasnetworkpoolstat',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasnetworkpoolstat_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasnetworkpoolstat', [
            'form_params'   =>  [
                'used_vlan'  =>  '1',
                'used_vxlan'  =>  '1',
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
    public function test_iaasnetworkpoolstat_model_get()
    {
        $result = AbstractIaasNetworkPoolStatService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasnetworkpoolstat_get_all()
    {
        $result = AbstractIaasNetworkPoolStatService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasnetworkpoolstat_get_paginated()
    {
        $result = AbstractIaasNetworkPoolStatService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasnetworkpoolstat_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkPoolStat\IaasNetworkPoolStatRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpoolstat_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkPoolStat\IaasNetworkPoolStatCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpoolstat_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkPoolStat\IaasNetworkPoolStatCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpoolstat_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkPoolStat\IaasNetworkPoolStatSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpoolstat_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkPoolStat\IaasNetworkPoolStatSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpoolstat_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkPoolStat\IaasNetworkPoolStatUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpoolstat_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkPoolStat\IaasNetworkPoolStatUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpoolstat_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkPoolStat\IaasNetworkPoolStatDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpoolstat_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkPoolStat\IaasNetworkPoolStatDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpoolstat_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkPoolStat\IaasNetworkPoolStatRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpoolstat_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkPoolStat\IaasNetworkPoolStatRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkpoolstat_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPoolStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkPoolStat\IaasNetworkPoolStatRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpoolstat_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPoolStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkPoolStat\IaasNetworkPoolStatCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpoolstat_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPoolStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkPoolStat\IaasNetworkPoolStatCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpoolstat_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPoolStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkPoolStat\IaasNetworkPoolStatSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpoolstat_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPoolStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkPoolStat\IaasNetworkPoolStatSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpoolstat_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPoolStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkPoolStat\IaasNetworkPoolStatUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpoolstat_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPoolStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkPoolStat\IaasNetworkPoolStatUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpoolstat_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPoolStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkPoolStat\IaasNetworkPoolStatDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpoolstat_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPoolStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkPoolStat\IaasNetworkPoolStatDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpoolstat_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPoolStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkPoolStat\IaasNetworkPoolStatRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpoolstat_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPoolStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkPoolStat\IaasNetworkPoolStatRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkpoolstat_event_used_vlan_filter()
    {
        try {
            $request = new Request(
                [
                'used_vlan'  =>  '1'
                ]
            );

            $filter = new IaasNetworkPoolStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPoolStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkpoolstat_event_used_vxlan_filter()
    {
        try {
            $request = new Request(
                [
                'used_vxlan'  =>  '1'
                ]
            );

            $filter = new IaasNetworkPoolStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPoolStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkpoolstat_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasNetworkPoolStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPoolStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkpoolstat_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasNetworkPoolStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPoolStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkpoolstat_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasNetworkPoolStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPoolStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkpoolstat_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasNetworkPoolStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPoolStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkpoolstat_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasNetworkPoolStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPoolStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkpoolstat_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasNetworkPoolStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPoolStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkpoolstat_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasNetworkPoolStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPoolStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkpoolstat_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasNetworkPoolStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPoolStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkpoolstat_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasNetworkPoolStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPoolStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}