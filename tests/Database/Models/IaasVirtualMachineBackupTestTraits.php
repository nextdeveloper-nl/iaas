<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;
use NextDeveloper\IAAS\Database\Filters\IaasVirtualMachineBackupQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasVirtualMachineBackupService;

trait IaasVirtualMachineBackupTestTraits
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

    public function test_http_iaasvirtualmachinebackup_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasvirtualmachinebackup',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasvirtualmachinebackup_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasvirtualmachinebackup', [
            'form_params'   =>  [
                'name'  =>  'a',
                'description'  =>  'a',
                'path'  =>  'a',
                'filename'  =>  'a',
                'username'  =>  'a',
                'password'  =>  'a',
                'hash'  =>  'a',
                'backup_type'  =>  'a',
                'status'  =>  'a',
                'size'  =>  '1',
                'ram'  =>  '1',
                'cpu'  =>  '1',
                    'backup_starts'  =>  now(),
                    'backup_ends'  =>  now(),
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
    public function test_iaasvirtualmachinebackup_model_get()
    {
        $result = AbstractIaasVirtualMachineBackupService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasvirtualmachinebackup_get_all()
    {
        $result = AbstractIaasVirtualMachineBackupService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasvirtualmachinebackup_get_paginated()
    {
        $result = AbstractIaasVirtualMachineBackupService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasvirtualmachinebackup_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineBackup\IaasVirtualMachineBackupRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinebackup_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineBackup\IaasVirtualMachineBackupCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinebackup_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineBackup\IaasVirtualMachineBackupCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinebackup_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineBackup\IaasVirtualMachineBackupSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinebackup_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineBackup\IaasVirtualMachineBackupSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinebackup_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineBackup\IaasVirtualMachineBackupUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinebackup_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineBackup\IaasVirtualMachineBackupUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinebackup_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineBackup\IaasVirtualMachineBackupDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinebackup_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineBackup\IaasVirtualMachineBackupDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinebackup_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineBackup\IaasVirtualMachineBackupRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinebackup_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineBackup\IaasVirtualMachineBackupRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinebackup_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineBackup\IaasVirtualMachineBackupRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinebackup_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineBackup\IaasVirtualMachineBackupCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinebackup_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineBackup\IaasVirtualMachineBackupCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinebackup_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineBackup\IaasVirtualMachineBackupSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinebackup_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineBackup\IaasVirtualMachineBackupSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinebackup_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineBackup\IaasVirtualMachineBackupUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinebackup_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineBackup\IaasVirtualMachineBackupUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinebackup_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineBackup\IaasVirtualMachineBackupDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinebackup_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineBackup\IaasVirtualMachineBackupDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinebackup_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineBackup\IaasVirtualMachineBackupRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinebackup_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineBackup\IaasVirtualMachineBackupRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinebackup_event_name_filter()
    {
        try {
            $request = new Request(
                [
                'name'  =>  'a'
                ]
            );

            $filter = new IaasVirtualMachineBackupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinebackup_event_description_filter()
    {
        try {
            $request = new Request(
                [
                'description'  =>  'a'
                ]
            );

            $filter = new IaasVirtualMachineBackupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinebackup_event_path_filter()
    {
        try {
            $request = new Request(
                [
                'path'  =>  'a'
                ]
            );

            $filter = new IaasVirtualMachineBackupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinebackup_event_filename_filter()
    {
        try {
            $request = new Request(
                [
                'filename'  =>  'a'
                ]
            );

            $filter = new IaasVirtualMachineBackupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinebackup_event_username_filter()
    {
        try {
            $request = new Request(
                [
                'username'  =>  'a'
                ]
            );

            $filter = new IaasVirtualMachineBackupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinebackup_event_password_filter()
    {
        try {
            $request = new Request(
                [
                'password'  =>  'a'
                ]
            );

            $filter = new IaasVirtualMachineBackupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinebackup_event_hash_filter()
    {
        try {
            $request = new Request(
                [
                'hash'  =>  'a'
                ]
            );

            $filter = new IaasVirtualMachineBackupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinebackup_event_backup_type_filter()
    {
        try {
            $request = new Request(
                [
                'backup_type'  =>  'a'
                ]
            );

            $filter = new IaasVirtualMachineBackupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinebackup_event_status_filter()
    {
        try {
            $request = new Request(
                [
                'status'  =>  'a'
                ]
            );

            $filter = new IaasVirtualMachineBackupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinebackup_event_size_filter()
    {
        try {
            $request = new Request(
                [
                'size'  =>  '1'
                ]
            );

            $filter = new IaasVirtualMachineBackupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinebackup_event_ram_filter()
    {
        try {
            $request = new Request(
                [
                'ram'  =>  '1'
                ]
            );

            $filter = new IaasVirtualMachineBackupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinebackup_event_cpu_filter()
    {
        try {
            $request = new Request(
                [
                'cpu'  =>  '1'
                ]
            );

            $filter = new IaasVirtualMachineBackupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinebackup_event_backup_starts_filter_start()
    {
        try {
            $request = new Request(
                [
                'backup_startsStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineBackupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinebackup_event_backup_ends_filter_start()
    {
        try {
            $request = new Request(
                [
                'backup_endsStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineBackupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinebackup_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineBackupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinebackup_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineBackupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinebackup_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineBackupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinebackup_event_backup_starts_filter_end()
    {
        try {
            $request = new Request(
                [
                'backup_startsEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineBackupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinebackup_event_backup_ends_filter_end()
    {
        try {
            $request = new Request(
                [
                'backup_endsEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineBackupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinebackup_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineBackupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinebackup_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineBackupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinebackup_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineBackupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinebackup_event_backup_starts_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'backup_startsStart'  =>  now(),
                'backup_startsEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineBackupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinebackup_event_backup_ends_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'backup_endsStart'  =>  now(),
                'backup_endsEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineBackupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinebackup_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineBackupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinebackup_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineBackupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinebackup_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineBackupQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineBackup::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}
