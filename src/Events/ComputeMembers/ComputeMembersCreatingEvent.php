<?php

namespace NextDeveloper\IAAS\Events\ComputeMembers;

use Illuminate\Queue\SerializesModels;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;

/**
 * Class ComputeMembersCreatingEvent
 *
 * @package NextDeveloper\IAAS\Events
 */
class ComputeMembersCreatingEvent
{
    use SerializesModels;

    /**
     * @var ComputeMembers
     */
    public $_model;

    /**
     * @var int|null
     */
    protected $timestamp = null;

    public function __construct(ComputeMembers $model = null)
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