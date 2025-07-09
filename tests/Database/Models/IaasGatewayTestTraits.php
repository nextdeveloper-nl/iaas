<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;
use NextDeveloper\IAAS\Database\Filters\IaasGatewayQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasGatewayService;

trait IaasGatewayTestTraits
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

    public function test_http_iaasgateway_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasgateway',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasgateway_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasgateway', [
            'form_params'   =>  [
                'name'  =>  'a',
                'ssh_username'  =>  'a',
                'ssh_password'  =>  'a',
                'api_token'  =>  'a',
                'api_url'  =>  'a',
                'gateway_type'  =>  'a',
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
    public function test_iaasgateway_model_get()
    {
        $result = AbstractIaasGatewayService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasgateway_get_all()
    {
        $result = AbstractIaasGatewayService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasgateway_get_paginated()
    {
        $result = AbstractIaasGatewayService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasgateway_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasGateway\IaasGatewayRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasgateway_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasGateway\IaasGatewayCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasgateway_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasGateway\IaasGatewayCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasgateway_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasGateway\IaasGatewaySavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasgateway_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasGateway\IaasGatewaySavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasgateway_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasGateway\IaasGatewayUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasgateway_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasGateway\IaasGatewayUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasgateway_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasGateway\IaasGatewayDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasgateway_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasGateway\IaasGatewayDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasgateway_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasGateway\IaasGatewayRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasgateway_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasGateway\IaasGatewayRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasgateway_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasGateway::first();

            event(new \NextDeveloper\IAAS\Events\IaasGateway\IaasGatewayRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasgateway_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasGateway::first();

            event(new \NextDeveloper\IAAS\Events\IaasGateway\IaasGatewayCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasgateway_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasGateway::first();

            event(new \NextDeveloper\IAAS\Events\IaasGateway\IaasGatewayCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasgateway_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasGateway::first();

            event(new \NextDeveloper\IAAS\Events\IaasGateway\IaasGatewaySavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasgateway_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasGateway::first();

            event(new \NextDeveloper\IAAS\Events\IaasGateway\IaasGatewaySavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasgateway_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasGateway::first();

            event(new \NextDeveloper\IAAS\Events\IaasGateway\IaasGatewayUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasgateway_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasGateway::first();

            event(new \NextDeveloper\IAAS\Events\IaasGateway\IaasGatewayUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasgateway_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasGateway::first();

            event(new \NextDeveloper\IAAS\Events\IaasGateway\IaasGatewayDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasgateway_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasGateway::first();

            event(new \NextDeveloper\IAAS\Events\IaasGateway\IaasGatewayDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasgateway_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasGateway::first();

            event(new \NextDeveloper\IAAS\Events\IaasGateway\IaasGatewayRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasgateway_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasGateway::first();

            event(new \NextDeveloper\IAAS\Events\IaasGateway\IaasGatewayRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasgateway_event_name_filter()
    {
        try {
            $request = new Request(
                [
                'name'  =>  'a'
                ]
            );

            $filter = new IaasGatewayQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasGateway::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasgateway_event_ssh_username_filter()
    {
        try {
            $request = new Request(
                [
                'ssh_username'  =>  'a'
                ]
            );

            $filter = new IaasGatewayQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasGateway::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasgateway_event_ssh_password_filter()
    {
        try {
            $request = new Request(
                [
                'ssh_password'  =>  'a'
                ]
            );

            $filter = new IaasGatewayQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasGateway::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasgateway_event_api_token_filter()
    {
        try {
            $request = new Request(
                [
                'api_token'  =>  'a'
                ]
            );

            $filter = new IaasGatewayQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasGateway::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasgateway_event_api_url_filter()
    {
        try {
            $request = new Request(
                [
                'api_url'  =>  'a'
                ]
            );

            $filter = new IaasGatewayQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasGateway::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasgateway_event_gateway_type_filter()
    {
        try {
            $request = new Request(
                [
                'gateway_type'  =>  'a'
                ]
            );

            $filter = new IaasGatewayQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasGateway::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasgateway_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasGatewayQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasGateway::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasgateway_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasGatewayQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasGateway::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasgateway_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasGatewayQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasGateway::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasgateway_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasGatewayQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasGateway::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasgateway_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasGatewayQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasGateway::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasgateway_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasGatewayQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasGateway::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasgateway_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasGatewayQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasGateway::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasgateway_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasGatewayQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasGateway::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasgateway_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasGatewayQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasGateway::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}
