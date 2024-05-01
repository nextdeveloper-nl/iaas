<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasAnsibleRoleQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasAnsibleRoleService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasAnsibleRoleTestTraits
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

    public function test_http_iaasansiblerole_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasansiblerole',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasansiblerole_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasansiblerole', [
            'form_params'   =>  [
                'name'  =>  'a',
                'hash'  =>  'a',
                'min_ansible_version'  =>  'a',
                'prerequisites'  =>  'a',
                'version'  =>  '1',
                'release_number'  =>  '1',
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
    public function test_iaasansiblerole_model_get()
    {
        $result = AbstractIaasAnsibleRoleService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasansiblerole_get_all()
    {
        $result = AbstractIaasAnsibleRoleService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasansiblerole_get_paginated()
    {
        $result = AbstractIaasAnsibleRoleService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasansiblerole_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleRole\IaasAnsibleRoleRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblerole_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleRole\IaasAnsibleRoleCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblerole_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleRole\IaasAnsibleRoleCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblerole_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleRole\IaasAnsibleRoleSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblerole_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleRole\IaasAnsibleRoleSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblerole_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleRole\IaasAnsibleRoleUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblerole_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleRole\IaasAnsibleRoleUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblerole_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleRole\IaasAnsibleRoleDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblerole_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleRole\IaasAnsibleRoleDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblerole_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleRole\IaasAnsibleRoleRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblerole_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleRole\IaasAnsibleRoleRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblerole_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleRole::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleRole\IaasAnsibleRoleRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblerole_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleRole::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleRole\IaasAnsibleRoleCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblerole_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleRole::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleRole\IaasAnsibleRoleCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblerole_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleRole::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleRole\IaasAnsibleRoleSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblerole_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleRole::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleRole\IaasAnsibleRoleSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblerole_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleRole::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleRole\IaasAnsibleRoleUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblerole_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleRole::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleRole\IaasAnsibleRoleUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblerole_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleRole::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleRole\IaasAnsibleRoleDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblerole_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleRole::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleRole\IaasAnsibleRoleDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblerole_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleRole::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleRole\IaasAnsibleRoleRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblerole_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleRole::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleRole\IaasAnsibleRoleRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblerole_event_name_filter()
    {
        try {
            $request = new Request(
                [
                'name'  =>  'a'
                ]
            );

            $filter = new IaasAnsibleRoleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleRole::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblerole_event_hash_filter()
    {
        try {
            $request = new Request(
                [
                'hash'  =>  'a'
                ]
            );

            $filter = new IaasAnsibleRoleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleRole::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblerole_event_min_ansible_version_filter()
    {
        try {
            $request = new Request(
                [
                'min_ansible_version'  =>  'a'
                ]
            );

            $filter = new IaasAnsibleRoleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleRole::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblerole_event_prerequisites_filter()
    {
        try {
            $request = new Request(
                [
                'prerequisites'  =>  'a'
                ]
            );

            $filter = new IaasAnsibleRoleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleRole::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblerole_event_version_filter()
    {
        try {
            $request = new Request(
                [
                'version'  =>  '1'
                ]
            );

            $filter = new IaasAnsibleRoleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleRole::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblerole_event_release_number_filter()
    {
        try {
            $request = new Request(
                [
                'release_number'  =>  '1'
                ]
            );

            $filter = new IaasAnsibleRoleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleRole::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblerole_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasAnsibleRoleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleRole::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblerole_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasAnsibleRoleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleRole::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblerole_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasAnsibleRoleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleRole::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblerole_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsibleRoleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleRole::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblerole_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsibleRoleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleRole::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblerole_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsibleRoleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleRole::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblerole_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsibleRoleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleRole::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblerole_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsibleRoleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleRole::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblerole_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsibleRoleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleRole::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}