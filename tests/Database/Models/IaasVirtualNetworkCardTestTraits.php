<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\IaasVirtualNetworkCardQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasVirtualNetworkCardService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait IaasVirtualNetworkCardTestTraits
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

    public function test_http_iaasvirtualnetworkcard_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasvirtualnetworkcard',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasvirtualnetworkcard_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasvirtualnetworkcard', [
            'form_params'   =>  [
                'name'  =>  'a',
                'hypervisor_uuid'  =>  'a',
                'status'  =>  'a',
                'bandwidth_limit'  =>  '1',
                'device_number'  =>  '1',
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
    public function test_iaasvirtualnetworkcard_model_get()
    {
        $result = AbstractIaasVirtualNetworkCardService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasvirtualnetworkcard_get_all()
    {
        $result = AbstractIaasVirtualNetworkCardService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasvirtualnetworkcard_get_paginated()
    {
        $result = AbstractIaasVirtualNetworkCardService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasvirtualnetworkcard_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCard\IaasVirtualNetworkCardRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcard_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCard\IaasVirtualNetworkCardCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcard_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCard\IaasVirtualNetworkCardCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcard_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCard\IaasVirtualNetworkCardSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcard_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCard\IaasVirtualNetworkCardSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcard_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCard\IaasVirtualNetworkCardUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcard_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCard\IaasVirtualNetworkCardUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcard_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCard\IaasVirtualNetworkCardDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcard_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCard\IaasVirtualNetworkCardDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcard_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCard\IaasVirtualNetworkCardRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcard_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCard\IaasVirtualNetworkCardRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualnetworkcard_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCard::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCard\IaasVirtualNetworkCardRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcard_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCard::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCard\IaasVirtualNetworkCardCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcard_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCard::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCard\IaasVirtualNetworkCardCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcard_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCard::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCard\IaasVirtualNetworkCardSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcard_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCard::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCard\IaasVirtualNetworkCardSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcard_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCard::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCard\IaasVirtualNetworkCardUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcard_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCard::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCard\IaasVirtualNetworkCardUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcard_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCard::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCard\IaasVirtualNetworkCardDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcard_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCard::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCard\IaasVirtualNetworkCardDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcard_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCard::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCard\IaasVirtualNetworkCardRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasvirtualnetworkcard_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCard::first();

            event(new \NextDeveloper\IAAS\Events\IaasVirtualNetworkCard\IaasVirtualNetworkCardRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualnetworkcard_event_name_filter()
    {
        try {
            $request = new Request(
                [
                'name'  =>  'a'
                ]
            );

            $filter = new IaasVirtualNetworkCardQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCard::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualnetworkcard_event_hypervisor_uuid_filter()
    {
        try {
            $request = new Request(
                [
                'hypervisor_uuid'  =>  'a'
                ]
            );

            $filter = new IaasVirtualNetworkCardQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCard::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualnetworkcard_event_status_filter()
    {
        try {
            $request = new Request(
                [
                'status'  =>  'a'
                ]
            );

            $filter = new IaasVirtualNetworkCardQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCard::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualnetworkcard_event_bandwidth_limit_filter()
    {
        try {
            $request = new Request(
                [
                'bandwidth_limit'  =>  '1'
                ]
            );

            $filter = new IaasVirtualNetworkCardQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCard::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualnetworkcard_event_device_number_filter()
    {
        try {
            $request = new Request(
                [
                'device_number'  =>  '1'
                ]
            );

            $filter = new IaasVirtualNetworkCardQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCard::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualnetworkcard_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualNetworkCardQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCard::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualnetworkcard_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualNetworkCardQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCard::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualnetworkcard_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasVirtualNetworkCardQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCard::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualnetworkcard_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualNetworkCardQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCard::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualnetworkcard_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualNetworkCardQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCard::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualnetworkcard_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualNetworkCardQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCard::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualnetworkcard_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualNetworkCardQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCard::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualnetworkcard_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualNetworkCardQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCard::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasvirtualnetworkcard_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasVirtualNetworkCardQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasVirtualNetworkCard::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}