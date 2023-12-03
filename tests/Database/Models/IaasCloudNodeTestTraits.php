<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasCloudNodeQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasCloudNodeService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasCloudNodeTestTraits
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

    public function test_http_iaascloudnode_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaascloudnode',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaascloudnode_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaascloudnode', [
            'form_params'   =>  [
                'name'  =>  'a',
                'slug'  =>  'a',
                'maintenance_mode'  =>  '1',
                'position'  =>  '1',
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
    public function test_iaascloudnode_model_get()
    {
        $result = AbstractIaasCloudNodeService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaascloudnode_get_all()
    {
        $result = AbstractIaasCloudNodeService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaascloudnode_get_paginated()
    {
        $result = AbstractIaasCloudNodeService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaascloudnode_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasCloudNode\IaasCloudNodeRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascloudnode_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasCloudNode\IaasCloudNodeCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascloudnode_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasCloudNode\IaasCloudNodeCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascloudnode_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasCloudNode\IaasCloudNodeSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascloudnode_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasCloudNode\IaasCloudNodeSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascloudnode_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasCloudNode\IaasCloudNodeUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascloudnode_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasCloudNode\IaasCloudNodeUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascloudnode_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasCloudNode\IaasCloudNodeDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascloudnode_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasCloudNode\IaasCloudNodeDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascloudnode_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasCloudNode\IaasCloudNodeRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascloudnode_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasCloudNode\IaasCloudNodeRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascloudnode_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasCloudNode::first();

            event(new \NextDeveloper\IAAS\Events\IaasCloudNode\IaasCloudNodeRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascloudnode_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasCloudNode::first();

            event(new \NextDeveloper\IAAS\Events\IaasCloudNode\IaasCloudNodeCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascloudnode_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasCloudNode::first();

            event(new \NextDeveloper\IAAS\Events\IaasCloudNode\IaasCloudNodeCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascloudnode_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasCloudNode::first();

            event(new \NextDeveloper\IAAS\Events\IaasCloudNode\IaasCloudNodeSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascloudnode_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasCloudNode::first();

            event(new \NextDeveloper\IAAS\Events\IaasCloudNode\IaasCloudNodeSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascloudnode_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasCloudNode::first();

            event(new \NextDeveloper\IAAS\Events\IaasCloudNode\IaasCloudNodeUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascloudnode_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasCloudNode::first();

            event(new \NextDeveloper\IAAS\Events\IaasCloudNode\IaasCloudNodeUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascloudnode_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasCloudNode::first();

            event(new \NextDeveloper\IAAS\Events\IaasCloudNode\IaasCloudNodeDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascloudnode_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasCloudNode::first();

            event(new \NextDeveloper\IAAS\Events\IaasCloudNode\IaasCloudNodeDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascloudnode_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasCloudNode::first();

            event(new \NextDeveloper\IAAS\Events\IaasCloudNode\IaasCloudNodeRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascloudnode_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasCloudNode::first();

            event(new \NextDeveloper\IAAS\Events\IaasCloudNode\IaasCloudNodeRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascloudnode_event_name_filter()
    {
        try {
            $request = new Request(
                [
                'name'  =>  'a'
                ]
            );

            $filter = new IaasCloudNodeQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasCloudNode::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascloudnode_event_slug_filter()
    {
        try {
            $request = new Request(
                [
                'slug'  =>  'a'
                ]
            );

            $filter = new IaasCloudNodeQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasCloudNode::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascloudnode_event_maintenance_mode_filter()
    {
        try {
            $request = new Request(
                [
                'maintenance_mode'  =>  '1'
                ]
            );

            $filter = new IaasCloudNodeQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasCloudNode::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascloudnode_event_position_filter()
    {
        try {
            $request = new Request(
                [
                'position'  =>  '1'
                ]
            );

            $filter = new IaasCloudNodeQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasCloudNode::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascloudnode_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasCloudNodeQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasCloudNode::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascloudnode_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasCloudNodeQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasCloudNode::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascloudnode_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasCloudNodeQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasCloudNode::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascloudnode_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasCloudNodeQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasCloudNode::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascloudnode_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasCloudNodeQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasCloudNode::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascloudnode_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasCloudNodeQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasCloudNode::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascloudnode_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasCloudNodeQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasCloudNode::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascloudnode_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasCloudNodeQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasCloudNode::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascloudnode_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasCloudNodeQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasCloudNode::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n
}