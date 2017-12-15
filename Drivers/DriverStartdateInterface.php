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
     * schedule delayed lock
     *
     * @return boolean
     */
    public function scheduleLock();

    /**
     * unschedule lock
     *
     * @return boolean
     */
    public function unscheduleLock();
    
    /**
     * Lock if scheduled and start date achieved
     *
     * @return boolean
     */
    public function lockWhenScheduled();
    
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

    /**
     * Test if maintenace is scheduled
     *
     * @return boolean
     */
    public function isExistsSchedule();

}
