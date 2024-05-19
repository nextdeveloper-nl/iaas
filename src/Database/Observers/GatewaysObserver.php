<?php

namespace NextDeveloper\IAAS\Database\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use NextDeveloper\Commons\Exceptions\NotAllowedException;
use NextDeveloper\IAM\Helpers\UserHelper;
use NextDeveloper\Events\Services\Events;

/**
 * Class GatewaysObserver
 *
 * @package NextDeveloper\IAAS\Database\Observers
 */
class GatewaysObserver
{
    /**
     * Triggered when a new record is retrieved.
     *
     * @param Model $model
     */
    public function retrieved(Model $model)
    {

    }

    /**
     * @param Model $model
     *
     * @return mixed
     */
    public function creating(Model $model)
    {
        throw_if(
            !UserHelper::can('create', $model),
            new NotAllowedException('You are not allowed to create this record')
        );

        Events::fire('creating:NextDeveloper\IAAS\Gateways', $model);
    }

    /**
     * @param Model $model
     *
     * @return mixed
     */
    public function created(Model $model)
    {
        Events::fire('created:NextDeveloper\IAAS\Gateways', $model);
    }

    /**
     * @param Model $model
     *
     * @return mixed
     */
    public function saving(Model $model)
    {
        throw_if(
            !UserHelper::can('save', $model),
            new NotAllowedException('You are not allowed to save this record')
        );

        Events::fire('saving:NextDeveloper\IAAS\Gateways', $model);
    }

    /**
     * @param Model $model
     *
     * @return mixed
     */
    public function saved(Model $model)
    {
        Events::fire('saved:NextDeveloper\IAAS\Gateways', $model);
    }


    /**
     * @param Model $model
     */
    public function updating(Model $model)
    {
        throw_if(
            !UserHelper::can('update', $model),
            new NotAllowedException('You are not allowed to update this record')
        );

        Events::fire('updating:NextDeveloper\IAAS\Gateways', $model);
    }

    /**
     * @param Model $model
     *
     * @return mixed
     */
    public function updated(Model $model)
    {
        Events::fire('updated:NextDeveloper\IAAS\Gateways', $model);
    }


    /**
     * @param Model $model
     */
    public function deleting(Model $model)
    {
        throw_if(
            !UserHelper::can('delete', $model),
            new NotAllowedException('You are not allowed to delete this record')
        );

        Events::fire('deleting:NextDeveloper\IAAS\Gateways', $model);
    }

    /**
     * @param Model $model
     *
     * @return mixed
     */
    public function deleted(Model $model)
    {
        Events::fire('deleted:NextDeveloper\IAAS\Gateways', $model);
    }

    /**
     * @param Model $model
     *
     * @return mixed
     */
    public function restoring(Model $model)
    {
        throw_if(
            !UserHelper::can('restore', $model),
            new NotAllowedException('You are not allowed to restore this record')
        );

        Events::fire('restoring:NextDeveloper\IAAS\Gateways', $model);
    }

    /**
     * @param Model $model
     *
     * @return mixed
     */
    public function restored(Model $model)
    {
        Events::fire('restored:NextDeveloper\IAAS\Gateways', $model);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}
