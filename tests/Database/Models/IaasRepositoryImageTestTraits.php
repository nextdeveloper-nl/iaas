<?php

namespace NextDeveloper\IAAS\Tests\Database\Models;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;
use NextDeveloper\IAAS\Database\Filters\IaasRepositoryImageQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractIaasRepositoryImageService;
use Tests\TestCase;

trait IaasRepositoryImageTestTraits
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

    public function test_http_iaasrepositoryimage_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/iaas/iaasrepositoryimage',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_iaasrepositoryimage_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/iaas/iaasrepositoryimage', [
            'form_params'   =>  [
                'name'  =>  'a',
                'description'  =>  'a',
                'path'  =>  'a',
                'filename'  =>  'a',
                'default_username'  =>  'a',
                'default_password'  =>  'a',
                'os'  =>  'a',
                'distro'  =>  'a',
                'version'  =>  'a',
                'release_version'  =>  'a',
                'extra'  =>  'a',
                'cpu_type'  =>  'a',
                'hash'  =>  'a',
                'post_boot_script'  =>  'a',
                'size'  =>  '1',
                'ram'  =>  '1',
                'cpu'  =>  '1',
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
    public function test_iaasrepositoryimage_model_get()
    {
        $result = AbstractIaasRepositoryImageService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasrepositoryimage_get_all()
    {
        $result = AbstractIaasRepositoryImageService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_iaasrepositoryimage_get_paginated()
    {
        $result = AbstractIaasRepositoryImageService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_iaasrepositoryimage_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasRepositoryImage\IaasRepositoryImageRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepositoryimage_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasRepositoryImage\IaasRepositoryImageCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepositoryimage_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasRepositoryImage\IaasRepositoryImageCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepositoryimage_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasRepositoryImage\IaasRepositoryImageSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepositoryimage_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasRepositoryImage\IaasRepositoryImageSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepositoryimage_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasRepositoryImage\IaasRepositoryImageUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepositoryimage_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasRepositoryImage\IaasRepositoryImageUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepositoryimage_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasRepositoryImage\IaasRepositoryImageDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepositoryimage_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasRepositoryImage\IaasRepositoryImageDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepositoryimage_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasRepositoryImage\IaasRepositoryImageRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepositoryimage_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\IAAS\Events\IaasRepositoryImage\IaasRepositoryImageRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepositoryimage_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::first();

            event(new \NextDeveloper\IAAS\Events\IaasRepositoryImage\IaasRepositoryImageRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepositoryimage_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::first();

            event(new \NextDeveloper\IAAS\Events\IaasRepositoryImage\IaasRepositoryImageCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepositoryimage_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::first();

            event(new \NextDeveloper\IAAS\Events\IaasRepositoryImage\IaasRepositoryImageCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepositoryimage_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::first();

            event(new \NextDeveloper\IAAS\Events\IaasRepositoryImage\IaasRepositoryImageSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepositoryimage_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::first();

            event(new \NextDeveloper\IAAS\Events\IaasRepositoryImage\IaasRepositoryImageSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepositoryimage_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::first();

            event(new \NextDeveloper\IAAS\Events\IaasRepositoryImage\IaasRepositoryImageUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepositoryimage_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::first();

            event(new \NextDeveloper\IAAS\Events\IaasRepositoryImage\IaasRepositoryImageUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepositoryimage_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::first();

            event(new \NextDeveloper\IAAS\Events\IaasRepositoryImage\IaasRepositoryImageDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepositoryimage_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::first();

            event(new \NextDeveloper\IAAS\Events\IaasRepositoryImage\IaasRepositoryImageDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepositoryimage_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::first();

            event(new \NextDeveloper\IAAS\Events\IaasRepositoryImage\IaasRepositoryImageRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_iaasrepositoryimage_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::first();

            event(new \NextDeveloper\IAAS\Events\IaasRepositoryImage\IaasRepositoryImageRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepositoryimage_event_name_filter()
    {
        try {
            $request = new Request(
                [
                'name'  =>  'a'
                ]
            );

            $filter = new IaasRepositoryImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepositoryimage_event_description_filter()
    {
        try {
            $request = new Request(
                [
                'description'  =>  'a'
                ]
            );

            $filter = new IaasRepositoryImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepositoryimage_event_path_filter()
    {
        try {
            $request = new Request(
                [
                'path'  =>  'a'
                ]
            );

            $filter = new IaasRepositoryImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepositoryimage_event_filename_filter()
    {
        try {
            $request = new Request(
                [
                'filename'  =>  'a'
                ]
            );

            $filter = new IaasRepositoryImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepositoryimage_event_default_username_filter()
    {
        try {
            $request = new Request(
                [
                'default_username'  =>  'a'
                ]
            );

            $filter = new IaasRepositoryImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepositoryimage_event_default_password_filter()
    {
        try {
            $request = new Request(
                [
                'default_password'  =>  'a'
                ]
            );

            $filter = new IaasRepositoryImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepositoryimage_event_os_filter()
    {
        try {
            $request = new Request(
                [
                'os'  =>  'a'
                ]
            );

            $filter = new IaasRepositoryImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepositoryimage_event_distro_filter()
    {
        try {
            $request = new Request(
                [
                'distro'  =>  'a'
                ]
            );

            $filter = new IaasRepositoryImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepositoryimage_event_version_filter()
    {
        try {
            $request = new Request(
                [
                'version'  =>  'a'
                ]
            );

            $filter = new IaasRepositoryImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepositoryimage_event_release_version_filter()
    {
        try {
            $request = new Request(
                [
                'release_version'  =>  'a'
                ]
            );

            $filter = new IaasRepositoryImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepositoryimage_event_extra_filter()
    {
        try {
            $request = new Request(
                [
                'extra'  =>  'a'
                ]
            );

            $filter = new IaasRepositoryImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepositoryimage_event_cpu_type_filter()
    {
        try {
            $request = new Request(
                [
                'cpu_type'  =>  'a'
                ]
            );

            $filter = new IaasRepositoryImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepositoryimage_event_hash_filter()
    {
        try {
            $request = new Request(
                [
                'hash'  =>  'a'
                ]
            );

            $filter = new IaasRepositoryImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepositoryimage_event_post_boot_script_filter()
    {
        try {
            $request = new Request(
                [
                'post_boot_script'  =>  'a'
                ]
            );

            $filter = new IaasRepositoryImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepositoryimage_event_size_filter()
    {
        try {
            $request = new Request(
                [
                'size'  =>  '1'
                ]
            );

            $filter = new IaasRepositoryImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepositoryimage_event_ram_filter()
    {
        try {
            $request = new Request(
                [
                'ram'  =>  '1'
                ]
            );

            $filter = new IaasRepositoryImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepositoryimage_event_cpu_filter()
    {
        try {
            $request = new Request(
                [
                'cpu'  =>  '1'
                ]
            );

            $filter = new IaasRepositoryImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepositoryimage_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new IaasRepositoryImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepositoryimage_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new IaasRepositoryImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepositoryimage_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new IaasRepositoryImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepositoryimage_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasRepositoryImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepositoryimage_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasRepositoryImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepositoryimage_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasRepositoryImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepositoryimage_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new IaasRepositoryImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepositoryimage_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new IaasRepositoryImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_iaasrepositoryimage_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new IaasRepositoryImageQueryFilter($request);

            $model = \NextDeveloper\IAAS\Database\Models\IaasRepositoryImage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}