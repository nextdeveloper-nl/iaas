<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasBackupRetentionPolicyQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasBackupRetentionPolicyService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasBackupRetentionPolicyTestTraits
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

    public function test_http_iaasbackupretentionpolicy_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasbackupretentionpolicy',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasbackupretentionpolicy_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasbackupretentionpolicy', [
            'form_params'   =>  [
                'name'  =>  'a',
                'description'  =>  'a',
                'keep_for_days'  =>  '1',
                'keep_last_n_backups'  =>  '1',
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
    public function test_iaasbackupretentionpolicy_model_get()
    {
        $result = AbstractIaasBackupRetentionPolicyService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasbackupretentionpolicy_get_all()
    {
        $result = AbstractIaasBackupRetentionPolicyService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasbackupretentionpolicy_get_paginated()
    {
        $result = AbstractIaasBackupRetentionPolicyService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasbackupretentionpolicy_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupRetentionPolicy\IaasBackupRetentionPolicyRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupretentionpolicy_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupRetentionPolicy\IaasBackupRetentionPolicyCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupretentionpolicy_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupRetentionPolicy\IaasBackupRetentionPolicyCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupretentionpolicy_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupRetentionPolicy\IaasBackupRetentionPolicySavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupretentionpolicy_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupRetentionPolicy\IaasBackupRetentionPolicySavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupretentionpolicy_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupRetentionPolicy\IaasBackupRetentionPolicyUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupretentionpolicy_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupRetentionPolicy\IaasBackupRetentionPolicyUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupretentionpolicy_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupRetentionPolicy\IaasBackupRetentionPolicyDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupretentionpolicy_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupRetentionPolicy\IaasBackupRetentionPolicyDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupretentionpolicy_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupRetentionPolicy\IaasBackupRetentionPolicyRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupretentionpolicy_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasBackupRetentionPolicy\IaasBackupRetentionPolicyRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupretentionpolicy_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupRetentionPolicy::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupRetentionPolicy\IaasBackupRetentionPolicyRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupretentionpolicy_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupRetentionPolicy::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupRetentionPolicy\IaasBackupRetentionPolicyCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupretentionpolicy_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupRetentionPolicy::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupRetentionPolicy\IaasBackupRetentionPolicyCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupretentionpolicy_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupRetentionPolicy::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupRetentionPolicy\IaasBackupRetentionPolicySavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupretentionpolicy_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupRetentionPolicy::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupRetentionPolicy\IaasBackupRetentionPolicySavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupretentionpolicy_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupRetentionPolicy::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupRetentionPolicy\IaasBackupRetentionPolicyUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupretentionpolicy_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupRetentionPolicy::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupRetentionPolicy\IaasBackupRetentionPolicyUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupretentionpolicy_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupRetentionPolicy::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupRetentionPolicy\IaasBackupRetentionPolicyDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupretentionpolicy_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupRetentionPolicy::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupRetentionPolicy\IaasBackupRetentionPolicyDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupretentionpolicy_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupRetentionPolicy::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupRetentionPolicy\IaasBackupRetentionPolicyRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasbackupretentionpolicy_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupRetentionPolicy::first();

            event(new \NextDeveloper\IAAS\Events\IaasBackupRetentionPolicy\IaasBackupRetentionPolicyRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupretentionpolicy_event_name_filter()
    {
        try {
            $request = new Request(
                [
                'name'  =>  'a'
                ]
            );

            $filter = new IaasBackupRetentionPolicyQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupRetentionPolicy::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupretentionpolicy_event_description_filter()
    {
        try {
            $request = new Request(
                [
                'description'  =>  'a'
                ]
            );

            $filter = new IaasBackupRetentionPolicyQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupRetentionPolicy::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupretentionpolicy_event_keep_for_days_filter()
    {
        try {
            $request = new Request(
                [
                'keep_for_days'  =>  '1'
                ]
            );

            $filter = new IaasBackupRetentionPolicyQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupRetentionPolicy::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupretentionpolicy_event_keep_last_n_backups_filter()
    {
        try {
            $request = new Request(
                [
                'keep_last_n_backups'  =>  '1'
                ]
            );

            $filter = new IaasBackupRetentionPolicyQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupRetentionPolicy::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupretentionpolicy_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasBackupRetentionPolicyQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupRetentionPolicy::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupretentionpolicy_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasBackupRetentionPolicyQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupRetentionPolicy::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupretentionpolicy_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasBackupRetentionPolicyQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupRetentionPolicy::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupretentionpolicy_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasBackupRetentionPolicyQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupRetentionPolicy::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupretentionpolicy_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasBackupRetentionPolicyQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupRetentionPolicy::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupretentionpolicy_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasBackupRetentionPolicyQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupRetentionPolicy::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupretentionpolicy_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasBackupRetentionPolicyQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupRetentionPolicy::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupretentionpolicy_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasBackupRetentionPolicyQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupRetentionPolicy::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasbackupretentionpolicy_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasBackupRetentionPolicyQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasBackupRetentionPolicy::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}