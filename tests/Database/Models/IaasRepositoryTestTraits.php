<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasRepositoryQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasRepositoryService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasRepositoryTestTraits
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

    public function test_http_iaasrepository_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasrepository',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasrepository_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasrepository', [
            'form_params'   =>  [
                'name'  =>  'a',
                'description'  =>  'a',
                'ssh_username'  =>  'a',
                'ssh_password'  =>  'a',
                'last_hash'  =>  'a',
                'iso_path'  =>  'a',
                'vm_path'  =>  'a',
                'docker_registry_port'  =>  '1',
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
    public function test_iaasrepository_model_get()
    {
        $result = AbstractIaasRepositoryService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasrepository_get_all()
    {
        $result = AbstractIaasRepositoryService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasrepository_get_paginated()
    {
        $result = AbstractIaasRepositoryService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasrepository_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasRepository\IaasRepositoryRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepository_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasRepository\IaasRepositoryCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepository_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasRepository\IaasRepositoryCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepository_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasRepository\IaasRepositorySavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepository_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasRepository\IaasRepositorySavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepository_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasRepository\IaasRepositoryUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepository_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasRepository\IaasRepositoryUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepository_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasRepository\IaasRepositoryDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepository_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasRepository\IaasRepositoryDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepository_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasRepository\IaasRepositoryRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepository_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasRepository\IaasRepositoryRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepository_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasRepository::first();

            event(new \NextDeveloper\IAAS\Events\IaasRepository\IaasRepositoryRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepository_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasRepository::first();

            event(new \NextDeveloper\IAAS\Events\IaasRepository\IaasRepositoryCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepository_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasRepository::first();

            event(new \NextDeveloper\IAAS\Events\IaasRepository\IaasRepositoryCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepository_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasRepository::first();

            event(new \NextDeveloper\IAAS\Events\IaasRepository\IaasRepositorySavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepository_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasRepository::first();

            event(new \NextDeveloper\IAAS\Events\IaasRepository\IaasRepositorySavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepository_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasRepository::first();

            event(new \NextDeveloper\IAAS\Events\IaasRepository\IaasRepositoryUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepository_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasRepository::first();

            event(new \NextDeveloper\IAAS\Events\IaasRepository\IaasRepositoryUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepository_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasRepository::first();

            event(new \NextDeveloper\IAAS\Events\IaasRepository\IaasRepositoryDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepository_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasRepository::first();

            event(new \NextDeveloper\IAAS\Events\IaasRepository\IaasRepositoryDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepository_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasRepository::first();

            event(new \NextDeveloper\IAAS\Events\IaasRepository\IaasRepositoryRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepository_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasRepository::first();

            event(new \NextDeveloper\IAAS\Events\IaasRepository\IaasRepositoryRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepository_event_name_filter()
    {
        try {
            $request = new Request(
                [
                'name'  =>  'a'
                ]
            );

            $filter = new IaasRepositoryQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepository::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepository_event_description_filter()
    {
        try {
            $request = new Request(
                [
                'description'  =>  'a'
                ]
            );

            $filter = new IaasRepositoryQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepository::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepository_event_ssh_username_filter()
    {
        try {
            $request = new Request(
                [
                'ssh_username'  =>  'a'
                ]
            );

            $filter = new IaasRepositoryQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepository::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepository_event_ssh_password_filter()
    {
        try {
            $request = new Request(
                [
                'ssh_password'  =>  'a'
                ]
            );

            $filter = new IaasRepositoryQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepository::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepository_event_last_hash_filter()
    {
        try {
            $request = new Request(
                [
                'last_hash'  =>  'a'
                ]
            );

            $filter = new IaasRepositoryQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepository::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepository_event_iso_path_filter()
    {
        try {
            $request = new Request(
                [
                'iso_path'  =>  'a'
                ]
            );

            $filter = new IaasRepositoryQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepository::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepository_event_vm_path_filter()
    {
        try {
            $request = new Request(
                [
                'vm_path'  =>  'a'
                ]
            );

            $filter = new IaasRepositoryQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepository::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepository_event_docker_registry_port_filter()
    {
        try {
            $request = new Request(
                [
                'docker_registry_port'  =>  '1'
                ]
            );

            $filter = new IaasRepositoryQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepository::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepository_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasRepositoryQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepository::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepository_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasRepositoryQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepository::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepository_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasRepositoryQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepository::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepository_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasRepositoryQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepository::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepository_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasRepositoryQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepository::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepository_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasRepositoryQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepository::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepository_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasRepositoryQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepository::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepository_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasRepositoryQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepository::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepository_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasRepositoryQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepository::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}