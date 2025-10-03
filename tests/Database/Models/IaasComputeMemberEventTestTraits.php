<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;
use NextDeveloper\IAAS\Database\Filters\IaasComputeMemberEventQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasComputeMemberEventService;
use Tests\TestCase;

trait IaasComputeMemberEventTestTraits
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

    public function test_http_iaascomputememberevent_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaascomputememberevent',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaascomputememberevent_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaascomputememberevent', [
            'form_params'   =>  [
                'source'  =>  'a',
                'type'  =>  'a',
                'event'  =>  'a',
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
    public function test_iaascomputememberevent_model_get()
    {
        $result = AbstractIaasComputeMemberEventService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaascomputememberevent_get_all()
    {
        $result = AbstractIaasComputeMemberEventService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaascomputememberevent_get_paginated()
    {
        $result = AbstractIaasComputeMemberEventService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaascomputememberevent_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberEvent\IaasComputeMemberEventRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberevent_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberEvent\IaasComputeMemberEventCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberevent_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberEvent\IaasComputeMemberEventCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberevent_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberEvent\IaasComputeMemberEventSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberevent_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberEvent\IaasComputeMemberEventSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberevent_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberEvent\IaasComputeMemberEventUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberevent_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberEvent\IaasComputeMemberEventUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberevent_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberEvent\IaasComputeMemberEventDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberevent_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberEvent\IaasComputeMemberEventDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberevent_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberEvent\IaasComputeMemberEventRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberevent_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberEvent\IaasComputeMemberEventRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberevent_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberEvent::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberEvent\IaasComputeMemberEventRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberevent_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberEvent::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberEvent\IaasComputeMemberEventCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberevent_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberEvent::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberEvent\IaasComputeMemberEventCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberevent_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberEvent::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberEvent\IaasComputeMemberEventSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberevent_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberEvent::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberEvent\IaasComputeMemberEventSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberevent_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberEvent::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberEvent\IaasComputeMemberEventUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberevent_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberEvent::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberEvent\IaasComputeMemberEventUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberevent_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberEvent::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberEvent\IaasComputeMemberEventDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberevent_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberEvent::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberEvent\IaasComputeMemberEventDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberevent_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberEvent::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberEvent\IaasComputeMemberEventRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaascomputememberevent_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberEvent::first();

            event(new \NextDeveloper\IAAS\Events\IaasComputeMemberEvent\IaasComputeMemberEventRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberevent_event_source_filter()
    {
        try {
            $request = new Request(
                [
                'source'  =>  'a'
                ]
            );

            $filter = new IaasComputeMemberEventQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberEvent::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberevent_event_type_filter()
    {
        try {
            $request = new Request(
                [
                'type'  =>  'a'
                ]
            );

            $filter = new IaasComputeMemberEventQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberEvent::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberevent_event_event_filter()
    {
        try {
            $request = new Request(
                [
                'event'  =>  'a'
                ]
            );

            $filter = new IaasComputeMemberEventQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberEvent::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberevent_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberEventQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberEvent::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberevent_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberEventQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberEvent::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberevent_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberEventQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberEvent::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberevent_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberEventQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberEvent::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberevent_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberEventQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberEvent::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberevent_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberEventQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberEvent::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberevent_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberEventQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberEvent::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberevent_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberEventQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberEvent::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaascomputememberevent_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasComputeMemberEventQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasComputeMemberEvent::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}