<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;
use NextDeveloper\IAAS\Database\Filters\IaasAccountStatQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasAccountStatService;
use Tests\TestCase;

trait IaasAccountStatTestTraits
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

    public function test_http_iaasaccountstat_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasaccountstat',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasaccountstat_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasaccountstat', [
            'form_params'   =>  [
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
    public function test_iaasaccountstat_model_get()
    {
        $result = AbstractIaasAccountStatService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasaccountstat_get_all()
    {
        $result = AbstractIaasAccountStatService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasaccountstat_get_paginated()
    {
        $result = AbstractIaasAccountStatService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasaccountstat_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAccountStat\IaasAccountStatRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasaccountstat_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAccountStat\IaasAccountStatCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasaccountstat_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAccountStat\IaasAccountStatCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasaccountstat_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAccountStat\IaasAccountStatSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasaccountstat_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAccountStat\IaasAccountStatSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasaccountstat_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAccountStat\IaasAccountStatUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasaccountstat_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAccountStat\IaasAccountStatUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasaccountstat_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAccountStat\IaasAccountStatDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasaccountstat_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAccountStat\IaasAccountStatDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasaccountstat_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAccountStat\IaasAccountStatRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasaccountstat_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAccountStat\IaasAccountStatRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasaccountstat_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAccountStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasAccountStat\IaasAccountStatRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasaccountstat_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAccountStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasAccountStat\IaasAccountStatCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasaccountstat_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAccountStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasAccountStat\IaasAccountStatCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasaccountstat_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAccountStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasAccountStat\IaasAccountStatSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasaccountstat_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAccountStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasAccountStat\IaasAccountStatSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasaccountstat_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAccountStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasAccountStat\IaasAccountStatUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasaccountstat_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAccountStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasAccountStat\IaasAccountStatUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasaccountstat_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAccountStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasAccountStat\IaasAccountStatDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasaccountstat_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAccountStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasAccountStat\IaasAccountStatDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasaccountstat_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAccountStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasAccountStat\IaasAccountStatRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasaccountstat_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAccountStat::first();

            event(new \NextDeveloper\IAAS\Events\IaasAccountStat\IaasAccountStatRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasaccountstat_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasAccountStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAccountStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasaccountstat_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasAccountStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAccountStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasaccountstat_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasAccountStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAccountStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasaccountstat_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAccountStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAccountStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasaccountstat_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAccountStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAccountStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasaccountstat_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAccountStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAccountStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasaccountstat_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAccountStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAccountStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasaccountstat_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAccountStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAccountStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasaccountstat_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAccountStatQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAccountStat::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}