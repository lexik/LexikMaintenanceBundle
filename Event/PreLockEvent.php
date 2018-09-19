<?php

namespace Lexik\Bundle\MaintenanceBundle\Event;

/**
 * An event dispatched just before the lock command is executed
 *
 * @package Lexik\Bundle\MaintenanceBundle\Event
 */
class PreLockEvent extends AbstractEvent
{
    const NAME = 'maintenance-bundle.lock.pre';
}
