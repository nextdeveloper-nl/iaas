<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasSshPublicKeyVirtualMachineQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasSshPublicKeyVirtualMachineService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasSshPublicKeyVirtualMachineTestTraits
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

    public function test_http_iaassshpublickeyvirtualmachine_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaassshpublickeyvirtualmachine',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaassshpublickeyvirtualmachine_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaassshpublickeyvirtualmachine', [
            'form_params'   =>  [
                    'deployed_at'  =>  now(),
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
    public function test_iaassshpublickeyvirtualmachine_model_get()
    {
        $result = AbstractIaasSshPublicKeyVirtualMachineService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaassshpublickeyvirtualmachine_get_all()
    {
        $result = AbstractIaasSshPublicKeyVirtualMachineService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaassshpublickeyvirtualmachine_get_paginated()
    {
        $result = AbstractIaasSshPublicKeyVirtualMachineService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaassshpublickeyvirtualmachine_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasSshPublicKeyVirtualMachine\IaasSshPublicKeyVirtualMachineRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaassshpublickeyvirtualmachine_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasSshPublicKeyVirtualMachine\IaasSshPublicKeyVirtualMachineCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaassshpublickeyvirtualmachine_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasSshPublicKeyVirtualMachine\IaasSshPublicKeyVirtualMachineCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaassshpublickeyvirtualmachine_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasSshPublicKeyVirtualMachine\IaasSshPublicKeyVirtualMachineSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaassshpublickeyvirtualmachine_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasSshPublicKeyVirtualMachine\IaasSshPublicKeyVirtualMachineSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaassshpublickeyvirtualmachine_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasSshPublicKeyVirtualMachine\IaasSshPublicKeyVirtualMachineUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaassshpublickeyvirtualmachine_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasSshPublicKeyVirtualMachine\IaasSshPublicKeyVirtualMachineUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaassshpublickeyvirtualmachine_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasSshPublicKeyVirtualMachine\IaasSshPublicKeyVirtualMachineDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaassshpublickeyvirtualmachine_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasSshPublicKeyVirtualMachine\IaasSshPublicKeyVirtualMachineDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaassshpublickeyvirtualmachine_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasSshPublicKeyVirtualMachine\IaasSshPublicKeyVirtualMachineRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaassshpublickeyvirtualmachine_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasSshPublicKeyVirtualMachine\IaasSshPublicKeyVirtualMachineRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaassshpublickeyvirtualmachine_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasSshPublicKeyVirtualMachine::first();

            event(new \NextDeveloper\IAAS\Events\IaasSshPublicKeyVirtualMachine\IaasSshPublicKeyVirtualMachineRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaassshpublickeyvirtualmachine_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasSshPublicKeyVirtualMachine::first();

            event(new \NextDeveloper\IAAS\Events\IaasSshPublicKeyVirtualMachine\IaasSshPublicKeyVirtualMachineCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaassshpublickeyvirtualmachine_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasSshPublicKeyVirtualMachine::first();

            event(new \NextDeveloper\IAAS\Events\IaasSshPublicKeyVirtualMachine\IaasSshPublicKeyVirtualMachineCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaassshpublickeyvirtualmachine_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasSshPublicKeyVirtualMachine::first();

            event(new \NextDeveloper\IAAS\Events\IaasSshPublicKeyVirtualMachine\IaasSshPublicKeyVirtualMachineSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaassshpublickeyvirtualmachine_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasSshPublicKeyVirtualMachine::first();

            event(new \NextDeveloper\IAAS\Events\IaasSshPublicKeyVirtualMachine\IaasSshPublicKeyVirtualMachineSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaassshpublickeyvirtualmachine_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasSshPublicKeyVirtualMachine::first();

            event(new \NextDeveloper\IAAS\Events\IaasSshPublicKeyVirtualMachine\IaasSshPublicKeyVirtualMachineUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaassshpublickeyvirtualmachine_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasSshPublicKeyVirtualMachine::first();

            event(new \NextDeveloper\IAAS\Events\IaasSshPublicKeyVirtualMachine\IaasSshPublicKeyVirtualMachineUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaassshpublickeyvirtualmachine_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasSshPublicKeyVirtualMachine::first();

            event(new \NextDeveloper\IAAS\Events\IaasSshPublicKeyVirtualMachine\IaasSshPublicKeyVirtualMachineDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaassshpublickeyvirtualmachine_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasSshPublicKeyVirtualMachine::first();

            event(new \NextDeveloper\IAAS\Events\IaasSshPublicKeyVirtualMachine\IaasSshPublicKeyVirtualMachineDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaassshpublickeyvirtualmachine_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasSshPublicKeyVirtualMachine::first();

            event(new \NextDeveloper\IAAS\Events\IaasSshPublicKeyVirtualMachine\IaasSshPublicKeyVirtualMachineRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaassshpublickeyvirtualmachine_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasSshPublicKeyVirtualMachine::first();

            event(new \NextDeveloper\IAAS\Events\IaasSshPublicKeyVirtualMachine\IaasSshPublicKeyVirtualMachineRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaassshpublickeyvirtualmachine_event_deployed_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deployed_atStart'  =>  now()
                ]
            );

            $filter = new IaasSshPublicKeyVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasSshPublicKeyVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaassshpublickeyvirtualmachine_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasSshPublicKeyVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasSshPublicKeyVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaassshpublickeyvirtualmachine_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasSshPublicKeyVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasSshPublicKeyVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaassshpublickeyvirtualmachine_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasSshPublicKeyVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasSshPublicKeyVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaassshpublickeyvirtualmachine_event_deployed_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deployed_atEnd'  =>  now()
                ]
            );

            $filter = new IaasSshPublicKeyVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasSshPublicKeyVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaassshpublickeyvirtualmachine_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasSshPublicKeyVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasSshPublicKeyVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaassshpublickeyvirtualmachine_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasSshPublicKeyVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasSshPublicKeyVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaassshpublickeyvirtualmachine_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasSshPublicKeyVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasSshPublicKeyVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaassshpublickeyvirtualmachine_event_deployed_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deployed_atStart'  =>  now(),
                'deployed_atEnd'  =>  now()
                ]
            );

            $filter = new IaasSshPublicKeyVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasSshPublicKeyVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaassshpublickeyvirtualmachine_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasSshPublicKeyVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasSshPublicKeyVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaassshpublickeyvirtualmachine_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasSshPublicKeyVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasSshPublicKeyVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaassshpublickeyvirtualmachine_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasSshPublicKeyVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasSshPublicKeyVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}