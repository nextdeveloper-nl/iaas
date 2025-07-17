<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;
use NextDeveloper\IAAS\Database\Filters\IaasStorageMemberStatQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasStorageMemberStatService;

trait IaasStorageMemberStatTestTraits
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

    public function test_http_iaasstoragememberstat_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasstoragememberstat',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasstoragememberstat_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasstoragememberstat', [
            'form_params'   =>  [
                'used_disk'  =>  '1',
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
    public function test_iaasstoragememberstat_model_get()
    {
        $result = AbstractIaasStorageMemberStatService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasstoragememberstat_get_all()
    {
        $result = AbstractIaasStorageMemberStatService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasstoragememberstat_get_paginated()
    {
        $result = AbstractIaasStorageMemberStatService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasstoragememberstat_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberStat\IaasStorageMemberStatRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberstat_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberStat\IaasStorageMemberStatCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberstat_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberStat\IaasStorageMemberStatCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberstat_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberStat\IaasStorageMemberStatSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberstat_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberStat\IaasStorageMemberStatSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberstat_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberStat\IaasStorageMemberStatUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberstat_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberStat\IaasStorageMemberStatUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberstat_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberStat\IaasStorageMemberStatDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberstat_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberStat\IaasStorageMemberStatDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberstat_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberStat\IaasStorageMemberStatRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberstat_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberStat\IaasStorageMemberStatRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragememberstat_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberStat\IaasStorageMemberStatRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberstat_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberStat\IaasStorageMemberStatCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberstat_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberStat\IaasStorageMemberStatCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberstat_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberStat\IaasStorageMemberStatSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberstat_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberStat\IaasStorageMemberStatSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberstat_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberStat\IaasStorageMemberStatUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberstat_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberStat\IaasStorageMemberStatUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberstat_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberStat\IaasStorageMemberStatDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberstat_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberStat\IaasStorageMemberStatDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberstat_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberStat\IaasStorageMemberStatRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasstoragememberstat_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasStorageMemberStat\IaasStorageMemberStatRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragememberstat_event_used_disk_filter()
    {
        try {
            $request = new Request(
                [
                'used_disk'  =>  '1'
                ]
            );

            $filter = new IaasStorageMemberStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragememberstat_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasStorageMemberStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragememberstat_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasStorageMemberStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragememberstat_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasStorageMemberStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragememberstat_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStorageMemberStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragememberstat_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStorageMemberStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragememberstat_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStorageMemberStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragememberstat_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStorageMemberStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragememberstat_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStorageMemberStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasstoragememberstat_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasStorageMemberStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasStorageMemberStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}
