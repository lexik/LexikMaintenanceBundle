<?php

namespace Lexik\Bundle\MaintenanceBundle\Drivers;

/**
 * Interface DriverTtlInterface
 *
 * @package Lexik\Bundle\MaintenanceBundle\Drivers
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
interface DriverTtlInterface
{
    /**
     * Set time to live for overwrite basic configuration
     *
     * @param integer $value ttl value
     */
    public function setTtl($value);

    /**
     * Return time to live
     *
     * @return integer
     */
    public function getTtl();

    /**
     * Has ttl
     *
     * @return bool
     */
    public function hasTtl();
}
