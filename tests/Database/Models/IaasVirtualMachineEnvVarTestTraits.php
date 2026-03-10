<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasVirtualMachineEnvVarQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasVirtualMachineEnvVarService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasVirtualMachineEnvVarTestTraits
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

    public function test_http_iaasvirtualmachineenvvar_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasvirtualmachineenvvar',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasvirtualmachineenvvar_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasvirtualmachineenvvar', [
            'form_params'   =>  [
                'key'  =>  'a',
                'value'  =>  'a',
                'source_type'  =>  'a',
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
    public function test_iaasvirtualmachineenvvar_model_get()
    {
        $result = AbstractIaasVirtualMachineEnvVarService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasvirtualmachineenvvar_get_all()
    {
        $result = AbstractIaasVirtualMachineEnvVarService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasvirtualmachineenvvar_get_paginated()
    {
        $result = AbstractIaasVirtualMachineEnvVarService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasvirtualmachineenvvar_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineEnvVar\IaasVirtualMachineEnvVarRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachineenvvar_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineEnvVar\IaasVirtualMachineEnvVarCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachineenvvar_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineEnvVar\IaasVirtualMachineEnvVarCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachineenvvar_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineEnvVar\IaasVirtualMachineEnvVarSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachineenvvar_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineEnvVar\IaasVirtualMachineEnvVarSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachineenvvar_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineEnvVar\IaasVirtualMachineEnvVarUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachineenvvar_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineEnvVar\IaasVirtualMachineEnvVarUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachineenvvar_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineEnvVar\IaasVirtualMachineEnvVarDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachineenvvar_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineEnvVar\IaasVirtualMachineEnvVarDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachineenvvar_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineEnvVar\IaasVirtualMachineEnvVarRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachineenvvar_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineEnvVar\IaasVirtualMachineEnvVarRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachineenvvar_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineEnvVar::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineEnvVar\IaasVirtualMachineEnvVarRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachineenvvar_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineEnvVar::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineEnvVar\IaasVirtualMachineEnvVarCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachineenvvar_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineEnvVar::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineEnvVar\IaasVirtualMachineEnvVarCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachineenvvar_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineEnvVar::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineEnvVar\IaasVirtualMachineEnvVarSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachineenvvar_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineEnvVar::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineEnvVar\IaasVirtualMachineEnvVarSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachineenvvar_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineEnvVar::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineEnvVar\IaasVirtualMachineEnvVarUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachineenvvar_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineEnvVar::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineEnvVar\IaasVirtualMachineEnvVarUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachineenvvar_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineEnvVar::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineEnvVar\IaasVirtualMachineEnvVarDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachineenvvar_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineEnvVar::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineEnvVar\IaasVirtualMachineEnvVarDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachineenvvar_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineEnvVar::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineEnvVar\IaasVirtualMachineEnvVarRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachineenvvar_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineEnvVar::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineEnvVar\IaasVirtualMachineEnvVarRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachineenvvar_event_key_filter()
    {
        try {
            $request = new Request(
                [
                'key'  =>  'a'
                ]
            );

            $filter = new IaasVirtualMachineEnvVarQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineEnvVar::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachineenvvar_event_value_filter()
    {
        try {
            $request = new Request(
                [
                'value'  =>  'a'
                ]
            );

            $filter = new IaasVirtualMachineEnvVarQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineEnvVar::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachineenvvar_event_source_type_filter()
    {
        try {
            $request = new Request(
                [
                'source_type'  =>  'a'
                ]
            );

            $filter = new IaasVirtualMachineEnvVarQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineEnvVar::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachineenvvar_event_description_filter()
    {
        try {
            $request = new Request(
                [
                'description'  =>  'a'
                ]
            );

            $filter = new IaasVirtualMachineEnvVarQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineEnvVar::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachineenvvar_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineEnvVarQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineEnvVar::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachineenvvar_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineEnvVarQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineEnvVar::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachineenvvar_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineEnvVarQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineEnvVar::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachineenvvar_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineEnvVarQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineEnvVar::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachineenvvar_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineEnvVarQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineEnvVar::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachineenvvar_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineEnvVarQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineEnvVar::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachineenvvar_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineEnvVarQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineEnvVar::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachineenvvar_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineEnvVarQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineEnvVar::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachineenvvar_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineEnvVarQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineEnvVar::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}