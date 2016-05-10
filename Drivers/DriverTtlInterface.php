<?php

namespace Lexik\Bundle\MaintenanceBundle\Drivers;

/**
 * Interface DriverTtlInterface.
 *
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
interface DriverTtlInterface
{
    /**
     * Set time to life for overwrite basic configuration.
     *
     * @param int $value ttl value
     */
    public function setTtl($value);

    /**
     * Return time to life.
     *
     * @return int
     */
    public function getTtl();

    /**
     * Has ttl.
     *
     * @return bool
     */
    public function hasTtl();
}
