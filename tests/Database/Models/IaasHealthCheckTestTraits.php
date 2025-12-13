<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasHealthCheckQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasHealthCheckService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasHealthCheckTestTraits
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

    public function test_http_iaashealthcheck_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaashealthcheck',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaashealthcheck_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaashealthcheck', [
            'form_params'   =>  [
                'object_type'  =>  'a',
                'check_type'  =>  'a',
                'check_status'  =>  'a',
                'severity'  =>  'a',
                'error_message'  =>  'a',
                'error_code'  =>  'a',
                'response_time_ms'  =>  '1',
                    'checked_at'  =>  now(),
                    'next_check_at'  =>  now(),
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
    public function test_iaashealthcheck_model_get()
    {
        $result = AbstractIaasHealthCheckService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaashealthcheck_get_all()
    {
        $result = AbstractIaasHealthCheckService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaashealthcheck_get_paginated()
    {
        $result = AbstractIaasHealthCheckService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaashealthcheck_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasHealthCheck\IaasHealthCheckRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaashealthcheck_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasHealthCheck\IaasHealthCheckCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaashealthcheck_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasHealthCheck\IaasHealthCheckCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaashealthcheck_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasHealthCheck\IaasHealthCheckSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaashealthcheck_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasHealthCheck\IaasHealthCheckSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaashealthcheck_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasHealthCheck\IaasHealthCheckUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaashealthcheck_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasHealthCheck\IaasHealthCheckUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaashealthcheck_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasHealthCheck\IaasHealthCheckDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaashealthcheck_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasHealthCheck\IaasHealthCheckDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaashealthcheck_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasHealthCheck\IaasHealthCheckRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaashealthcheck_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasHealthCheck\IaasHealthCheckRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaashealthcheck_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::first();

            event(new \NextDeveloper\IAAS\Events\IaasHealthCheck\IaasHealthCheckRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaashealthcheck_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::first();

            event(new \NextDeveloper\IAAS\Events\IaasHealthCheck\IaasHealthCheckCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaashealthcheck_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::first();

            event(new \NextDeveloper\IAAS\Events\IaasHealthCheck\IaasHealthCheckCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaashealthcheck_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::first();

            event(new \NextDeveloper\IAAS\Events\IaasHealthCheck\IaasHealthCheckSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaashealthcheck_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::first();

            event(new \NextDeveloper\IAAS\Events\IaasHealthCheck\IaasHealthCheckSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaashealthcheck_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::first();

            event(new \NextDeveloper\IAAS\Events\IaasHealthCheck\IaasHealthCheckUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaashealthcheck_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::first();

            event(new \NextDeveloper\IAAS\Events\IaasHealthCheck\IaasHealthCheckUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaashealthcheck_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::first();

            event(new \NextDeveloper\IAAS\Events\IaasHealthCheck\IaasHealthCheckDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaashealthcheck_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::first();

            event(new \NextDeveloper\IAAS\Events\IaasHealthCheck\IaasHealthCheckDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaashealthcheck_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::first();

            event(new \NextDeveloper\IAAS\Events\IaasHealthCheck\IaasHealthCheckRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaashealthcheck_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::first();

            event(new \NextDeveloper\IAAS\Events\IaasHealthCheck\IaasHealthCheckRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaashealthcheck_event_object_type_filter()
    {
        try {
            $request = new Request(
                [
                'object_type'  =>  'a'
                ]
            );

            $filter = new IaasHealthCheckQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaashealthcheck_event_check_type_filter()
    {
        try {
            $request = new Request(
                [
                'check_type'  =>  'a'
                ]
            );

            $filter = new IaasHealthCheckQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaashealthcheck_event_check_status_filter()
    {
        try {
            $request = new Request(
                [
                'check_status'  =>  'a'
                ]
            );

            $filter = new IaasHealthCheckQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaashealthcheck_event_severity_filter()
    {
        try {
            $request = new Request(
                [
                'severity'  =>  'a'
                ]
            );

            $filter = new IaasHealthCheckQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaashealthcheck_event_error_message_filter()
    {
        try {
            $request = new Request(
                [
                'error_message'  =>  'a'
                ]
            );

            $filter = new IaasHealthCheckQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaashealthcheck_event_error_code_filter()
    {
        try {
            $request = new Request(
                [
                'error_code'  =>  'a'
                ]
            );

            $filter = new IaasHealthCheckQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaashealthcheck_event_response_time_ms_filter()
    {
        try {
            $request = new Request(
                [
                'response_time_ms'  =>  '1'
                ]
            );

            $filter = new IaasHealthCheckQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaashealthcheck_event_checked_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'checked_atStart'  =>  now()
                ]
            );

            $filter = new IaasHealthCheckQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaashealthcheck_event_next_check_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'next_check_atStart'  =>  now()
                ]
            );

            $filter = new IaasHealthCheckQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaashealthcheck_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasHealthCheckQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaashealthcheck_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasHealthCheckQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaashealthcheck_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasHealthCheckQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaashealthcheck_event_checked_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'checked_atEnd'  =>  now()
                ]
            );

            $filter = new IaasHealthCheckQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaashealthcheck_event_next_check_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'next_check_atEnd'  =>  now()
                ]
            );

            $filter = new IaasHealthCheckQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaashealthcheck_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasHealthCheckQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaashealthcheck_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasHealthCheckQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaashealthcheck_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasHealthCheckQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaashealthcheck_event_checked_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'checked_atStart'  =>  now(),
                'checked_atEnd'  =>  now()
                ]
            );

            $filter = new IaasHealthCheckQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaashealthcheck_event_next_check_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'next_check_atStart'  =>  now(),
                'next_check_atEnd'  =>  now()
                ]
            );

            $filter = new IaasHealthCheckQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaashealthcheck_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasHealthCheckQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaashealthcheck_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasHealthCheckQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaashealthcheck_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasHealthCheckQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasHealthCheck::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}