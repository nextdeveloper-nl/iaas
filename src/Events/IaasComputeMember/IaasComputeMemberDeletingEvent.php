<?php

namespace NextDeveloper\IAAS\Events\IaasComputeMember;

use Illuminate\Queue\SerializesModels;
use NextDeveloper\IAAS\Database\Models\IaasComputeMember;

/**
 * Class IaasComputeMemberDeletingEvent
 * @package NextDeveloper\IAAS\Events
 */
class IaasComputeMemberDeletingEvent
{
    use SerializesModels;

    /**
     * @var IaasComputeMember
     */
    public $_model;

    /**
     * @var int|null
     */
    protected $timestamp = null;

    public function __construct(IaasComputeMember $model = null) {
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