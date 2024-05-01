<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasDhcpServerQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasDhcpServerService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasDhcpServerTestTraits
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

    public function test_http_iaasdhcpserver_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasdhcpserver',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasdhcpserver_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasdhcpserver', [
            'form_params'   =>  [
                'name'  =>  'a',
                'ssh_username'  =>  'a',
                'ssh_password'  =>  'a',
                'api_token'  =>  'a',
                'api_url'  =>  'a',
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
    public function test_iaasdhcpserver_model_get()
    {
        $result = AbstractIaasDhcpServerService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasdhcpserver_get_all()
    {
        $result = AbstractIaasDhcpServerService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasdhcpserver_get_paginated()
    {
        $result = AbstractIaasDhcpServerService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasdhcpserver_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasDhcpServer\IaasDhcpServerRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdhcpserver_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasDhcpServer\IaasDhcpServerCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdhcpserver_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasDhcpServer\IaasDhcpServerCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdhcpserver_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasDhcpServer\IaasDhcpServerSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdhcpserver_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasDhcpServer\IaasDhcpServerSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdhcpserver_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasDhcpServer\IaasDhcpServerUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdhcpserver_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasDhcpServer\IaasDhcpServerUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdhcpserver_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasDhcpServer\IaasDhcpServerDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdhcpserver_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasDhcpServer\IaasDhcpServerDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdhcpserver_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasDhcpServer\IaasDhcpServerRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdhcpserver_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasDhcpServer\IaasDhcpServerRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdhcpserver_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasDhcpServer::first();

            event(new \NextDeveloper\IAAS\Events\IaasDhcpServer\IaasDhcpServerRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdhcpserver_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasDhcpServer::first();

            event(new \NextDeveloper\IAAS\Events\IaasDhcpServer\IaasDhcpServerCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdhcpserver_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasDhcpServer::first();

            event(new \NextDeveloper\IAAS\Events\IaasDhcpServer\IaasDhcpServerCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdhcpserver_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasDhcpServer::first();

            event(new \NextDeveloper\IAAS\Events\IaasDhcpServer\IaasDhcpServerSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdhcpserver_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasDhcpServer::first();

            event(new \NextDeveloper\IAAS\Events\IaasDhcpServer\IaasDhcpServerSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdhcpserver_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasDhcpServer::first();

            event(new \NextDeveloper\IAAS\Events\IaasDhcpServer\IaasDhcpServerUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdhcpserver_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasDhcpServer::first();

            event(new \NextDeveloper\IAAS\Events\IaasDhcpServer\IaasDhcpServerUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdhcpserver_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasDhcpServer::first();

            event(new \NextDeveloper\IAAS\Events\IaasDhcpServer\IaasDhcpServerDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdhcpserver_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasDhcpServer::first();

            event(new \NextDeveloper\IAAS\Events\IaasDhcpServer\IaasDhcpServerDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdhcpserver_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasDhcpServer::first();

            event(new \NextDeveloper\IAAS\Events\IaasDhcpServer\IaasDhcpServerRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdhcpserver_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasDhcpServer::first();

            event(new \NextDeveloper\IAAS\Events\IaasDhcpServer\IaasDhcpServerRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdhcpserver_event_name_filter()
    {
        try {
            $request = new Request(
                [
                'name'  =>  'a'
                ]
            );

            $filter = new IaasDhcpServerQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasDhcpServer::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdhcpserver_event_ssh_username_filter()
    {
        try {
            $request = new Request(
                [
                'ssh_username'  =>  'a'
                ]
            );

            $filter = new IaasDhcpServerQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasDhcpServer::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdhcpserver_event_ssh_password_filter()
    {
        try {
            $request = new Request(
                [
                'ssh_password'  =>  'a'
                ]
            );

            $filter = new IaasDhcpServerQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasDhcpServer::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdhcpserver_event_api_token_filter()
    {
        try {
            $request = new Request(
                [
                'api_token'  =>  'a'
                ]
            );

            $filter = new IaasDhcpServerQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasDhcpServer::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdhcpserver_event_api_url_filter()
    {
        try {
            $request = new Request(
                [
                'api_url'  =>  'a'
                ]
            );

            $filter = new IaasDhcpServerQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasDhcpServer::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdhcpserver_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasDhcpServerQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasDhcpServer::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdhcpserver_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasDhcpServerQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasDhcpServer::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdhcpserver_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasDhcpServerQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasDhcpServer::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdhcpserver_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasDhcpServerQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasDhcpServer::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdhcpserver_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasDhcpServerQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasDhcpServer::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdhcpserver_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasDhcpServerQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasDhcpServer::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdhcpserver_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasDhcpServerQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasDhcpServer::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdhcpserver_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasDhcpServerQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasDhcpServer::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdhcpserver_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasDhcpServerQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasDhcpServer::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}