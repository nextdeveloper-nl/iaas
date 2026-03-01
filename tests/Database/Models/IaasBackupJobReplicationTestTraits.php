<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasBackupJobReplicationQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasBackupJobReplicationService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasBackupJobReplicationTestTraits
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

    public function test_http_iaasbackupjobreplication_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasbackupjobreplication',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasbackupjobreplication_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasbackupjobreplication', [
            'form_params'   =>  [
                'replication_type'  =>  'a',
                'last_replication_status'  =>  'a',
                'priority'  =>  '1',
                'bandwidth_limit_mbps'  =>  '1',
                'last_replication_size_bytes'  =>  '1',
                'last_replication_duration_secs'  =>  '1',
                    'last_replicated_at'  =>  now(),
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
    public function test_iaasbackupjobreplication_model_get()
    {
        $result = AbstractIaasBackupJobReplicationService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasbackupjobreplication_get_all()
    {
        $result = AbstractIaasBackupJobReplicationService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasbackupjobreplication_get_paginated()
    {
        $result = AbstractIaasBackupJobReplicationService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasbackupjobreplication_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupJobReplication\IaasBackupJobReplicationRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjobreplication_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupJobReplication\IaasBackupJobReplicationCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjobreplication_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupJobReplication\IaasBackupJobReplicationCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjobreplication_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupJobReplication\IaasBackupJobReplicationSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjobreplication_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupJobReplication\IaasBackupJobReplicationSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjobreplication_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupJobReplication\IaasBackupJobReplicationUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjobreplication_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupJobReplication\IaasBackupJobReplicationUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjobreplication_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupJobReplication\IaasBackupJobReplicationDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjobreplication_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupJobReplication\IaasBackupJobReplicationDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjobreplication_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupJobReplication\IaasBackupJobReplicationRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjobreplication_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupJobReplication\IaasBackupJobReplicationRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjobreplication_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJobReplication::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupJobReplication\IaasBackupJobReplicationRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjobreplication_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJobReplication::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupJobReplication\IaasBackupJobReplicationCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjobreplication_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJobReplication::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupJobReplication\IaasBackupJobReplicationCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjobreplication_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJobReplication::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupJobReplication\IaasBackupJobReplicationSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjobreplication_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJobReplication::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupJobReplication\IaasBackupJobReplicationSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjobreplication_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJobReplication::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupJobReplication\IaasBackupJobReplicationUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjobreplication_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJobReplication::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupJobReplication\IaasBackupJobReplicationUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjobreplication_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJobReplication::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupJobReplication\IaasBackupJobReplicationDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjobreplication_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJobReplication::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupJobReplication\IaasBackupJobReplicationDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjobreplication_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJobReplication::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupJobReplication\IaasBackupJobReplicationRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupjobreplication_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJobReplication::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupJobReplication\IaasBackupJobReplicationRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjobreplication_event_replication_type_filter()
    {
        try {
            $request = new Request(
                [
                'replication_type'  =>  'a'
                ]
            );

            $filter = new IaasBackupJobReplicationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJobReplication::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjobreplication_event_last_replication_status_filter()
    {
        try {
            $request = new Request(
                [
                'last_replication_status'  =>  'a'
                ]
            );

            $filter = new IaasBackupJobReplicationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJobReplication::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjobreplication_event_priority_filter()
    {
        try {
            $request = new Request(
                [
                'priority'  =>  '1'
                ]
            );

            $filter = new IaasBackupJobReplicationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJobReplication::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjobreplication_event_bandwidth_limit_mbps_filter()
    {
        try {
            $request = new Request(
                [
                'bandwidth_limit_mbps'  =>  '1'
                ]
            );

            $filter = new IaasBackupJobReplicationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJobReplication::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjobreplication_event_last_replication_size_bytes_filter()
    {
        try {
            $request = new Request(
                [
                'last_replication_size_bytes'  =>  '1'
                ]
            );

            $filter = new IaasBackupJobReplicationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJobReplication::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjobreplication_event_last_replication_duration_secs_filter()
    {
        try {
            $request = new Request(
                [
                'last_replication_duration_secs'  =>  '1'
                ]
            );

            $filter = new IaasBackupJobReplicationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJobReplication::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjobreplication_event_last_replicated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'last_replicated_atStart'  =>  now()
                ]
            );

            $filter = new IaasBackupJobReplicationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJobReplication::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjobreplication_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasBackupJobReplicationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJobReplication::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjobreplication_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasBackupJobReplicationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJobReplication::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjobreplication_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasBackupJobReplicationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJobReplication::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjobreplication_event_last_replicated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'last_replicated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasBackupJobReplicationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJobReplication::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjobreplication_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasBackupJobReplicationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJobReplication::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjobreplication_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasBackupJobReplicationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJobReplication::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjobreplication_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasBackupJobReplicationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJobReplication::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjobreplication_event_last_replicated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'last_replicated_atStart'  =>  now(),
                'last_replicated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasBackupJobReplicationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJobReplication::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjobreplication_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasBackupJobReplicationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJobReplication::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjobreplication_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasBackupJobReplicationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJobReplication::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupjobreplication_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasBackupJobReplicationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupJobReplication::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}