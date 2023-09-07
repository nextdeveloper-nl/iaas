<?php

namespace NextDeveloper\IAAS\Events\IaasCloudNode;

use Illuminate\Queue\SerializesModels;
use NextDeveloper\IAAS\Database\Models\IaasCloudNode;

/**
 * Class IaasCloudNodeRestoredEvent
 * @package NextDeveloper\IAAS\Events
 */
class IaasCloudNodeRestoredEvent
{
    use SerializesModels;

    /**
     * @var IaasCloudNode
     */
    public $_model;

    /**
     * @var int|null
     */
    protected $timestamp = null;

    public function __construct(IaasCloudNode $model = null) {
        $this->_model = $model;
    }

    /**
    * @param int $value
    *
    * @return AbstractEvent
    */
    public function setTimestamp($value) {
        $this->timestamp = $value;

        return $this;
    }

    /**
    * @return int|null
    */
    public function getTimestamp() {
        return $this->timestamp;
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}