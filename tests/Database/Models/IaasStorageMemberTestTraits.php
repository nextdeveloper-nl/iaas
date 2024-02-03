<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasStorageMemberQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasStorageMemberService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasStorageMemberTestTraits
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

    public function test_http_iaasstoragemember_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasstoragemember',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasstoragemember_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasstoragemember', [
            'form_params'   =>  [
                'name'  =>  'a',
                'hostname'  =>  'a',
                'ip_addr'  =>  'a',
                'local_ip_addr'  =>  'a',
                'total_socket'  =>  '1',
                'total_cpu'  =>  '1',
                'total_ram'  =>  '1',
                'total_disk'  =>  '1',
                'used_disk'  =>  '1',
                'benchmark_score'  =>  '1',
                    'up_since'  =>  now(),
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
    public function test_iaasstoragemember_model_get()
    {
        $result = AbstractIaasStorageMemberService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasstoragemember_get_all()
    {
        $result = AbstractIaasStorageMemberService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasstoragemember_get_paginated()
    {
        $result = AbstractIaasStorageMemberService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasstoragemember_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMember\IaasStorageMemberRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragemember_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMember\IaasStorageMemberCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragemember_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMember\IaasStorageMemberCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragemember_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMember\IaasStorageMemberSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragemember_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMember\IaasStorageMemberSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragemember_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMember\IaasStorageMemberUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragemember_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMember\IaasStorageMemberUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragemember_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMember\IaasStorageMemberDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragemember_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMember\IaasStorageMemberDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragemember_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMember\IaasStorageMemberRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragemember_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMember\IaasStorageMemberRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragemember_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMember\IaasStorageMemberRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragemember_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMember\IaasStorageMemberCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragemember_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMember\IaasStorageMemberCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragemember_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMember\IaasStorageMemberSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragemember_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMember\IaasStorageMemberSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragemember_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMember\IaasStorageMemberUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragemember_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMember\IaasStorageMemberUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragemember_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMember\IaasStorageMemberDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragemember_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMember\IaasStorageMemberDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragemember_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMember\IaasStorageMemberRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragemember_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMember\IaasStorageMemberRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragemember_event_name_filter()
    {
        try {
            $request = new Request(
                [
                'name'  =>  'a'
                ]
            );

            $filter = new IaasStorageMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragemember_event_hostname_filter()
    {
        try {
            $request = new Request(
                [
                'hostname'  =>  'a'
                ]
            );

            $filter = new IaasStorageMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragemember_event_ip_addr_filter()
    {
        try {
            $request = new Request(
                [
                'ip_addr'  =>  'a'
                ]
            );

            $filter = new IaasStorageMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragemember_event_local_ip_addr_filter()
    {
        try {
            $request = new Request(
                [
                'local_ip_addr'  =>  'a'
                ]
            );

            $filter = new IaasStorageMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragemember_event_total_socket_filter()
    {
        try {
            $request = new Request(
                [
                'total_socket'  =>  '1'
                ]
            );

            $filter = new IaasStorageMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragemember_event_total_cpu_filter()
    {
        try {
            $request = new Request(
                [
                'total_cpu'  =>  '1'
                ]
            );

            $filter = new IaasStorageMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragemember_event_total_ram_filter()
    {
        try {
            $request = new Request(
                [
                'total_ram'  =>  '1'
                ]
            );

            $filter = new IaasStorageMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragemember_event_total_disk_filter()
    {
        try {
            $request = new Request(
                [
                'total_disk'  =>  '1'
                ]
            );

            $filter = new IaasStorageMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragemember_event_used_disk_filter()
    {
        try {
            $request = new Request(
                [
                'used_disk'  =>  '1'
                ]
            );

            $filter = new IaasStorageMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragemember_event_benchmark_score_filter()
    {
        try {
            $request = new Request(
                [
                'benchmark_score'  =>  '1'
                ]
            );

            $filter = new IaasStorageMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragemember_event_up_since_filter_start()
    {
        try {
            $request = new Request(
                [
                'up_sinceStart'  =>  now()
                ]
            );

            $filter = new IaasStorageMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragemember_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasStorageMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragemember_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasStorageMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragemember_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasStorageMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragemember_event_up_since_filter_end()
    {
        try {
            $request = new Request(
                [
                'up_sinceEnd'  =>  now()
                ]
            );

            $filter = new IaasStorageMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragemember_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStorageMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragemember_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStorageMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragemember_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStorageMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragemember_event_up_since_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'up_sinceStart'  =>  now(),
                'up_sinceEnd'  =>  now()
                ]
            );

            $filter = new IaasStorageMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragemember_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStorageMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragemember_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStorageMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragemember_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStorageMemberQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMember::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}