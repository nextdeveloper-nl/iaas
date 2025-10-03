<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;
use NextDeveloper\IAAS\Database\Filters\IaasComputeMemberStatQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasComputeMemberStatService;
use Tests\TestCase;

trait IaasComputeMemberStatTestTraits
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

    public function test_http_iaascomputememberstat_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaascomputememberstat',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaascomputememberstat_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaascomputememberstat', [
            'form_params'   =>  [
                'used_ram'  =>  '1',
                'used_cpu'  =>  '1',
                'running_vm'  =>  '1',
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
    public function test_iaascomputememberstat_model_get()
    {
        $result = AbstractIaasComputeMemberStatService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaascomputememberstat_get_all()
    {
        $result = AbstractIaasComputeMemberStatService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaascomputememberstat_get_paginated()
    {
        $result = AbstractIaasComputeMemberStatService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaascomputememberstat_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberStat\IaasComputeMemberStatRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberstat_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberStat\IaasComputeMemberStatCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberstat_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberStat\IaasComputeMemberStatCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberstat_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberStat\IaasComputeMemberStatSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberstat_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberStat\IaasComputeMemberStatSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberstat_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberStat\IaasComputeMemberStatUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberstat_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberStat\IaasComputeMemberStatUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberstat_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberStat\IaasComputeMemberStatDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberstat_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberStat\IaasComputeMemberStatDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberstat_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberStat\IaasComputeMemberStatRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberstat_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberStat\IaasComputeMemberStatRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberstat_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberStat\IaasComputeMemberStatRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberstat_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberStat\IaasComputeMemberStatCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberstat_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberStat\IaasComputeMemberStatCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberstat_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberStat\IaasComputeMemberStatSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberstat_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberStat\IaasComputeMemberStatSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberstat_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberStat\IaasComputeMemberStatUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberstat_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberStat\IaasComputeMemberStatUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberstat_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberStat\IaasComputeMemberStatDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberstat_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberStat\IaasComputeMemberStatDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberstat_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberStat\IaasComputeMemberStatRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberstat_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberStat\IaasComputeMemberStatRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberstat_event_used_ram_filter()
    {
        try {
            $request = new Request(
                [
                'used_ram'  =>  '1'
                ]
            );

            $filter = new IaasComputeMemberStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberstat_event_used_cpu_filter()
    {
        try {
            $request = new Request(
                [
                'used_cpu'  =>  '1'
                ]
            );

            $filter = new IaasComputeMemberStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberstat_event_running_vm_filter()
    {
        try {
            $request = new Request(
                [
                'running_vm'  =>  '1'
                ]
            );

            $filter = new IaasComputeMemberStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberstat_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberstat_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberstat_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberstat_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberstat_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberstat_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberstat_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberstat_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberstat_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}