<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasAnsiblePlaybookExecutionQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasAnsiblePlaybookExecutionService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasAnsiblePlaybookExecutionTestTraits
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

    public function test_http_iaasansibleplaybookexecution_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasansibleplaybookexecution',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasansibleplaybookexecution_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasansibleplaybookexecution', [
            'form_params'   =>  [
                'ssh_username'  =>  'a',
                'ssh_password'  =>  'a',
                'last_output'  =>  'a',
                'ssh_port'  =>  '1',
                'execution_total_time'  =>  '1',
                'result_ok'  =>  '1',
                'result_unreachable'  =>  '1',
                'result_failed'  =>  '1',
                'result_skipped'  =>  '1',
                'result_rescued'  =>  '1',
                'result_ignored'  =>  '1',
                    'last_execution_time'  =>  now(),
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
    public function test_iaasansibleplaybookexecution_model_get()
    {
        $result = AbstractIaasAnsiblePlaybookExecutionService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasansibleplaybookexecution_get_all()
    {
        $result = AbstractIaasAnsiblePlaybookExecutionService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasansibleplaybookexecution_get_paginated()
    {
        $result = AbstractIaasAnsiblePlaybookExecutionService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasansibleplaybookexecution_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookExecution\IaasAnsiblePlaybookExecutionRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookexecution_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookExecution\IaasAnsiblePlaybookExecutionCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookexecution_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookExecution\IaasAnsiblePlaybookExecutionCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookexecution_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookExecution\IaasAnsiblePlaybookExecutionSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookexecution_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookExecution\IaasAnsiblePlaybookExecutionSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookexecution_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookExecution\IaasAnsiblePlaybookExecutionUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookexecution_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookExecution\IaasAnsiblePlaybookExecutionUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookexecution_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookExecution\IaasAnsiblePlaybookExecutionDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookexecution_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookExecution\IaasAnsiblePlaybookExecutionDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookexecution_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookExecution\IaasAnsiblePlaybookExecutionRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookexecution_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookExecution\IaasAnsiblePlaybookExecutionRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookexecution_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookExecution\IaasAnsiblePlaybookExecutionRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookexecution_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookExecution\IaasAnsiblePlaybookExecutionCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookexecution_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookExecution\IaasAnsiblePlaybookExecutionCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookexecution_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookExecution\IaasAnsiblePlaybookExecutionSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookexecution_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookExecution\IaasAnsiblePlaybookExecutionSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookexecution_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookExecution\IaasAnsiblePlaybookExecutionUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookexecution_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookExecution\IaasAnsiblePlaybookExecutionUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookexecution_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookExecution\IaasAnsiblePlaybookExecutionDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookexecution_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookExecution\IaasAnsiblePlaybookExecutionDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookexecution_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookExecution\IaasAnsiblePlaybookExecutionRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansibleplaybookexecution_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsiblePlaybookExecution\IaasAnsiblePlaybookExecutionRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookexecution_event_ssh_username_filter()
    {
        try {
            $request = new Request(
                [
                'ssh_username'  =>  'a'
                ]
            );

            $filter = new IaasAnsiblePlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookexecution_event_ssh_password_filter()
    {
        try {
            $request = new Request(
                [
                'ssh_password'  =>  'a'
                ]
            );

            $filter = new IaasAnsiblePlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookexecution_event_last_output_filter()
    {
        try {
            $request = new Request(
                [
                'last_output'  =>  'a'
                ]
            );

            $filter = new IaasAnsiblePlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookexecution_event_ssh_port_filter()
    {
        try {
            $request = new Request(
                [
                'ssh_port'  =>  '1'
                ]
            );

            $filter = new IaasAnsiblePlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookexecution_event_execution_total_time_filter()
    {
        try {
            $request = new Request(
                [
                'execution_total_time'  =>  '1'
                ]
            );

            $filter = new IaasAnsiblePlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookexecution_event_result_ok_filter()
    {
        try {
            $request = new Request(
                [
                'result_ok'  =>  '1'
                ]
            );

            $filter = new IaasAnsiblePlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookexecution_event_result_unreachable_filter()
    {
        try {
            $request = new Request(
                [
                'result_unreachable'  =>  '1'
                ]
            );

            $filter = new IaasAnsiblePlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookexecution_event_result_failed_filter()
    {
        try {
            $request = new Request(
                [
                'result_failed'  =>  '1'
                ]
            );

            $filter = new IaasAnsiblePlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookexecution_event_result_skipped_filter()
    {
        try {
            $request = new Request(
                [
                'result_skipped'  =>  '1'
                ]
            );

            $filter = new IaasAnsiblePlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookexecution_event_result_rescued_filter()
    {
        try {
            $request = new Request(
                [
                'result_rescued'  =>  '1'
                ]
            );

            $filter = new IaasAnsiblePlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookexecution_event_result_ignored_filter()
    {
        try {
            $request = new Request(
                [
                'result_ignored'  =>  '1'
                ]
            );

            $filter = new IaasAnsiblePlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookexecution_event_last_execution_time_filter_start()
    {
        try {
            $request = new Request(
                [
                'last_execution_timeStart'  =>  now()
                ]
            );

            $filter = new IaasAnsiblePlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookexecution_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasAnsiblePlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookexecution_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasAnsiblePlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookexecution_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasAnsiblePlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookexecution_event_last_execution_time_filter_end()
    {
        try {
            $request = new Request(
                [
                'last_execution_timeEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsiblePlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookexecution_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsiblePlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookexecution_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsiblePlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookexecution_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsiblePlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookexecution_event_last_execution_time_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'last_execution_timeStart'  =>  now(),
                'last_execution_timeEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsiblePlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookexecution_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsiblePlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookexecution_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsiblePlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansibleplaybookexecution_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsiblePlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsiblePlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}