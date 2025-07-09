<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;
use NextDeveloper\IAAS\Database\Filters\IaasAnsibleSystemPlaybookQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasAnsibleSystemPlaybookService;

trait IaasAnsibleSystemPlaybookTestTraits
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

    public function test_http_iaasansiblesystemplaybook_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasansiblesystemplaybook',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasansiblesystemplaybook_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasansiblesystemplaybook', [
            'form_params'   =>  [
                'slug'  =>  'a',
                'name'  =>  'a',
                'description'  =>  'a',
                'package'  =>  'a',
                'path'  =>  'a',
                'playbook_filename'  =>  'a',
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
    public function test_iaasansiblesystemplaybook_model_get()
    {
        $result = AbstractIaasAnsibleSystemPlaybookService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasansiblesystemplaybook_get_all()
    {
        $result = AbstractIaasAnsibleSystemPlaybookService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasansiblesystemplaybook_get_paginated()
    {
        $result = AbstractIaasAnsibleSystemPlaybookService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasansiblesystemplaybook_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybook\IaasAnsibleSystemPlaybookRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybook_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybook\IaasAnsibleSystemPlaybookCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybook_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybook\IaasAnsibleSystemPlaybookCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybook_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybook\IaasAnsibleSystemPlaybookSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybook_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybook\IaasAnsibleSystemPlaybookSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybook_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybook\IaasAnsibleSystemPlaybookUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybook_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybook\IaasAnsibleSystemPlaybookUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybook_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybook\IaasAnsibleSystemPlaybookDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybook_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybook\IaasAnsibleSystemPlaybookDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybook_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybook\IaasAnsibleSystemPlaybookRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybook_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybook\IaasAnsibleSystemPlaybookRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybook_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybook::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybook\IaasAnsibleSystemPlaybookRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybook_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybook::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybook\IaasAnsibleSystemPlaybookCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybook_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybook::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybook\IaasAnsibleSystemPlaybookCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybook_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybook::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybook\IaasAnsibleSystemPlaybookSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybook_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybook::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybook\IaasAnsibleSystemPlaybookSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybook_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybook::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybook\IaasAnsibleSystemPlaybookUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybook_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybook::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybook\IaasAnsibleSystemPlaybookUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybook_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybook::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybook\IaasAnsibleSystemPlaybookDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybook_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybook::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybook\IaasAnsibleSystemPlaybookDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybook_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybook::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybook\IaasAnsibleSystemPlaybookRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasansiblesystemplaybook_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybook::first();

            event(new \NextDeveloper\IAAS\Events\IaasAnsibleSystemPlaybook\IaasAnsibleSystemPlaybookRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybook_event_slug_filter()
    {
        try {
            $request = new Request(
                [
                'slug'  =>  'a'
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybook::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybook_event_name_filter()
    {
        try {
            $request = new Request(
                [
                'name'  =>  'a'
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybook::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybook_event_description_filter()
    {
        try {
            $request = new Request(
                [
                'description'  =>  'a'
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybook::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybook_event_package_filter()
    {
        try {
            $request = new Request(
                [
                'package'  =>  'a'
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybook::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybook_event_path_filter()
    {
        try {
            $request = new Request(
                [
                'path'  =>  'a'
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybook::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybook_event_playbook_filename_filter()
    {
        try {
            $request = new Request(
                [
                'playbook_filename'  =>  'a'
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybook::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybook_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybook::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybook_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybook::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybook_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybook::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybook_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybook::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybook_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybook::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybook_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybook::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybook_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybook::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybook_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybook::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasansiblesystemplaybook_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasAnsibleSystemPlaybookQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasAnsibleSystemPlaybook::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}
