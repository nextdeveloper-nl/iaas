<?php

namespace NextDeveloper\IAAS\Events\StoragePools;

use Illuminate\Queue\SerializesModels;
use NextDeveloper\IAAS\Database\Models\StoragePools;

/**
 * Class StoragePoolsRetrievedEvent
 *
 * @package NextDeveloper\IAAS\Events
 */
class StoragePoolsRetrievedEvent
{
    use SerializesModels;

    /**
     * @var StoragePools
     */
    public $_model;

    /**
     * @var int|null
     */
    protected $timestamp = null;

    public function __construct(StoragePools $model = null)
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