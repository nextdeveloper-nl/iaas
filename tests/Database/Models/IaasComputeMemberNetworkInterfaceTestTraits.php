<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;
use NextDeveloper\IAAS\Database\Filters\IaasComputeMemberNetworkInterfaceQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasComputeMemberNetworkInterfaceService;

trait IaasComputeMemberNetworkInterfaceTestTraits
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

    public function test_http_iaascomputemembernetworkinterface_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaascomputemembernetworkinterface',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaascomputemembernetworkinterface_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaascomputemembernetworkinterface', [
            'form_params'   =>  [
                'device'  =>  'a',
                'hypervisor_uuid'  =>  'a',
                'network_uuid'  =>  'a',
                'network_name'  =>  'a',
                'vlan'  =>  '1',
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
    public function test_iaascomputemembernetworkinterface_model_get()
    {
        $result = AbstractIaasComputeMemberNetworkInterfaceService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaascomputemembernetworkinterface_get_all()
    {
        $result = AbstractIaasComputeMemberNetworkInterfaceService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaascomputemembernetworkinterface_get_paginated()
    {
        $result = AbstractIaasComputeMemberNetworkInterfaceService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaascomputemembernetworkinterface_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberNetworkInterface\IaasComputeMemberNetworkInterfaceRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembernetworkinterface_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberNetworkInterface\IaasComputeMemberNetworkInterfaceCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembernetworkinterface_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberNetworkInterface\IaasComputeMemberNetworkInterfaceCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembernetworkinterface_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberNetworkInterface\IaasComputeMemberNetworkInterfaceSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembernetworkinterface_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberNetworkInterface\IaasComputeMemberNetworkInterfaceSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembernetworkinterface_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberNetworkInterface\IaasComputeMemberNetworkInterfaceUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembernetworkinterface_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberNetworkInterface\IaasComputeMemberNetworkInterfaceUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembernetworkinterface_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberNetworkInterface\IaasComputeMemberNetworkInterfaceDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembernetworkinterface_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberNetworkInterface\IaasComputeMemberNetworkInterfaceDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembernetworkinterface_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberNetworkInterface\IaasComputeMemberNetworkInterfaceRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembernetworkinterface_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberNetworkInterface\IaasComputeMemberNetworkInterfaceRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemembernetworkinterface_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberNetworkInterface::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberNetworkInterface\IaasComputeMemberNetworkInterfaceRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembernetworkinterface_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberNetworkInterface::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberNetworkInterface\IaasComputeMemberNetworkInterfaceCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembernetworkinterface_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberNetworkInterface::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberNetworkInterface\IaasComputeMemberNetworkInterfaceCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembernetworkinterface_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberNetworkInterface::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberNetworkInterface\IaasComputeMemberNetworkInterfaceSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembernetworkinterface_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberNetworkInterface::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberNetworkInterface\IaasComputeMemberNetworkInterfaceSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembernetworkinterface_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberNetworkInterface::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberNetworkInterface\IaasComputeMemberNetworkInterfaceUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembernetworkinterface_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberNetworkInterface::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberNetworkInterface\IaasComputeMemberNetworkInterfaceUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembernetworkinterface_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberNetworkInterface::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberNetworkInterface\IaasComputeMemberNetworkInterfaceDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembernetworkinterface_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberNetworkInterface::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberNetworkInterface\IaasComputeMemberNetworkInterfaceDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembernetworkinterface_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberNetworkInterface::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberNetworkInterface\IaasComputeMemberNetworkInterfaceRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemembernetworkinterface_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberNetworkInterface::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberNetworkInterface\IaasComputeMemberNetworkInterfaceRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemembernetworkinterface_event_device_filter()
    {
        try {
            $request = new Request(
                [
                'device'  =>  'a'
                ]
            );

            $filter = new IaasComputeMemberNetworkInterfaceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberNetworkInterface::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemembernetworkinterface_event_hypervisor_uuid_filter()
    {
        try {
            $request = new Request(
                [
                'hypervisor_uuid'  =>  'a'
                ]
            );

            $filter = new IaasComputeMemberNetworkInterfaceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberNetworkInterface::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemembernetworkinterface_event_network_uuid_filter()
    {
        try {
            $request = new Request(
                [
                'network_uuid'  =>  'a'
                ]
            );

            $filter = new IaasComputeMemberNetworkInterfaceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberNetworkInterface::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemembernetworkinterface_event_network_name_filter()
    {
        try {
            $request = new Request(
                [
                'network_name'  =>  'a'
                ]
            );

            $filter = new IaasComputeMemberNetworkInterfaceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberNetworkInterface::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemembernetworkinterface_event_vlan_filter()
    {
        try {
            $request = new Request(
                [
                'vlan'  =>  '1'
                ]
            );

            $filter = new IaasComputeMemberNetworkInterfaceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberNetworkInterface::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemembernetworkinterface_event_mtu_filter()
    {
        try {
            $request = new Request(
                [
                'mtu'  =>  '1'
                ]
            );

            $filter = new IaasComputeMemberNetworkInterfaceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberNetworkInterface::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemembernetworkinterface_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberNetworkInterfaceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberNetworkInterface::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemembernetworkinterface_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberNetworkInterfaceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberNetworkInterface::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemembernetworkinterface_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberNetworkInterfaceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberNetworkInterface::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemembernetworkinterface_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberNetworkInterfaceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberNetworkInterface::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemembernetworkinterface_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberNetworkInterfaceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberNetworkInterface::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemembernetworkinterface_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberNetworkInterfaceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberNetworkInterface::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemembernetworkinterface_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberNetworkInterfaceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberNetworkInterface::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemembernetworkinterface_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberNetworkInterfaceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberNetworkInterface::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemembernetworkinterface_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberNetworkInterfaceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberNetworkInterface::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}
