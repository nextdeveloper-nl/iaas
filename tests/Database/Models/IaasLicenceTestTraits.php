<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;
use NextDeveloper\IAAS\Database\Filters\IaasLicenceQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasLicenceService;

trait IaasLicenceTestTraits
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

    public function test_http_iaaslicence_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaaslicence',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaaslicence_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaaslicence', [
            'form_params'   =>  [
                'object_type'  =>  'a',
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
    public function test_iaaslicence_model_get()
    {
        $result = AbstractIaasLicenceService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaaslicence_get_all()
    {
        $result = AbstractIaasLicenceService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaaslicence_get_paginated()
    {
        $result = AbstractIaasLicenceService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaaslicence_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasLicence\IaasLicenceRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaaslicence_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasLicence\IaasLicenceCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaaslicence_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasLicence\IaasLicenceCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaaslicence_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasLicence\IaasLicenceSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaaslicence_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasLicence\IaasLicenceSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaaslicence_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasLicence\IaasLicenceUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaaslicence_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasLicence\IaasLicenceUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaaslicence_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasLicence\IaasLicenceDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaaslicence_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasLicence\IaasLicenceDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaaslicence_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasLicence\IaasLicenceRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaaslicence_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasLicence\IaasLicenceRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaaslicence_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasLicence::first();

            event(new \NextDeveloper\IAAS\Events\IaasLicence\IaasLicenceRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaaslicence_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasLicence::first();

            event(new \NextDeveloper\IAAS\Events\IaasLicence\IaasLicenceCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaaslicence_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasLicence::first();

            event(new \NextDeveloper\IAAS\Events\IaasLicence\IaasLicenceCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaaslicence_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasLicence::first();

            event(new \NextDeveloper\IAAS\Events\IaasLicence\IaasLicenceSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaaslicence_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasLicence::first();

            event(new \NextDeveloper\IAAS\Events\IaasLicence\IaasLicenceSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaaslicence_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasLicence::first();

            event(new \NextDeveloper\IAAS\Events\IaasLicence\IaasLicenceUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaaslicence_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasLicence::first();

            event(new \NextDeveloper\IAAS\Events\IaasLicence\IaasLicenceUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaaslicence_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasLicence::first();

            event(new \NextDeveloper\IAAS\Events\IaasLicence\IaasLicenceDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaaslicence_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasLicence::first();

            event(new \NextDeveloper\IAAS\Events\IaasLicence\IaasLicenceDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaaslicence_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasLicence::first();

            event(new \NextDeveloper\IAAS\Events\IaasLicence\IaasLicenceRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaaslicence_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasLicence::first();

            event(new \NextDeveloper\IAAS\Events\IaasLicence\IaasLicenceRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaaslicence_event_object_type_filter()
    {
        try {
            $request = new Request(
                [
                'object_type'  =>  'a'
                ]
            );

            $filter = new IaasLicenceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasLicence::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaaslicence_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasLicenceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasLicence::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaaslicence_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasLicenceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasLicence::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaaslicence_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasLicenceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasLicence::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaaslicence_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasLicenceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasLicence::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaaslicence_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasLicenceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasLicence::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaaslicence_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasLicenceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasLicence::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaaslicence_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasLicenceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasLicence::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaaslicence_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasLicenceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasLicence::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaaslicence_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasLicenceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasLicence::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}
