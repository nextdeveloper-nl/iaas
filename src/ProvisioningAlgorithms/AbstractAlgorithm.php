<?php
/**
 * This file is part of the PlusClouds.IAAS library.
 *
 * (c) Semih Turna <semih.turna@plusclouds.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NextDeveloper\IAAS\Common\ProvisioningAlgorithms;

use NextDeveloper\IAAS\Database\Models\ComputePools;

/**
 * Class AbstractProvisioningAlg
 * @package PlusClouds\IAAS\Common\ProvisioningAlg
 */
abstract class AbstractAlgorithm
{
    /**
     * @var ComputePools
     */
    protected $computePool;

    /**
     * @var mixed
     */
    protected $type;

    /**
     * AbstractProvisioningAlg constructor.
     *
     * @param ComputePools|null $computePool
     */
    public function __construct(ComputePools $computePool = null)
    {
        $this->computePool = $computePool;

        $this->type = $this->computePool->pool_type;
    }

    /**
     * This function will calculate the best compute member for the given resources
     *
     * @param integer $cpu
     * @param integer $ram
     * @return mixed
     */
    abstract public function calculate($ram = 0, $cpu = null);
}
