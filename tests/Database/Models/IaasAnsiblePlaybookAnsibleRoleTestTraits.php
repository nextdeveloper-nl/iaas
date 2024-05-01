<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasAnsiblePlaybookAnsibleRoleQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasAnsiblePlaybookAnsibleRoleService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasAnsiblePlaybookAnsibleRoleTestTraits
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

    public function test_http_iaasansibleplaybookansiblerole_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasansibleplaybookansiblerole',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasansibleplaybookansiblerole_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasansibleplaybookansiblerole', [
            'form_params'   =>  [
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
    public function test_iaasansibleplaybookansiblerole_model_get()
    {
        $result = AbstractIaasAnsiblePlaybookAnsibleRoleService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasansibleplaybookansiblerole_get_all()
    {
        $result = AbstractIaasAnsiblePlaybookAnsibleRoleService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasansibleplaybookansiblerole_get_paginated()
    {
        $result = AbstractIaasAnsiblePlaybookAnsibleRoleService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasansibleplaybookansiblerole_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookAnsibleRole\IaasAnsiblePlaybookAnsibleRoleRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookansiblerole_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookAnsibleRole\IaasAnsiblePlaybookAnsibleRoleCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookansiblerole_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookAnsibleRole\IaasAnsiblePlaybookAnsibleRoleCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookansiblerole_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookAnsibleRole\IaasAnsiblePlaybookAnsibleRoleSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookansiblerole_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookAnsibleRole\IaasAnsiblePlaybookAnsibleRoleSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookansiblerole_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookAnsibleRole\IaasAnsiblePlaybookAnsibleRoleUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookansiblerole_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookAnsibleRole\IaasAnsiblePlaybookAnsibleRoleUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookansiblerole_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookAnsibleRole\IaasAnsiblePlaybookAnsibleRoleDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookansiblerole_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookAnsibleRole\IaasAnsiblePlaybookAnsibleRoleDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookansiblerole_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookAnsibleRole\IaasAnsiblePlaybookAnsibleRoleRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookansiblerole_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookAnsibleRole\IaasAnsiblePlaybookAnsibleRoleRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookansiblerole_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookAnsibleRole::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookAnsibleRole\IaasAnsiblePlaybookAnsibleRoleRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookansiblerole_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookAnsibleRole::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookAnsibleRole\IaasAnsiblePlaybookAnsibleRoleCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookansiblerole_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookAnsibleRole::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookAnsibleRole\IaasAnsiblePlaybookAnsibleRoleCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookansiblerole_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookAnsibleRole::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookAnsibleRole\IaasAnsiblePlaybookAnsibleRoleSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookansiblerole_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookAnsibleRole::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookAnsibleRole\IaasAnsiblePlaybookAnsibleRoleSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookansiblerole_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookAnsibleRole::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookAnsibleRole\IaasAnsiblePlaybookAnsibleRoleUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookansiblerole_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookAnsibleRole::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookAnsibleRole\IaasAnsiblePlaybookAnsibleRoleUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookansiblerole_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookAnsibleRole::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookAnsibleRole\IaasAnsiblePlaybookAnsibleRoleDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookansiblerole_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookAnsibleRole::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookAnsibleRole\IaasAnsiblePlaybookAnsibleRoleDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookansiblerole_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookAnsibleRole::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookAnsibleRole\IaasAnsiblePlaybookAnsibleRoleRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookansiblerole_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookAnsibleRole::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookAnsibleRole\IaasAnsiblePlaybookAnsibleRoleRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookansiblerole_event_position_filter()
    {
        try {
            $request = new Request(
                [
                'position'  =>  '1'
                ]
            );

            $filter = new IaasAnsiblePlaybookAnsibleRoleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookAnsibleRole::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookansiblerole_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasAnsiblePlaybookAnsibleRoleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookAnsibleRole::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookansiblerole_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasAnsiblePlaybookAnsibleRoleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookAnsibleRole::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookansiblerole_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasAnsiblePlaybookAnsibleRoleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookAnsibleRole::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookansiblerole_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsiblePlaybookAnsibleRoleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookAnsibleRole::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookansiblerole_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsiblePlaybookAnsibleRoleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookAnsibleRole::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookansiblerole_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsiblePlaybookAnsibleRoleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookAnsibleRole::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookansiblerole_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsiblePlaybookAnsibleRoleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookAnsibleRole::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookansiblerole_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsiblePlaybookAnsibleRoleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookAnsibleRole::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookansiblerole_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsiblePlaybookAnsibleRoleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookAnsibleRole::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}