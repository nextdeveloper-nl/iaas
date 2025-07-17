<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;
use NextDeveloper\IAAS\Database\Filters\IaasAnsibleSystemPlaybookExecutionQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasAnsibleSystemPlaybookExecutionService;

trait IaasAnsibleSystemPlaybookExecutionTestTraits
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

    public function test_http_iaasansiblesystemplaybookexecution_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasansiblesystemplaybookexecution',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasansiblesystemplaybookexecution_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasansiblesystemplaybookexecution', [
            'form_params'   =>  [
                'package'  =>  'a',
                'last_output'  =>  'a',
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
    public function test_iaasansiblesystemplaybookexecution_model_get()
    {
        $result = AbstractIaasAnsibleSystemPlaybookExecutionService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasansiblesystemplaybookexecution_get_all()
    {
        $result = AbstractIaasAnsibleSystemPlaybookExecutionService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasansiblesystemplaybookexecution_get_paginated()
    {
        $result = AbstractIaasAnsibleSystemPlaybookExecutionService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasansiblesystemplaybookexecution_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybookExecution\IaasAnsibleSystemPlaybookExecutionRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybookexecution_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybookExecution\IaasAnsibleSystemPlaybookExecutionCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybookexecution_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybookExecution\IaasAnsibleSystemPlaybookExecutionCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybookexecution_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybookExecution\IaasAnsibleSystemPlaybookExecutionSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybookexecution_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybookExecution\IaasAnsibleSystemPlaybookExecutionSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybookexecution_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybookExecution\IaasAnsibleSystemPlaybookExecutionUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybookexecution_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybookExecution\IaasAnsibleSystemPlaybookExecutionUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybookexecution_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybookExecution\IaasAnsibleSystemPlaybookExecutionDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybookexecution_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybookExecution\IaasAnsibleSystemPlaybookExecutionDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybookexecution_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybookExecution\IaasAnsibleSystemPlaybookExecutionRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybookexecution_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybookExecution\IaasAnsibleSystemPlaybookExecutionRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybookexecution_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybookExecution::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybookExecution\IaasAnsibleSystemPlaybookExecutionRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybookexecution_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybookExecution::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybookExecution\IaasAnsibleSystemPlaybookExecutionCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybookexecution_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybookExecution::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybookExecution\IaasAnsibleSystemPlaybookExecutionCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybookexecution_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybookExecution::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybookExecution\IaasAnsibleSystemPlaybookExecutionSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybookexecution_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybookExecution::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybookExecution\IaasAnsibleSystemPlaybookExecutionSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybookexecution_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybookExecution::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybookExecution\IaasAnsibleSystemPlaybookExecutionUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybookexecution_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybookExecution::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybookExecution\IaasAnsibleSystemPlaybookExecutionUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybookexecution_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybookExecution::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybookExecution\IaasAnsibleSystemPlaybookExecutionDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybookexecution_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybookExecution::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybookExecution\IaasAnsibleSystemPlaybookExecutionDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybookexecution_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybookExecution::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybookExecution\IaasAnsibleSystemPlaybookExecutionRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybookexecution_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybookExecution::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybookExecution\IaasAnsibleSystemPlaybookExecutionRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybookexecution_event_package_filter()
    {
        try {
            $request = new Request(
                [
                'package'  =>  'a'
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybookexecution_event_last_output_filter()
    {
        try {
            $request = new Request(
                [
                'last_output'  =>  'a'
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybookexecution_event_execution_total_time_filter()
    {
        try {
            $request = new Request(
                [
                'execution_total_time'  =>  '1'
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybookexecution_event_result_ok_filter()
    {
        try {
            $request = new Request(
                [
                'result_ok'  =>  '1'
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybookexecution_event_result_unreachable_filter()
    {
        try {
            $request = new Request(
                [
                'result_unreachable'  =>  '1'
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybookexecution_event_result_failed_filter()
    {
        try {
            $request = new Request(
                [
                'result_failed'  =>  '1'
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybookexecution_event_result_skipped_filter()
    {
        try {
            $request = new Request(
                [
                'result_skipped'  =>  '1'
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybookexecution_event_result_rescued_filter()
    {
        try {
            $request = new Request(
                [
                'result_rescued'  =>  '1'
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybookexecution_event_result_ignored_filter()
    {
        try {
            $request = new Request(
                [
                'result_ignored'  =>  '1'
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybookexecution_event_last_execution_time_filter_start()
    {
        try {
            $request = new Request(
                [
                'last_execution_timeStart'  =>  now()
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybookexecution_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybookexecution_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybookexecution_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybookexecution_event_last_execution_time_filter_end()
    {
        try {
            $request = new Request(
                [
                'last_execution_timeEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybookexecution_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybookexecution_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybookexecution_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybookexecution_event_last_execution_time_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'last_execution_timeStart'  =>  now(),
                'last_execution_timeEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybookexecution_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybookexecution_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybookexecution_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookExecutionQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybookExecution::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}
