<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasVirtualMachineMigrationQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasVirtualMachineMigrationService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasVirtualMachineMigrationTestTraits
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

    public function test_http_iaasvirtualmachinemigration_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasvirtualmachinemigration',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasvirtualmachinemigration_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasvirtualmachinemigration', [
            'form_params'   =>  [
                'step_message'  =>  'a',
                'error_message'  =>  'a',
                'progress'  =>  '1',
                    'started_at'  =>  now(),
                    'completed_at'  =>  now(),
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
    public function test_iaasvirtualmachinemigration_model_get()
    {
        $result = AbstractIaasVirtualMachineMigrationService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasvirtualmachinemigration_get_all()
    {
        $result = AbstractIaasVirtualMachineMigrationService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasvirtualmachinemigration_get_paginated()
    {
        $result = AbstractIaasVirtualMachineMigrationService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasvirtualmachinemigration_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineMigration\IaasVirtualMachineMigrationRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinemigration_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineMigration\IaasVirtualMachineMigrationCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinemigration_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineMigration\IaasVirtualMachineMigrationCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinemigration_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineMigration\IaasVirtualMachineMigrationSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinemigration_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineMigration\IaasVirtualMachineMigrationSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinemigration_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineMigration\IaasVirtualMachineMigrationUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinemigration_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineMigration\IaasVirtualMachineMigrationUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinemigration_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineMigration\IaasVirtualMachineMigrationDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinemigration_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineMigration\IaasVirtualMachineMigrationDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinemigration_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineMigration\IaasVirtualMachineMigrationRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinemigration_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineMigration\IaasVirtualMachineMigrationRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinemigration_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineMigration::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineMigration\IaasVirtualMachineMigrationRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinemigration_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineMigration::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineMigration\IaasVirtualMachineMigrationCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinemigration_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineMigration::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineMigration\IaasVirtualMachineMigrationCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinemigration_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineMigration::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineMigration\IaasVirtualMachineMigrationSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinemigration_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineMigration::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineMigration\IaasVirtualMachineMigrationSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinemigration_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineMigration::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineMigration\IaasVirtualMachineMigrationUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinemigration_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineMigration::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineMigration\IaasVirtualMachineMigrationUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinemigration_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineMigration::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineMigration\IaasVirtualMachineMigrationDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinemigration_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineMigration::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineMigration\IaasVirtualMachineMigrationDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinemigration_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineMigration::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineMigration\IaasVirtualMachineMigrationRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinemigration_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineMigration::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineMigration\IaasVirtualMachineMigrationRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinemigration_event_step_message_filter()
    {
        try {
            $request = new Request(
                [
                'step_message'  =>  'a'
                ]
            );

            $filter = new IaasVirtualMachineMigrationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineMigration::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinemigration_event_error_message_filter()
    {
        try {
            $request = new Request(
                [
                'error_message'  =>  'a'
                ]
            );

            $filter = new IaasVirtualMachineMigrationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineMigration::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinemigration_event_progress_filter()
    {
        try {
            $request = new Request(
                [
                'progress'  =>  '1'
                ]
            );

            $filter = new IaasVirtualMachineMigrationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineMigration::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinemigration_event_started_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'started_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineMigrationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineMigration::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinemigration_event_completed_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'completed_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineMigrationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineMigration::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinemigration_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineMigrationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineMigration::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinemigration_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineMigrationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineMigration::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinemigration_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineMigrationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineMigration::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinemigration_event_started_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'started_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineMigrationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineMigration::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinemigration_event_completed_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'completed_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineMigrationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineMigration::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinemigration_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineMigrationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineMigration::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinemigration_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineMigrationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineMigration::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinemigration_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineMigrationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineMigration::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinemigration_event_started_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'started_atStart'  =>  now(),
                'started_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineMigrationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineMigration::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinemigration_event_completed_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'completed_atStart'  =>  now(),
                'completed_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineMigrationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineMigration::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinemigration_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineMigrationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineMigration::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinemigration_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineMigrationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineMigration::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinemigration_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineMigrationQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineMigration::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}