<?php

namespace Lexik\Bundle\MaintenanceBundle\Drivers;

/**
 * Interface DriverStartdateInterface
 *
 * @package Lexik\Bundle\MaintenanceBundle\Drivers
 * @author  Wolfram Eberius <edrush@posteo.de>
 */
interface DriverStartdateInterface
{

    /**
     * prepare delayed lock
     *
     * @return boolean
     */
    public function prepareLock();

    /**
     * Lock if prepared and start date achieved
     *
     * @return boolean
     */
    public function lockWhenPrepared();
    
    /**
     * Set start date of lock
     *
     * @param \DateTime $value start date value
     */
    public function setStartDate($value);

    /**
     * Return start date
     *
     * @return \DateTime
     */
    public function getStartDate();

}
