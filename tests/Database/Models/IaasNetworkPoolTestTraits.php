<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasNetworkPoolQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasNetworkPoolService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasNetworkPoolTestTraits
{
    public $http;

    /**
    *   Creating the Guzzle object
    */
    public function setupGuzzle()
    {
        $this->http = new Client([
            'base_uri'  =>  '127.0.0.1:8000'
        ]);
    }

    /**
    *   Destroying the Guzzle object
    */
    public function destroyGuzzle()
    {
        $this->http = null;
    }

    public function test_http_iaasnetworkpool_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasnetworkpool',
            ['http_errors' => false]
        );

        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
        ]);
    }

    public function test_http_iaasnetworkpool_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request('POST', '/iaas/iaasnetworkpool', [
            'form_params'   =>  [
                'name'  =>  'a',
                'provisioning_alg'  =>  'a',
                'management_package_name'  =>  'a',
                'resource_validator'  =>  'a',
                'vlan_start'  =>  '1',
                'vlan_end'  =>  '1',
                'vxlan_start'  =>  '1',
                'vxlan_end'  =>  '1',
                'has_vlan_support'  =>  '1',
                'has_vxlan_support'  =>  '1',
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
    public function test_iaasnetworkpool_model_get()
    {
        $result = AbstractIaasNetworkPoolService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasnetworkpool_get_all()
    {
        $result = AbstractIaasNetworkPoolService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasnetworkpool_get_paginated()
    {
        $result = AbstractIaasNetworkPoolService::get(null, [
            'paginated' =>  'true'
        ]);

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasnetworkpool_event_retrieved_without_object()
    {
        try {
            event( new \NextDeveloper\IAAS\Events\IaasNetworkPool\IaasNetworkPoolRetrievedEvent() );
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpool_event_created_without_object()
    {
        try {
            event( new \NextDeveloper\IAAS\Events\IaasNetworkPool\IaasNetworkPoolCreatedEvent() );
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpool_event_creating_without_object()
    {
        try {
            event( new \NextDeveloper\IAAS\Events\IaasNetworkPool\IaasNetworkPoolCreatingEvent() );
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpool_event_saving_without_object()
    {
        try {
            event( new \NextDeveloper\IAAS\Events\IaasNetworkPool\IaasNetworkPoolSavingEvent() );
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpool_event_saved_without_object()
    {
        try {
            event( new \NextDeveloper\IAAS\Events\IaasNetworkPool\IaasNetworkPoolSavedEvent() );
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpool_event_updating_without_object()
    {
        try {
            event( new \NextDeveloper\IAAS\Events\IaasNetworkPool\IaasNetworkPoolUpdatingEvent() );
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpool_event_updated_without_object()
    {
        try {
            event( new \NextDeveloper\IAAS\Events\IaasNetworkPool\IaasNetworkPoolUpdatedEvent() );
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpool_event_deleting_without_object()
    {
        try {
            event( new \NextDeveloper\IAAS\Events\IaasNetworkPool\IaasNetworkPoolDeletingEvent() );
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpool_event_deleted_without_object()
    {
        try {
            event( new \NextDeveloper\IAAS\Events\IaasNetworkPool\IaasNetworkPoolDeletedEvent() );
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpool_event_restoring_without_object()
    {
        try {
            event( new \NextDeveloper\IAAS\Events\IaasNetworkPool\IaasNetworkPoolRestoringEvent() );
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpool_event_restored_without_object()
    {
        try {
            event( new \NextDeveloper\IAAS\Events\IaasNetworkPool\IaasNetworkPoolRestoredEvent() );
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkpool_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPool::first();

            event( new \NextDeveloper\IAAS\Events\IaasNetworkPool\IaasNetworkPoolRetrievedEvent($model) );
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpool_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPool::first();

            event( new \NextDeveloper\IAAS\Events\IaasNetworkPool\IaasNetworkPoolCreatedEvent($model) );
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpool_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPool::first();

            event( new \NextDeveloper\IAAS\Events\IaasNetworkPool\IaasNetworkPoolCreatingEvent($model) );
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpool_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPool::first();

            event( new \NextDeveloper\IAAS\Events\IaasNetworkPool\IaasNetworkPoolSavingEvent($model) );
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpool_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPool::first();

            event( new \NextDeveloper\IAAS\Events\IaasNetworkPool\IaasNetworkPoolSavedEvent($model) );
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpool_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPool::first();

            event( new \NextDeveloper\IAAS\Events\IaasNetworkPool\IaasNetworkPoolUpdatingEvent($model) );
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpool_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPool::first();

            event( new \NextDeveloper\IAAS\Events\IaasNetworkPool\IaasNetworkPoolUpdatedEvent($model) );
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpool_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPool::first();

            event( new \NextDeveloper\IAAS\Events\IaasNetworkPool\IaasNetworkPoolDeletingEvent($model) );
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpool_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPool::first();

            event( new \NextDeveloper\IAAS\Events\IaasNetworkPool\IaasNetworkPoolDeletedEvent($model) );
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpool_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPool::first();

            event( new \NextDeveloper\IAAS\Events\IaasNetworkPool\IaasNetworkPoolRestoringEvent($model) );
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkpool_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPool::first();

            event( new \NextDeveloper\IAAS\Events\IaasNetworkPool\IaasNetworkPoolRestoredEvent($model) );
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkpool_event_name_filter()
    {
        try {
            $request = new Request([
                'name'  =>  'a'
            ]);

            $filter = new IaasNetworkPoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkpool_event_provisioning_alg_filter()
    {
        try {
            $request = new Request([
                'provisioning_alg'  =>  'a'
            ]);

            $filter = new IaasNetworkPoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkpool_event_management_package_name_filter()
    {
        try {
            $request = new Request([
                'management_package_name'  =>  'a'
            ]);

            $filter = new IaasNetworkPoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkpool_event_resource_validator_filter()
    {
        try {
            $request = new Request([
                'resource_validator'  =>  'a'
            ]);

            $filter = new IaasNetworkPoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkpool_event_vlan_start_filter()
    {
        try {
            $request = new Request([
                'vlan_start'  =>  '1'
            ]);

            $filter = new IaasNetworkPoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkpool_event_vlan_end_filter()
    {
        try {
            $request = new Request([
                'vlan_end'  =>  '1'
            ]);

            $filter = new IaasNetworkPoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkpool_event_vxlan_start_filter()
    {
        try {
            $request = new Request([
                'vxlan_start'  =>  '1'
            ]);

            $filter = new IaasNetworkPoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkpool_event_vxlan_end_filter()
    {
        try {
            $request = new Request([
                'vxlan_end'  =>  '1'
            ]);

            $filter = new IaasNetworkPoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkpool_event_has_vlan_support_filter()
    {
        try {
            $request = new Request([
                'has_vlan_support'  =>  '1'
            ]);

            $filter = new IaasNetworkPoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkpool_event_has_vxlan_support_filter()
    {
        try {
            $request = new Request([
                'has_vxlan_support'  =>  '1'
            ]);

            $filter = new IaasNetworkPoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkpool_event_created_at_filter_start()
    {
        try {
            $request = new Request([
                'created_atStart'  =>  now()
            ]);

            $filter = new IaasNetworkPoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkpool_event_updated_at_filter_start()
    {
        try {
            $request = new Request([
                'updated_atStart'  =>  now()
            ]);

            $filter = new IaasNetworkPoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkpool_event_deleted_at_filter_start()
    {
        try {
            $request = new Request([
                'deleted_atStart'  =>  now()
            ]);

            $filter = new IaasNetworkPoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkpool_event_created_at_filter_end()
    {
        try {
            $request = new Request([
                'created_atEnd'  =>  now()
            ]);

            $filter = new IaasNetworkPoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkpool_event_updated_at_filter_end()
    {
        try {
            $request = new Request([
                'updated_atEnd'  =>  now()
            ]);

            $filter = new IaasNetworkPoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkpool_event_deleted_at_filter_end()
    {
        try {
            $request = new Request([
                'deleted_atEnd'  =>  now()
            ]);

            $filter = new IaasNetworkPoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkpool_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request([
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
            ]);

            $filter = new IaasNetworkPoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkpool_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request([
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
            ]);

            $filter = new IaasNetworkPoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkpool_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request([
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
            ]);

            $filter = new IaasNetworkPoolQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkPool::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n
}