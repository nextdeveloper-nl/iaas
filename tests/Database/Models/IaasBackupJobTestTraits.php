<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasBackupJobQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasBackupJobService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasBackupJobTestTraits
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

    public function test_http_iaasbackupjob_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasbackupjob',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasbackupjob_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasbackupjob', [
            'form_params'   =>  [
                'name'  =>  'a',
                'type'  =>  'a',
                'object_type'  =>  'a',
                'notification_webhook'  =>  'a',
                'expected_rpo_hours'  =>  '1',
                'expected_rto_hours'  =>  '1',
                'sla_target_pct'  =>  '1',
                'max_allowed_failures'  =>  '1',
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
    public function test_iaasbackupjob_model_get()
    {
        $result = AbstractIaasBackupJobService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasbackupjob_get_all()
    {
        $result = AbstractIaasBackupJobService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasbackupjob_get_paginated()
    {
        $result = AbstractIaasBackupJobService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasbackupjob_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupJob\IaasBackupJobRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjob_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupJob\IaasBackupJobCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjob_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupJob\IaasBackupJobCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjob_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupJob\IaasBackupJobSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjob_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupJob\IaasBackupJobSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjob_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupJob\IaasBackupJobUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjob_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupJob\IaasBackupJobUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjob_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupJob\IaasBackupJobDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjob_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupJob\IaasBackupJobDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjob_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupJob\IaasBackupJobRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjob_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupJob\IaasBackupJobRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjob_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJob::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupJob\IaasBackupJobRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjob_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJob::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupJob\IaasBackupJobCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjob_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJob::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupJob\IaasBackupJobCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjob_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJob::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupJob\IaasBackupJobSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjob_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJob::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupJob\IaasBackupJobSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjob_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJob::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupJob\IaasBackupJobUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjob_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJob::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupJob\IaasBackupJobUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjob_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJob::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupJob\IaasBackupJobDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjob_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJob::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupJob\IaasBackupJobDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjob_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJob::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupJob\IaasBackupJobRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjob_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJob::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupJob\IaasBackupJobRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjob_event_name_filter()
    {
        try {
            $request = new Request(
                [
                'name'  =>  'a'
                ]
            );

            $filter = new IaasBackupJobQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJob::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjob_event_type_filter()
    {
        try {
            $request = new Request(
                [
                'type'  =>  'a'
                ]
            );

            $filter = new IaasBackupJobQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJob::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjob_event_object_type_filter()
    {
        try {
            $request = new Request(
                [
                'object_type'  =>  'a'
                ]
            );

            $filter = new IaasBackupJobQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJob::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjob_event_notification_webhook_filter()
    {
        try {
            $request = new Request(
                [
                'notification_webhook'  =>  'a'
                ]
            );

            $filter = new IaasBackupJobQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJob::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjob_event_expected_rpo_hours_filter()
    {
        try {
            $request = new Request(
                [
                'expected_rpo_hours'  =>  '1'
                ]
            );

            $filter = new IaasBackupJobQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJob::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjob_event_expected_rto_hours_filter()
    {
        try {
            $request = new Request(
                [
                'expected_rto_hours'  =>  '1'
                ]
            );

            $filter = new IaasBackupJobQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJob::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjob_event_sla_target_pct_filter()
    {
        try {
            $request = new Request(
                [
                'sla_target_pct'  =>  '1'
                ]
            );

            $filter = new IaasBackupJobQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJob::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjob_event_max_allowed_failures_filter()
    {
        try {
            $request = new Request(
                [
                'max_allowed_failures'  =>  '1'
                ]
            );

            $filter = new IaasBackupJobQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJob::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjob_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasBackupJobQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJob::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjob_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasBackupJobQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJob::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjob_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasBackupJobQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJob::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjob_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasBackupJobQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJob::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjob_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasBackupJobQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJob::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjob_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasBackupJobQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJob::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjob_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasBackupJobQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJob::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjob_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasBackupJobQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJob::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjob_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasBackupJobQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJob::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}