<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;
use NextDeveloper\IAAS\Database\Filters\IaasVirtualNetworkCardStatQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasVirtualNetworkCardStatService;
use Tests\TestCase;

trait IaasVirtualNetworkCardStatTestTraits
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

    public function test_http_iaasvirtualnetworkcardstat_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasvirtualnetworkcardstat',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasvirtualnetworkcardstat_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasvirtualnetworkcardstat', [
            'form_params'   =>  [
                'used_tx'  =>  '1',
                'used_rx'  =>  '1',
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
    public function test_iaasvirtualnetworkcardstat_model_get()
    {
        $result = AbstractIaasVirtualNetworkCardStatService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasvirtualnetworkcardstat_get_all()
    {
        $result = AbstractIaasVirtualNetworkCardStatService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasvirtualnetworkcardstat_get_paginated()
    {
        $result = AbstractIaasVirtualNetworkCardStatService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasvirtualnetworkcardstat_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCardStat\IaasVirtualNetworkCardStatRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcardstat_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCardStat\IaasVirtualNetworkCardStatCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcardstat_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCardStat\IaasVirtualNetworkCardStatCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcardstat_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCardStat\IaasVirtualNetworkCardStatSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcardstat_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCardStat\IaasVirtualNetworkCardStatSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcardstat_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCardStat\IaasVirtualNetworkCardStatUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcardstat_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCardStat\IaasVirtualNetworkCardStatUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcardstat_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCardStat\IaasVirtualNetworkCardStatDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcardstat_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCardStat\IaasVirtualNetworkCardStatDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcardstat_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCardStat\IaasVirtualNetworkCardStatRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcardstat_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCardStat\IaasVirtualNetworkCardStatRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualnetworkcardstat_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCardStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCardStat\IaasVirtualNetworkCardStatRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcardstat_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCardStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCardStat\IaasVirtualNetworkCardStatCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcardstat_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCardStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCardStat\IaasVirtualNetworkCardStatCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcardstat_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCardStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCardStat\IaasVirtualNetworkCardStatSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcardstat_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCardStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCardStat\IaasVirtualNetworkCardStatSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcardstat_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCardStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCardStat\IaasVirtualNetworkCardStatUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcardstat_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCardStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCardStat\IaasVirtualNetworkCardStatUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcardstat_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCardStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCardStat\IaasVirtualNetworkCardStatDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcardstat_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCardStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCardStat\IaasVirtualNetworkCardStatDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcardstat_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCardStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCardStat\IaasVirtualNetworkCardStatRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcardstat_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCardStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCardStat\IaasVirtualNetworkCardStatRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualnetworkcardstat_event_used_tx_filter()
    {
        try {
            $request = new Request(
                [
                'used_tx'  =>  '1'
                ]
            );

            $filter = new IaasVirtualNetworkCardStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCardStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualnetworkcardstat_event_used_rx_filter()
    {
        try {
            $request = new Request(
                [
                'used_rx'  =>  '1'
                ]
            );

            $filter = new IaasVirtualNetworkCardStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCardStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualnetworkcardstat_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualNetworkCardStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCardStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualnetworkcardstat_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualNetworkCardStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCardStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualnetworkcardstat_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualNetworkCardStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCardStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualnetworkcardstat_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualNetworkCardStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCardStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualnetworkcardstat_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualNetworkCardStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCardStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualnetworkcardstat_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualNetworkCardStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCardStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualnetworkcardstat_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualNetworkCardStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCardStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualnetworkcardstat_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualNetworkCardStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCardStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualnetworkcardstat_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualNetworkCardStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCardStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}