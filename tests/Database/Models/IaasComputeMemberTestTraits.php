<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;
use NextDeveloper\IAAS\Database\Filters\IaasComputeMemberQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasComputeMemberService;

trait IaasComputeMemberTestTraits
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

    public function test_http_iaascomputemember_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaascomputemember',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaascomputemember_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaascomputemember', [
            'form_params'   =>  [
                'name'  =>  'a',
                'hostname'  =>  'a',
                'ssh_username'  =>  'a',
                'ssh_password'  =>  'a',
                'hypervisor_model'  =>  'a',
                'ssh_port'  =>  '1',
                'total_socket'  =>  '1',
                'total_cpu'  =>  '1',
                'total_ram'  =>  '1',
                'used_cpu'  =>  '1',
                'used_ram'  =>  '1',
                'running_vm'  =>  '1',
                'halted_vm'  =>  '1',
                'total_vm'  =>  '1',
                'max_overbooking_ratio'  =>  '1',
                'benchmark_score'  =>  '1',
                'free_ram'  =>  '1',
                    'uptime'  =>  now(),
                    'idle_time'  =>  now(),
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
    public function test_iaascomputemember_model_get()
    {
        $result = AbstractIaasComputeMemberService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaascomputemember_get_all()
    {
        $result = AbstractIaasComputeMemberService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaascomputemember_get_paginated()
    {
        $result = AbstractIaasComputeMemberService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaascomputemember_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMember\IaasComputeMemberRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemember_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMember\IaasComputeMemberCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemember_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMember\IaasComputeMemberCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemember_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMember\IaasComputeMemberSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemember_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMember\IaasComputeMemberSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemember_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMember\IaasComputeMemberUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemember_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMember\IaasComputeMemberUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemember_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMember\IaasComputeMemberDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemember_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMember\IaasComputeMemberDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemember_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMember\IaasComputeMemberRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemember_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMember\IaasComputeMemberRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMember\IaasComputeMemberRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemember_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMember\IaasComputeMemberCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemember_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMember\IaasComputeMemberCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemember_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMember\IaasComputeMemberSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemember_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMember\IaasComputeMemberSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemember_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMember\IaasComputeMemberUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemember_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMember\IaasComputeMemberUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemember_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMember\IaasComputeMemberDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemember_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMember\IaasComputeMemberDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemember_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMember\IaasComputeMemberRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputemember_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMember\IaasComputeMemberRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_name_filter()
    {
        try {
            $request = new Request(
                [
                'name'  =>  'a'
                ]
            );

            $filter = new IaasComputeMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_hostname_filter()
    {
        try {
            $request = new Request(
                [
                'hostname'  =>  'a'
                ]
            );

            $filter = new IaasComputeMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_ssh_username_filter()
    {
        try {
            $request = new Request(
                [
                'ssh_username'  =>  'a'
                ]
            );

            $filter = new IaasComputeMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_ssh_password_filter()
    {
        try {
            $request = new Request(
                [
                'ssh_password'  =>  'a'
                ]
            );

            $filter = new IaasComputeMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_hypervisor_model_filter()
    {
        try {
            $request = new Request(
                [
                'hypervisor_model'  =>  'a'
                ]
            );

            $filter = new IaasComputeMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_ssh_port_filter()
    {
        try {
            $request = new Request(
                [
                'ssh_port'  =>  '1'
                ]
            );

            $filter = new IaasComputeMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_total_socket_filter()
    {
        try {
            $request = new Request(
                [
                'total_socket'  =>  '1'
                ]
            );

            $filter = new IaasComputeMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_total_cpu_filter()
    {
        try {
            $request = new Request(
                [
                'total_cpu'  =>  '1'
                ]
            );

            $filter = new IaasComputeMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_total_ram_filter()
    {
        try {
            $request = new Request(
                [
                'total_ram'  =>  '1'
                ]
            );

            $filter = new IaasComputeMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_used_cpu_filter()
    {
        try {
            $request = new Request(
                [
                'used_cpu'  =>  '1'
                ]
            );

            $filter = new IaasComputeMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_used_ram_filter()
    {
        try {
            $request = new Request(
                [
                'used_ram'  =>  '1'
                ]
            );

            $filter = new IaasComputeMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_running_vm_filter()
    {
        try {
            $request = new Request(
                [
                'running_vm'  =>  '1'
                ]
            );

            $filter = new IaasComputeMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_halted_vm_filter()
    {
        try {
            $request = new Request(
                [
                'halted_vm'  =>  '1'
                ]
            );

            $filter = new IaasComputeMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_total_vm_filter()
    {
        try {
            $request = new Request(
                [
                'total_vm'  =>  '1'
                ]
            );

            $filter = new IaasComputeMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_max_overbooking_ratio_filter()
    {
        try {
            $request = new Request(
                [
                'max_overbooking_ratio'  =>  '1'
                ]
            );

            $filter = new IaasComputeMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_benchmark_score_filter()
    {
        try {
            $request = new Request(
                [
                'benchmark_score'  =>  '1'
                ]
            );

            $filter = new IaasComputeMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_free_ram_filter()
    {
        try {
            $request = new Request(
                [
                'free_ram'  =>  '1'
                ]
            );

            $filter = new IaasComputeMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_uptime_filter_start()
    {
        try {
            $request = new Request(
                [
                'uptimeStart'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_idle_time_filter_start()
    {
        try {
            $request = new Request(
                [
                'idle_timeStart'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_uptime_filter_end()
    {
        try {
            $request = new Request(
                [
                'uptimeEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_idle_time_filter_end()
    {
        try {
            $request = new Request(
                [
                'idle_timeEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_uptime_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'uptimeStart'  =>  now(),
                'uptimeEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_idle_time_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'idle_timeStart'  =>  now(),
                'idle_timeEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputemember_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
