<?php

namespace Lexik\Bundle\MaintenanceBundle\Event;

/**
 * An event dispatched just after the unlock command is executed
 *
 * @package Lexik\Bundle\MaintenanceBundle\Event
 */
class PostUnlockEvent extends AbstractPostEvent
{
    const NAME = 'maintenance-bundle.unlock.post';
}
