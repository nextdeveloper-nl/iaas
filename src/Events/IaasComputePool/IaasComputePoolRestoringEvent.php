<?php

namespace NextDeveloper\IAAS\Events\IaasComputePool;

use Illuminate\Queue\SerializesModels;
use NextDeveloper\IAAS\Database\Models\IaasComputePool;

/**
 * Class IaasComputePoolRestoringEvent
 * @package NextDeveloper\IAAS\Events
 */
class IaasComputePoolRestoringEvent
{
    use SerializesModels;

    /**
     * @var IaasComputePool
     */
    public $_model;

    /**
     * @var int|null
     */
    protected $timestamp = null;

    public function __construct(IaasComputePool $model = null) {
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