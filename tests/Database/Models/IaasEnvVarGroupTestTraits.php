<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasEnvVarGroupQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasEnvVarGroupService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasEnvVarGroupTestTraits
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

    public function test_http_iaasenvvargroup_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasenvvargroup',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasenvvargroup_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasenvvargroup', [
            'form_params'   =>  [
                'name'  =>  'a',
                'description'  =>  'a',
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
    public function test_iaasenvvargroup_model_get()
    {
        $result = AbstractIaasEnvVarGroupService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasenvvargroup_get_all()
    {
        $result = AbstractIaasEnvVarGroupService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasenvvargroup_get_paginated()
    {
        $result = AbstractIaasEnvVarGroupService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasenvvargroup_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasEnvVarGroup\IaasEnvVarGroupRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasenvvargroup_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasEnvVarGroup\IaasEnvVarGroupCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasenvvargroup_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasEnvVarGroup\IaasEnvVarGroupCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasenvvargroup_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasEnvVarGroup\IaasEnvVarGroupSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasenvvargroup_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasEnvVarGroup\IaasEnvVarGroupSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasenvvargroup_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasEnvVarGroup\IaasEnvVarGroupUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasenvvargroup_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasEnvVarGroup\IaasEnvVarGroupUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasenvvargroup_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasEnvVarGroup\IaasEnvVarGroupDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasenvvargroup_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasEnvVarGroup\IaasEnvVarGroupDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasenvvargroup_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasEnvVarGroup\IaasEnvVarGroupRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasenvvargroup_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasEnvVarGroup\IaasEnvVarGroupRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasenvvargroup_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasEnvVarGroup::first();

            event(new \NextDeveloper\IAAS\Events\IaasEnvVarGroup\IaasEnvVarGroupRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasenvvargroup_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasEnvVarGroup::first();

            event(new \NextDeveloper\IAAS\Events\IaasEnvVarGroup\IaasEnvVarGroupCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasenvvargroup_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasEnvVarGroup::first();

            event(new \NextDeveloper\IAAS\Events\IaasEnvVarGroup\IaasEnvVarGroupCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasenvvargroup_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasEnvVarGroup::first();

            event(new \NextDeveloper\IAAS\Events\IaasEnvVarGroup\IaasEnvVarGroupSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasenvvargroup_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasEnvVarGroup::first();

            event(new \NextDeveloper\IAAS\Events\IaasEnvVarGroup\IaasEnvVarGroupSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasenvvargroup_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasEnvVarGroup::first();

            event(new \NextDeveloper\IAAS\Events\IaasEnvVarGroup\IaasEnvVarGroupUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasenvvargroup_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasEnvVarGroup::first();

            event(new \NextDeveloper\IAAS\Events\IaasEnvVarGroup\IaasEnvVarGroupUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasenvvargroup_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasEnvVarGroup::first();

            event(new \NextDeveloper\IAAS\Events\IaasEnvVarGroup\IaasEnvVarGroupDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasenvvargroup_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasEnvVarGroup::first();

            event(new \NextDeveloper\IAAS\Events\IaasEnvVarGroup\IaasEnvVarGroupDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasenvvargroup_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasEnvVarGroup::first();

            event(new \NextDeveloper\IAAS\Events\IaasEnvVarGroup\IaasEnvVarGroupRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasenvvargroup_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasEnvVarGroup::first();

            event(new \NextDeveloper\IAAS\Events\IaasEnvVarGroup\IaasEnvVarGroupRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasenvvargroup_event_name_filter()
    {
        try {
            $request = new Request(
                [
                'name'  =>  'a'
                ]
            );

            $filter = new IaasEnvVarGroupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasEnvVarGroup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasenvvargroup_event_description_filter()
    {
        try {
            $request = new Request(
                [
                'description'  =>  'a'
                ]
            );

            $filter = new IaasEnvVarGroupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasEnvVarGroup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasenvvargroup_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasEnvVarGroupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasEnvVarGroup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasenvvargroup_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasEnvVarGroupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasEnvVarGroup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasenvvargroup_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasEnvVarGroupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasEnvVarGroup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasenvvargroup_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasEnvVarGroupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasEnvVarGroup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasenvvargroup_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasEnvVarGroupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasEnvVarGroup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasenvvargroup_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasEnvVarGroupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasEnvVarGroup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasenvvargroup_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasEnvVarGroupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasEnvVarGroup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasenvvargroup_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasEnvVarGroupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasEnvVarGroup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasenvvargroup_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasEnvVarGroupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasEnvVarGroup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}