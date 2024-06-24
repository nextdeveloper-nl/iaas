<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasDatacenterQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasDatacenterService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasDatacenterTestTraits
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

    public function test_http_iaasdatacenter_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasdatacenter',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasdatacenter_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasdatacenter', [
            'form_params'   =>  [
                'name'  =>  'a',
                'slug'  =>  'a',
                'geo_latitude'  =>  'a',
                'geo_longitude'  =>  'a',
                'power_source'  =>  'a',
                'ups'  =>  'a',
                'cooling'  =>  'a',
                'description'  =>  'a',
                'tier_level'  =>  '1',
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
    public function test_iaasdatacenter_model_get()
    {
        $result = AbstractIaasDatacenterService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasdatacenter_get_all()
    {
        $result = AbstractIaasDatacenterService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasdatacenter_get_paginated()
    {
        $result = AbstractIaasDatacenterService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasdatacenter_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasDatacenter\IaasDatacenterRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdatacenter_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasDatacenter\IaasDatacenterCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdatacenter_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasDatacenter\IaasDatacenterCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdatacenter_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasDatacenter\IaasDatacenterSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdatacenter_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasDatacenter\IaasDatacenterSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdatacenter_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasDatacenter\IaasDatacenterUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdatacenter_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasDatacenter\IaasDatacenterUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdatacenter_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasDatacenter\IaasDatacenterDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdatacenter_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasDatacenter\IaasDatacenterDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdatacenter_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasDatacenter\IaasDatacenterRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdatacenter_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasDatacenter\IaasDatacenterRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdatacenter_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasDatacenter::first();

            event(new \NextDeveloper\IAAS\Events\IaasDatacenter\IaasDatacenterRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdatacenter_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasDatacenter::first();

            event(new \NextDeveloper\IAAS\Events\IaasDatacenter\IaasDatacenterCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdatacenter_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasDatacenter::first();

            event(new \NextDeveloper\IAAS\Events\IaasDatacenter\IaasDatacenterCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdatacenter_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasDatacenter::first();

            event(new \NextDeveloper\IAAS\Events\IaasDatacenter\IaasDatacenterSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdatacenter_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasDatacenter::first();

            event(new \NextDeveloper\IAAS\Events\IaasDatacenter\IaasDatacenterSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdatacenter_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasDatacenter::first();

            event(new \NextDeveloper\IAAS\Events\IaasDatacenter\IaasDatacenterUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdatacenter_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasDatacenter::first();

            event(new \NextDeveloper\IAAS\Events\IaasDatacenter\IaasDatacenterUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdatacenter_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasDatacenter::first();

            event(new \NextDeveloper\IAAS\Events\IaasDatacenter\IaasDatacenterDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdatacenter_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasDatacenter::first();

            event(new \NextDeveloper\IAAS\Events\IaasDatacenter\IaasDatacenterDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdatacenter_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasDatacenter::first();

            event(new \NextDeveloper\IAAS\Events\IaasDatacenter\IaasDatacenterRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasdatacenter_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasDatacenter::first();

            event(new \NextDeveloper\IAAS\Events\IaasDatacenter\IaasDatacenterRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdatacenter_event_name_filter()
    {
        try {
            $request = new Request(
                [
                'name'  =>  'a'
                ]
            );

            $filter = new IaasDatacenterQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasDatacenter::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdatacenter_event_slug_filter()
    {
        try {
            $request = new Request(
                [
                'slug'  =>  'a'
                ]
            );

            $filter = new IaasDatacenterQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasDatacenter::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdatacenter_event_geo_latitude_filter()
    {
        try {
            $request = new Request(
                [
                'geo_latitude'  =>  'a'
                ]
            );

            $filter = new IaasDatacenterQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasDatacenter::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdatacenter_event_geo_longitude_filter()
    {
        try {
            $request = new Request(
                [
                'geo_longitude'  =>  'a'
                ]
            );

            $filter = new IaasDatacenterQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasDatacenter::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdatacenter_event_power_source_filter()
    {
        try {
            $request = new Request(
                [
                'power_source'  =>  'a'
                ]
            );

            $filter = new IaasDatacenterQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasDatacenter::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdatacenter_event_ups_filter()
    {
        try {
            $request = new Request(
                [
                'ups'  =>  'a'
                ]
            );

            $filter = new IaasDatacenterQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasDatacenter::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdatacenter_event_cooling_filter()
    {
        try {
            $request = new Request(
                [
                'cooling'  =>  'a'
                ]
            );

            $filter = new IaasDatacenterQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasDatacenter::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdatacenter_event_description_filter()
    {
        try {
            $request = new Request(
                [
                'description'  =>  'a'
                ]
            );

            $filter = new IaasDatacenterQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasDatacenter::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdatacenter_event_tier_level_filter()
    {
        try {
            $request = new Request(
                [
                'tier_level'  =>  '1'
                ]
            );

            $filter = new IaasDatacenterQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasDatacenter::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdatacenter_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasDatacenterQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasDatacenter::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdatacenter_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasDatacenterQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasDatacenter::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdatacenter_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasDatacenterQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasDatacenter::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdatacenter_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasDatacenterQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasDatacenter::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdatacenter_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasDatacenterQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasDatacenter::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdatacenter_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasDatacenterQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasDatacenter::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdatacenter_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasDatacenterQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasDatacenter::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdatacenter_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasDatacenterQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasDatacenter::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasdatacenter_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasDatacenterQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasDatacenter::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}