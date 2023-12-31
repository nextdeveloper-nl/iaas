<?php

namespace NextDeveloper\IAAS\Events\VirtualMachines;

use Illuminate\Queue\SerializesModels;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;

/**
 * Class VirtualMachinesUpdatedEvent
 *
 * @package NextDeveloper\IAAS\Events
 */
class VirtualMachinesUpdatedEvent
{
    use SerializesModels;

    /**
     * @var VirtualMachines
     */
    public $_model;

    /**
     * @var int|null
     */
    protected $timestamp = null;

    public function __construct(VirtualMachines $model = null)
    {
        $this->_model = $model;
    }

    /**
     * @param int $value
     *
     * @return AbstractEvent
     */
    public function setTimestamp($value)
    {
        $this->timestamp = $value;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}