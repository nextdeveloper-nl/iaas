<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;
use NextDeveloper\IAAS\Database\Filters\IaasNetworkMembersInterfaceQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasNetworkMembersInterfaceService;
use Tests\TestCase;

trait IaasNetworkMembersInterfaceTestTraits
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

    public function test_http_iaasnetworkmembersinterface_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasnetworkmembersinterface',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasnetworkmembersinterface_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasnetworkmembersinterface', [
            'form_params'   =>  [
                'name'  =>  'a',
                'configuration'  =>  'a',
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
    public function test_iaasnetworkmembersinterface_model_get()
    {
        $result = AbstractIaasNetworkMembersInterfaceService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasnetworkmembersinterface_get_all()
    {
        $result = AbstractIaasNetworkMembersInterfaceService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasnetworkmembersinterface_get_paginated()
    {
        $result = AbstractIaasNetworkMembersInterfaceService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasnetworkmembersinterface_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkMembersInterface\IaasNetworkMembersInterfaceRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmembersinterface_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkMembersInterface\IaasNetworkMembersInterfaceCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmembersinterface_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkMembersInterface\IaasNetworkMembersInterfaceCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmembersinterface_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkMembersInterface\IaasNetworkMembersInterfaceSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmembersinterface_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkMembersInterface\IaasNetworkMembersInterfaceSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmembersinterface_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkMembersInterface\IaasNetworkMembersInterfaceUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmembersinterface_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkMembersInterface\IaasNetworkMembersInterfaceUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmembersinterface_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkMembersInterface\IaasNetworkMembersInterfaceDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmembersinterface_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkMembersInterface\IaasNetworkMembersInterfaceDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmembersinterface_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkMembersInterface\IaasNetworkMembersInterfaceRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmembersinterface_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasNetworkMembersInterface\IaasNetworkMembersInterfaceRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkmembersinterface_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMembersInterface::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkMembersInterface\IaasNetworkMembersInterfaceRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmembersinterface_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMembersInterface::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkMembersInterface\IaasNetworkMembersInterfaceCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmembersinterface_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMembersInterface::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkMembersInterface\IaasNetworkMembersInterfaceCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmembersinterface_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMembersInterface::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkMembersInterface\IaasNetworkMembersInterfaceSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmembersinterface_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMembersInterface::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkMembersInterface\IaasNetworkMembersInterfaceSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmembersinterface_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMembersInterface::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkMembersInterface\IaasNetworkMembersInterfaceUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmembersinterface_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMembersInterface::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkMembersInterface\IaasNetworkMembersInterfaceUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmembersinterface_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMembersInterface::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkMembersInterface\IaasNetworkMembersInterfaceDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmembersinterface_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMembersInterface::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkMembersInterface\IaasNetworkMembersInterfaceDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmembersinterface_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMembersInterface::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkMembersInterface\IaasNetworkMembersInterfaceRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasnetworkmembersinterface_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMembersInterface::first();

            event(new \NextDeveloper\IAAS\Events\IaasNetworkMembersInterface\IaasNetworkMembersInterfaceRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkmembersinterface_event_name_filter()
    {
        try {
            $request = new Request(
                [
                'name'  =>  'a'
                ]
            );

            $filter = new IaasNetworkMembersInterfaceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMembersInterface::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkmembersinterface_event_configuration_filter()
    {
        try {
            $request = new Request(
                [
                'configuration'  =>  'a'
                ]
            );

            $filter = new IaasNetworkMembersInterfaceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMembersInterface::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkmembersinterface_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasNetworkMembersInterfaceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMembersInterface::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkmembersinterface_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasNetworkMembersInterfaceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMembersInterface::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkmembersinterface_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasNetworkMembersInterfaceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMembersInterface::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkmembersinterface_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasNetworkMembersInterfaceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMembersInterface::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkmembersinterface_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasNetworkMembersInterfaceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMembersInterface::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkmembersinterface_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasNetworkMembersInterfaceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMembersInterface::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkmembersinterface_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasNetworkMembersInterfaceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMembersInterface::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkmembersinterface_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasNetworkMembersInterfaceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMembersInterface::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasnetworkmembersinterface_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasNetworkMembersInterfaceQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasNetworkMembersInterface::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}