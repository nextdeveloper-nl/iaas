<?php

namespace NextDeveloper\IAAS\Events\StorageMembers;

use Illuminate\Queue\SerializesModels;
use NextDeveloper\IAAS\Database\Models\StorageMembers;

/**
 * Class sStorageMembersSavingEvent
 *
 * @package NextDeveloper\IAAS\Events
 */
class sStorageMembersSavingEvent
{
    use SerializesModels;

    /**
     * @var StorageMembers
     */
    public $_model;

    /**
     * @var int|null
     */
    protected $timestamp = null;

    public function __construct(StorageMembers $model = null)
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