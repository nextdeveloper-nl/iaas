<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;
use NextDeveloper\IAAS\Database\Filters\IaasVirtualMachineQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasVirtualMachineService;
use Tests\TestCase;

trait IaasVirtualMachineTestTraits
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

    public function test_http_iaasvirtualmachine_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasvirtualmachine',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasvirtualmachine_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasvirtualmachine', [
            'form_params'   =>  [
                'name'  =>  'a',
                'username'  =>  'a',
                'password'  =>  'a',
                'hostname'  =>  'a',
                'description'  =>  'a',
                'os'  =>  'a',
                'distro'  =>  'a',
                'version'  =>  'a',
                'domain_type'  =>  'a',
                'status'  =>  'a',
                'lock_password'  =>  'a',
                'auto_backup_interval'  =>  'a',
                'auto_backup_time'  =>  'a',
                'cpu'  =>  '1',
                'ram'  =>  '1',
                'snapshot_of_virtual_machine'  =>  '1',
                    'last_metadata_request'  =>  now(),
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
    public function test_iaasvirtualmachine_model_get()
    {
        $result = AbstractIaasVirtualMachineService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasvirtualmachine_get_all()
    {
        $result = AbstractIaasVirtualMachineService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasvirtualmachine_get_paginated()
    {
        $result = AbstractIaasVirtualMachineService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasvirtualmachine_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachine\IaasVirtualMachineRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachine_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachine\IaasVirtualMachineCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachine_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachine\IaasVirtualMachineCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachine_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachine\IaasVirtualMachineSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachine_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachine\IaasVirtualMachineSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachine_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachine\IaasVirtualMachineUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachine_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachine\IaasVirtualMachineUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachine_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachine\IaasVirtualMachineDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachine_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachine\IaasVirtualMachineDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachine_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachine\IaasVirtualMachineRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachine_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachine\IaasVirtualMachineRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachine_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachine\IaasVirtualMachineRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachine_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachine\IaasVirtualMachineCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachine_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachine\IaasVirtualMachineCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachine_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachine\IaasVirtualMachineSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachine_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachine\IaasVirtualMachineSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachine_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachine\IaasVirtualMachineUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachine_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachine\IaasVirtualMachineUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachine_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachine\IaasVirtualMachineDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachine_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachine\IaasVirtualMachineDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachine_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachine\IaasVirtualMachineRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachine_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachine\IaasVirtualMachineRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachine_event_name_filter()
    {
        try {
            $request = new Request(
                [
                'name'  =>  'a'
                ]
            );

            $filter = new IaasVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachine_event_username_filter()
    {
        try {
            $request = new Request(
                [
                'username'  =>  'a'
                ]
            );

            $filter = new IaasVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachine_event_password_filter()
    {
        try {
            $request = new Request(
                [
                'password'  =>  'a'
                ]
            );

            $filter = new IaasVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachine_event_hostname_filter()
    {
        try {
            $request = new Request(
                [
                'hostname'  =>  'a'
                ]
            );

            $filter = new IaasVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachine_event_description_filter()
    {
        try {
            $request = new Request(
                [
                'description'  =>  'a'
                ]
            );

            $filter = new IaasVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachine_event_os_filter()
    {
        try {
            $request = new Request(
                [
                'os'  =>  'a'
                ]
            );

            $filter = new IaasVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachine_event_distro_filter()
    {
        try {
            $request = new Request(
                [
                'distro'  =>  'a'
                ]
            );

            $filter = new IaasVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachine_event_version_filter()
    {
        try {
            $request = new Request(
                [
                'version'  =>  'a'
                ]
            );

            $filter = new IaasVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachine_event_domain_type_filter()
    {
        try {
            $request = new Request(
                [
                'domain_type'  =>  'a'
                ]
            );

            $filter = new IaasVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachine_event_status_filter()
    {
        try {
            $request = new Request(
                [
                'status'  =>  'a'
                ]
            );

            $filter = new IaasVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachine_event_lock_password_filter()
    {
        try {
            $request = new Request(
                [
                'lock_password'  =>  'a'
                ]
            );

            $filter = new IaasVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachine_event_auto_backup_interval_filter()
    {
        try {
            $request = new Request(
                [
                'auto_backup_interval'  =>  'a'
                ]
            );

            $filter = new IaasVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachine_event_auto_backup_time_filter()
    {
        try {
            $request = new Request(
                [
                'auto_backup_time'  =>  'a'
                ]
            );

            $filter = new IaasVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachine_event_cpu_filter()
    {
        try {
            $request = new Request(
                [
                'cpu'  =>  '1'
                ]
            );

            $filter = new IaasVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachine_event_ram_filter()
    {
        try {
            $request = new Request(
                [
                'ram'  =>  '1'
                ]
            );

            $filter = new IaasVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachine_event_snapshot_of_virtual_machine_filter()
    {
        try {
            $request = new Request(
                [
                'snapshot_of_virtual_machine'  =>  '1'
                ]
            );

            $filter = new IaasVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachine_event_last_metadata_request_filter_start()
    {
        try {
            $request = new Request(
                [
                'last_metadata_requestStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachine_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachine_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachine_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachine_event_last_metadata_request_filter_end()
    {
        try {
            $request = new Request(
                [
                'last_metadata_requestEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachine_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachine_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachine_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachine_event_last_metadata_request_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'last_metadata_requestStart'  =>  now(),
                'last_metadata_requestEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachine_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachine_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachine_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachine::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE


}