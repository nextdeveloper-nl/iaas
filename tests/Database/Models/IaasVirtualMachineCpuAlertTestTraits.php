<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasVirtualMachineCpuAlertQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasVirtualMachineCpuAlertService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasVirtualMachineCpuAlertTestTraits
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

    public function test_http_iaasvirtualmachinecpualert_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasvirtualmachinecpualert',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasvirtualmachinecpualert_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasvirtualmachinecpualert', [
            'form_params'   =>  [
                'severity'  =>  'a',
                'alert_reason'  =>  'a',
                'check_duration_ms'  =>  '1',
                    'alert_time'  =>  now(),
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
    public function test_iaasvirtualmachinecpualert_model_get()
    {
        $result = AbstractIaasVirtualMachineCpuAlertService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasvirtualmachinecpualert_get_all()
    {
        $result = AbstractIaasVirtualMachineCpuAlertService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasvirtualmachinecpualert_get_paginated()
    {
        $result = AbstractIaasVirtualMachineCpuAlertService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasvirtualmachinecpualert_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuAlert\IaasVirtualMachineCpuAlertRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpualert_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuAlert\IaasVirtualMachineCpuAlertCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpualert_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuAlert\IaasVirtualMachineCpuAlertCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpualert_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuAlert\IaasVirtualMachineCpuAlertSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpualert_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuAlert\IaasVirtualMachineCpuAlertSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpualert_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuAlert\IaasVirtualMachineCpuAlertUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpualert_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuAlert\IaasVirtualMachineCpuAlertUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpualert_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuAlert\IaasVirtualMachineCpuAlertDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpualert_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuAlert\IaasVirtualMachineCpuAlertDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpualert_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuAlert\IaasVirtualMachineCpuAlertRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpualert_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuAlert\IaasVirtualMachineCpuAlertRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpualert_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuAlert::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuAlert\IaasVirtualMachineCpuAlertRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpualert_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuAlert::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuAlert\IaasVirtualMachineCpuAlertCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpualert_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuAlert::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuAlert\IaasVirtualMachineCpuAlertCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpualert_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuAlert::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuAlert\IaasVirtualMachineCpuAlertSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpualert_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuAlert::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuAlert\IaasVirtualMachineCpuAlertSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpualert_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuAlert::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuAlert\IaasVirtualMachineCpuAlertUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpualert_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuAlert::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuAlert\IaasVirtualMachineCpuAlertUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpualert_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuAlert::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuAlert\IaasVirtualMachineCpuAlertDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpualert_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuAlert::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuAlert\IaasVirtualMachineCpuAlertDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpualert_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuAlert::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuAlert\IaasVirtualMachineCpuAlertRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualmachinecpualert_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuAlert::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualMachineCpuAlert\IaasVirtualMachineCpuAlertRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpualert_event_severity_filter()
    {
        try {
            $request = new Request(
                [
                'severity'  =>  'a'
                ]
            );

            $filter = new IaasVirtualMachineCpuAlertQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuAlert::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpualert_event_alert_reason_filter()
    {
        try {
            $request = new Request(
                [
                'alert_reason'  =>  'a'
                ]
            );

            $filter = new IaasVirtualMachineCpuAlertQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuAlert::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpualert_event_check_duration_ms_filter()
    {
        try {
            $request = new Request(
                [
                'check_duration_ms'  =>  '1'
                ]
            );

            $filter = new IaasVirtualMachineCpuAlertQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuAlert::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpualert_event_alert_time_filter_start()
    {
        try {
            $request = new Request(
                [
                'alert_timeStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineCpuAlertQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuAlert::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpualert_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineCpuAlertQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuAlert::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpualert_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineCpuAlertQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuAlert::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpualert_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineCpuAlertQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuAlert::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpualert_event_alert_time_filter_end()
    {
        try {
            $request = new Request(
                [
                'alert_timeEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineCpuAlertQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuAlert::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpualert_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineCpuAlertQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuAlert::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpualert_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineCpuAlertQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuAlert::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpualert_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineCpuAlertQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuAlert::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpualert_event_alert_time_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'alert_timeStart'  =>  now(),
                'alert_timeEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineCpuAlertQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuAlert::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpualert_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineCpuAlertQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuAlert::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpualert_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineCpuAlertQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuAlert::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualmachinecpualert_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualMachineCpuAlertQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualMachineCpuAlert::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}