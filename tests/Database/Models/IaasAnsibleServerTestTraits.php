<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasAnsibleServerQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasAnsibleServerService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasAnsibleServerTestTraits
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

    public function test_http_iaasansibleserver_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasansibleserver',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasansibleserver_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasansibleserver', [
            'form_params'   =>  [
                'name'  =>  'a',
                'ssh_username'  =>  'a',
                'ssh_password'  =>  'a',
                'roles_path'  =>  'a',
                'system_playbooks_path'  =>  'a',
                'execution_path'  =>  'a',
                'ssh_port'  =>  '1',
                'ansible_version'  =>  '1',
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
    public function test_iaasansibleserver_model_get()
    {
        $result = AbstractIaasAnsibleServerService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasansibleserver_get_all()
    {
        $result = AbstractIaasAnsibleServerService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasansibleserver_get_paginated()
    {
        $result = AbstractIaasAnsibleServerService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasansibleserver_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleServer\IaasAnsibleServerRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleserver_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleServer\IaasAnsibleServerCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleserver_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleServer\IaasAnsibleServerCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleserver_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleServer\IaasAnsibleServerSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleserver_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleServer\IaasAnsibleServerSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleserver_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleServer\IaasAnsibleServerUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleserver_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleServer\IaasAnsibleServerUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleserver_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleServer\IaasAnsibleServerDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleserver_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleServer\IaasAnsibleServerDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleserver_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleServer\IaasAnsibleServerRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleserver_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleServer\IaasAnsibleServerRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleserver_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleServer::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleServer\IaasAnsibleServerRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleserver_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleServer::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleServer\IaasAnsibleServerCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleserver_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleServer::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleServer\IaasAnsibleServerCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleserver_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleServer::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleServer\IaasAnsibleServerSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleserver_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleServer::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleServer\IaasAnsibleServerSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleserver_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleServer::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleServer\IaasAnsibleServerUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleserver_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleServer::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleServer\IaasAnsibleServerUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleserver_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleServer::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleServer\IaasAnsibleServerDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleserver_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleServer::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleServer\IaasAnsibleServerDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleserver_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleServer::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleServer\IaasAnsibleServerRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleserver_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleServer::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleServer\IaasAnsibleServerRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleserver_event_name_filter()
    {
        try {
            $request = new Request(
                [
                'name'  =>  'a'
                ]
            );

            $filter = new IaasAnsibleServerQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleServer::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleserver_event_ssh_username_filter()
    {
        try {
            $request = new Request(
                [
                'ssh_username'  =>  'a'
                ]
            );

            $filter = new IaasAnsibleServerQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleServer::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleserver_event_ssh_password_filter()
    {
        try {
            $request = new Request(
                [
                'ssh_password'  =>  'a'
                ]
            );

            $filter = new IaasAnsibleServerQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleServer::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleserver_event_roles_path_filter()
    {
        try {
            $request = new Request(
                [
                'roles_path'  =>  'a'
                ]
            );

            $filter = new IaasAnsibleServerQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleServer::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleserver_event_system_playbooks_path_filter()
    {
        try {
            $request = new Request(
                [
                'system_playbooks_path'  =>  'a'
                ]
            );

            $filter = new IaasAnsibleServerQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleServer::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleserver_event_execution_path_filter()
    {
        try {
            $request = new Request(
                [
                'execution_path'  =>  'a'
                ]
            );

            $filter = new IaasAnsibleServerQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleServer::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleserver_event_ssh_port_filter()
    {
        try {
            $request = new Request(
                [
                'ssh_port'  =>  '1'
                ]
            );

            $filter = new IaasAnsibleServerQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleServer::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleserver_event_ansible_version_filter()
    {
        try {
            $request = new Request(
                [
                'ansible_version'  =>  '1'
                ]
            );

            $filter = new IaasAnsibleServerQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleServer::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleserver_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasAnsibleServerQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleServer::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleserver_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasAnsibleServerQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleServer::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleserver_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasAnsibleServerQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleServer::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleserver_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsibleServerQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleServer::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleserver_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsibleServerQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleServer::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleserver_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsibleServerQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleServer::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleserver_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsibleServerQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleServer::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleserver_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsibleServerQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleServer::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleserver_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsibleServerQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleServer::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}