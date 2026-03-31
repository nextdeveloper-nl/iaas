<?php

namespace NextDeveloper\IAAS\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\CustomerResourcesPerspectiveQueryFilter;
use NextDeveloper\IAAS\Database\Models\CustomerResourcesPerspective;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This class is responsible from managing the data for CustomerResourcesPerspective
 *
 * Class CustomerResourcesPerspectiveService.
 *
 * @package NextDeveloper\IAAS\Services
 */
class CustomerResourcesPerspectiveService
{
    public static function get(CustomerResourcesPerspectiveQueryFilter $filter = null, array $params = []): Collection|LengthAwarePaginator
    {
        $enablePaginate = array_key_exists('paginate', $params);

        $request = new Request();

        if ($filter == null) {
            $filter = new CustomerResourcesPerspectiveQueryFilter($request);
        }

        $perPage = config('commons.pagination.per_page');

        if ($perPage == null) {
            $perPage = 20;
        }

        if (array_key_exists('per_page', $params)) {
            $perPage = intval($params['per_page']);

            if ($perPage == 0) {
                $perPage = 20;
            }
        }

        if (array_key_exists('orderBy', $params)) {
            $filter->orderBy($params['orderBy']);
        }

        $model = CustomerResourcesPerspective::withoutGlobalScope(AuthorizationScope::class)->filter($filter);

        if ($enablePaginate) {
            $modelCount = $model->count();
            $page = array_key_exists('page', $params) ? $params['page'] : 1;
            $items = $model->skip(($page - 1) * $perPage)->take($perPage)->get();

            return new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $modelCount,
                $perPage,
                $page
            );
        }

        return $model->get();
    }

    public static function getAll()
    {
        return CustomerResourcesPerspective::all();
    }

    /**
     * This method returns the model by looking at reference id
     *
     * @param  $ref
     * @return mixed
     */
    public static function getByRef($ref): ?CustomerResourcesPerspective
    {
        return CustomerResourcesPerspective::where('resource_uuid', $ref)->first();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}
