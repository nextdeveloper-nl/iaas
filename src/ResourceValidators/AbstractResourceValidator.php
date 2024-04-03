<?php

namespace ResourceValidators;

use NextDeveloper\IAAS\Database\Models\ComputePools;

abstract class AbstractResourceValidator
{
    /**
     * @var ComputePools
     */
    protected $computePool;

    public function __construct(ComputePools $computePool)
    {
        $this->computePool = $computePool;
    }

    /**
     * @param int $cpu vCPU count
     * @param int $ram Amount of ram in terms of GB
     * @param int $harddisk Amount of harddisk in terms of GB
     * @return mixed
     */
    abstract public function validate($cpu, $ram, $harddisk = 0);
}
