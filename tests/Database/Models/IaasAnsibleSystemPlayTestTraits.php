<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;
use NextDeveloper\IAAS\Database\Filters\IaasAnsibleSystemPlayQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasAnsibleSystemPlayService;

trait IaasAnsibleSystemPlayTestTraits
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

    public function test_http_iaasansiblesystemplay_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasansiblesystemplay',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasansiblesystemplay_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasansiblesystemplay', [
            'form_params'   =>  [
                'name'  =>  'a',
                'hosts'  =>  'a',
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
    public function test_iaasansiblesystemplay_model_get()
    {
        $result = AbstractIaasAnsibleSystemPlayService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasansiblesystemplay_get_all()
    {
        $result = AbstractIaasAnsibleSystemPlayService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasansiblesystemplay_get_paginated()
    {
        $result = AbstractIaasAnsibleSystemPlayService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasansiblesystemplay_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlay\IaasAnsibleSystemPlayRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplay_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlay\IaasAnsibleSystemPlayCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplay_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlay\IaasAnsibleSystemPlayCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplay_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlay\IaasAnsibleSystemPlaySavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplay_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlay\IaasAnsibleSystemPlaySavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplay_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlay\IaasAnsibleSystemPlayUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplay_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlay\IaasAnsibleSystemPlayUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplay_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlay\IaasAnsibleSystemPlayDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplay_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlay\IaasAnsibleSystemPlayDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplay_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlay\IaasAnsibleSystemPlayRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplay_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlay\IaasAnsibleSystemPlayRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplay_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlay::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlay\IaasAnsibleSystemPlayRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplay_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlay::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlay\IaasAnsibleSystemPlayCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplay_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlay::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlay\IaasAnsibleSystemPlayCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplay_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlay::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlay\IaasAnsibleSystemPlaySavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplay_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlay::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlay\IaasAnsibleSystemPlaySavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplay_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlay::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlay\IaasAnsibleSystemPlayUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplay_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlay::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlay\IaasAnsibleSystemPlayUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplay_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlay::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlay\IaasAnsibleSystemPlayDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplay_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlay::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlay\IaasAnsibleSystemPlayDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplay_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlay::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlay\IaasAnsibleSystemPlayRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplay_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlay::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlay\IaasAnsibleSystemPlayRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplay_event_name_filter()
    {
        try {
            $request = new Request(
                [
                'name'  =>  'a'
                ]
            );

            $filter = new IaasAnsibleSystemPlayQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlay::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplay_event_hosts_filter()
    {
        try {
            $request = new Request(
                [
                'hosts'  =>  'a'
                ]
            );

            $filter = new IaasAnsibleSystemPlayQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlay::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplay_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasAnsibleSystemPlayQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlay::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplay_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasAnsibleSystemPlayQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlay::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplay_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasAnsibleSystemPlayQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlay::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplay_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsibleSystemPlayQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlay::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplay_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsibleSystemPlayQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlay::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplay_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsibleSystemPlayQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlay::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplay_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsibleSystemPlayQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlay::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplay_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsibleSystemPlayQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlay::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplay_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsibleSystemPlayQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlay::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}
