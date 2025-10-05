<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasBackupScheduleQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasBackupScheduleService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasBackupScheduleTestTraits
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

    public function test_http_iaasbackupschedule_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasbackupschedule',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasbackupschedule_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasbackupschedule', [
            'form_params'   =>  [
                'schedule_type'  =>  'a',
                'day_of_month'  =>  '1',
                'day_of_week'  =>  '1',
                    'time_of_day'  =>  now(),
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
    public function test_iaasbackupschedule_model_get()
    {
        $result = AbstractIaasBackupScheduleService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasbackupschedule_get_all()
    {
        $result = AbstractIaasBackupScheduleService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasbackupschedule_get_paginated()
    {
        $result = AbstractIaasBackupScheduleService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasbackupschedule_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupSchedule\IaasBackupScheduleRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupschedule_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupSchedule\IaasBackupScheduleCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupschedule_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupSchedule\IaasBackupScheduleCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupschedule_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupSchedule\IaasBackupScheduleSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupschedule_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupSchedule\IaasBackupScheduleSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupschedule_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupSchedule\IaasBackupScheduleUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupschedule_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupSchedule\IaasBackupScheduleUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupschedule_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupSchedule\IaasBackupScheduleDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupschedule_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupSchedule\IaasBackupScheduleDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupschedule_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupSchedule\IaasBackupScheduleRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupschedule_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupSchedule\IaasBackupScheduleRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupschedule_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupSchedule::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupSchedule\IaasBackupScheduleRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupschedule_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupSchedule::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupSchedule\IaasBackupScheduleCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupschedule_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupSchedule::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupSchedule\IaasBackupScheduleCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupschedule_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupSchedule::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupSchedule\IaasBackupScheduleSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupschedule_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupSchedule::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupSchedule\IaasBackupScheduleSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupschedule_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupSchedule::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupSchedule\IaasBackupScheduleUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupschedule_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupSchedule::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupSchedule\IaasBackupScheduleUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupschedule_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupSchedule::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupSchedule\IaasBackupScheduleDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupschedule_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupSchedule::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupSchedule\IaasBackupScheduleDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupschedule_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupSchedule::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupSchedule\IaasBackupScheduleRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupschedule_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupSchedule::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupSchedule\IaasBackupScheduleRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupschedule_event_schedule_type_filter()
    {
        try {
            $request = new Request(
                [
                'schedule_type'  =>  'a'
                ]
            );

            $filter = new IaasBackupScheduleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupSchedule::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupschedule_event_day_of_month_filter()
    {
        try {
            $request = new Request(
                [
                'day_of_month'  =>  '1'
                ]
            );

            $filter = new IaasBackupScheduleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupSchedule::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupschedule_event_day_of_week_filter()
    {
        try {
            $request = new Request(
                [
                'day_of_week'  =>  '1'
                ]
            );

            $filter = new IaasBackupScheduleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupSchedule::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupschedule_event_time_of_day_filter_start()
    {
        try {
            $request = new Request(
                [
                'time_of_dayStart'  =>  now()
                ]
            );

            $filter = new IaasBackupScheduleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupSchedule::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupschedule_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasBackupScheduleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupSchedule::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupschedule_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasBackupScheduleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupSchedule::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupschedule_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasBackupScheduleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupSchedule::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupschedule_event_time_of_day_filter_end()
    {
        try {
            $request = new Request(
                [
                'time_of_dayEnd'  =>  now()
                ]
            );

            $filter = new IaasBackupScheduleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupSchedule::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupschedule_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasBackupScheduleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupSchedule::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupschedule_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasBackupScheduleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupSchedule::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupschedule_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasBackupScheduleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupSchedule::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupschedule_event_time_of_day_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'time_of_dayStart'  =>  now(),
                'time_of_dayEnd'  =>  now()
                ]
            );

            $filter = new IaasBackupScheduleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupSchedule::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupschedule_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasBackupScheduleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupSchedule::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupschedule_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasBackupScheduleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupSchedule::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupschedule_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasBackupScheduleQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupSchedule::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}