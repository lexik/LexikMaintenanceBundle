<?php

namespace Lexik\Bundle\MaintenanceBundle\Event;

/**
 * An event dispatched just after the lock command is executed
 *
 * @package Lexik\Bundle\MaintenanceBundle\Event
 */
class PostLockEvent extends AbstractPostEvent
{
    const NAME = 'maintenance-bundle.lock.post';
}
