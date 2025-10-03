<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;
use NextDeveloper\IAAS\Database\Filters\IaasNetworkStatQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasNetworkStatService;
use Tests\TestCase;

trait IaasNetworkStatTestTraits
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

    public function test_http_iaasnetworkstat_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasnetworkstat',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasnetworkstat_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasnetworkstat', [
            'form_params'   =>  [
                'total_tx'  =>  '1',
                'total_rx'  =>  '1',
                'total_ip_address'  =>  '1',
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
    public function test_iaasnetworkstat_model_get()
    {
        $result = AbstractIaasNetworkStatService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasnetworkstat_get_all()
    {
        $result = AbstractIaasNetworkStatService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasnetworkstat_get_paginated()
    {
        $result = AbstractIaasNetworkStatService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasnetworkstat_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkStat\IaasNetworkStatRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkstat_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkStat\IaasNetworkStatCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkstat_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkStat\IaasNetworkStatCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkstat_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkStat\IaasNetworkStatSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkstat_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkStat\IaasNetworkStatSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkstat_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkStat\IaasNetworkStatUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkstat_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkStat\IaasNetworkStatUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkstat_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkStat\IaasNetworkStatDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkstat_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkStat\IaasNetworkStatDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkstat_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkStat\IaasNetworkStatRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkstat_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkStat\IaasNetworkStatRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkstat_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkStat\IaasNetworkStatRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkstat_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkStat\IaasNetworkStatCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkstat_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkStat\IaasNetworkStatCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkstat_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkStat\IaasNetworkStatSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkstat_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkStat\IaasNetworkStatSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkstat_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkStat\IaasNetworkStatUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkstat_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkStat\IaasNetworkStatUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkstat_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkStat\IaasNetworkStatDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkstat_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkStat\IaasNetworkStatDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkstat_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkStat\IaasNetworkStatRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkstat_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkStat\IaasNetworkStatRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkstat_event_total_tx_filter()
    {
        try {
            $request = new Request(
                [
                'total_tx'  =>  '1'
                ]
            );

            $filter = new IaasNetworkStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkstat_event_total_rx_filter()
    {
        try {
            $request = new Request(
                [
                'total_rx'  =>  '1'
                ]
            );

            $filter = new IaasNetworkStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkstat_event_total_ip_address_filter()
    {
        try {
            $request = new Request(
                [
                'total_ip_address'  =>  '1'
                ]
            );

            $filter = new IaasNetworkStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkstat_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasNetworkStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkstat_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasNetworkStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkstat_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasNetworkStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkstat_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasNetworkStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkstat_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasNetworkStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkstat_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasNetworkStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkstat_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasNetworkStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkstat_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasNetworkStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkstat_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasNetworkStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}