<?php

namespace NextDeveloper\IAAS\Events\IaasNetworkPool;

use Illuminate\Queue\SerializesModels;
use NextDeveloper\IAAS\Database\Models\IaasNetworkPool;

/**
 * Class IaasNetworkPoolDeletedEvent
 * @package NextDeveloper\IAAS\Events
 */
class IaasNetworkPoolDeletedEvent
{
    use SerializesModels;

    /**
     * @var IaasNetworkPool
     */
    public $_model;

    /**
     * @var int|null
     */
    protected $timestamp = null;

    public function __construct(IaasNetworkPool $model = null) {
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