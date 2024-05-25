<?php
/**
 * This file is part of the PlusClouds.IAAS library.
 *
 * (c) Semih Turna <semih.turna@plusclouds.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NextDeveloper\IAAS\ProvisioningAlgorithms\StorageVolumes;

use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Database\Models\StoragePools;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;

/**
 * Class AbstractProvisioningAlg
 * @package PlusClouds\IAAS\Common\ProvisioningAlg
 */
abstract class AbstractStorageVolumeAlgorithm
{
    /**
     * @var ComputePools
     */
    protected $storagePools;

    /**
     * @var mixed
     */
    protected $type;

    /**
     * AbstractProvisioningAlg constructor.
     *
     * @param ComputePools|null $computePool
     */
    public function __construct(StoragePools $storagePools = null)
    {
        $this->storagePools = $storagePools;
    }

    /**
     * This function will calculate the best compute member for the given resources
     *
     * @param integer $cpu
     * @param integer $ram
     * @return mixed
     */
    abstract public function calculate(ComputeMembers $member, $size) : ?StorageVolumes;
}
