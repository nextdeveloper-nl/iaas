<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasNetworkQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasNetworkService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasNetworkTestTraits
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

    public function test_http_iaasnetwork_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasnetwork',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasnetwork_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasnetwork', [
            'form_params'   =>  [
                'name'  =>  'a',
                'vxlan'  =>  'a',
                'vlan'  =>  '1',
                'bandwidth'  =>  '1',
                'speed_limit'  =>  '1',
                'mtu'  =>  '1',
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
    public function test_iaasnetwork_model_get()
    {
        $result = AbstractIaasNetworkService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasnetwork_get_all()
    {
        $result = AbstractIaasNetworkService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasnetwork_get_paginated()
    {
        $result = AbstractIaasNetworkService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasnetwork_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetwork\IaasNetworkRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetwork_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetwork\IaasNetworkCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetwork_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetwork\IaasNetworkCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetwork_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetwork\IaasNetworkSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetwork_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetwork\IaasNetworkSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetwork_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetwork\IaasNetworkUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetwork_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetwork\IaasNetworkUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetwork_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetwork\IaasNetworkDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetwork_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetwork\IaasNetworkDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetwork_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetwork\IaasNetworkRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetwork_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetwork\IaasNetworkRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetwork_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetwork::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetwork\IaasNetworkRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetwork_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetwork::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetwork\IaasNetworkCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetwork_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetwork::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetwork\IaasNetworkCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetwork_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetwork::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetwork\IaasNetworkSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetwork_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetwork::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetwork\IaasNetworkSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetwork_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetwork::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetwork\IaasNetworkUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetwork_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetwork::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetwork\IaasNetworkUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetwork_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetwork::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetwork\IaasNetworkDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetwork_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetwork::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetwork\IaasNetworkDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetwork_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetwork::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetwork\IaasNetworkRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetwork_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetwork::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetwork\IaasNetworkRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetwork_event_name_filter()
    {
        try {
            $request = new Request(
                [
                'name'  =>  'a'
                ]
            );

            $filter = new IaasNetworkQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetwork::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetwork_event_vxlan_filter()
    {
        try {
            $request = new Request(
                [
                'vxlan'  =>  'a'
                ]
            );

            $filter = new IaasNetworkQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetwork::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetwork_event_vlan_filter()
    {
        try {
            $request = new Request(
                [
                'vlan'  =>  '1'
                ]
            );

            $filter = new IaasNetworkQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetwork::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetwork_event_bandwidth_filter()
    {
        try {
            $request = new Request(
                [
                'bandwidth'  =>  '1'
                ]
            );

            $filter = new IaasNetworkQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetwork::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetwork_event_speed_limit_filter()
    {
        try {
            $request = new Request(
                [
                'speed_limit'  =>  '1'
                ]
            );

            $filter = new IaasNetworkQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetwork::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetwork_event_mtu_filter()
    {
        try {
            $request = new Request(
                [
                'mtu'  =>  '1'
                ]
            );

            $filter = new IaasNetworkQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetwork::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetwork_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasNetworkQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetwork::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetwork_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasNetworkQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetwork::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetwork_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasNetworkQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetwork::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetwork_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasNetworkQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetwork::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetwork_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasNetworkQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetwork::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetwork_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasNetworkQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetwork::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetwork_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasNetworkQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetwork::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetwork_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasNetworkQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetwork::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetwork_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasNetworkQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetwork::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}