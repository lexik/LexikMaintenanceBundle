<?php

namespace Lexik\Bundle\MaintenanceBundle\Event;

/**
 * An event dispatched just before the unlock command is executed
 *
 * @package Lexik\Bundle\MaintenanceBundle\Event
 */
class PreUnlockEvent extends AbstractEvent
{
    const NAME = 'maintenance-bundle.unlock.pre';
}
