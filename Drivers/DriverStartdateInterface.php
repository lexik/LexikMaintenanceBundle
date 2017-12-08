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
