<?php

namespace Lexik\Bundle\MaintenanceBundle\Event;

/**
 * Class AbstractPostEvent
 *
 * @package Lexik\Bundle\MaintenanceBundle\Event
 */
class AbstractPostEvent extends AbstractEvent
{
    /**
     * @var bool
     */
    private $success;

    /**
     * PostUnlockEvent constructor.
     *
     * @param bool $success
     */
    public function __construct($success)
    {
        $this->setSuccess($success);
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @param bool $success
     * @return AbstractPostEvent
     */
    private function setSuccess($success)
    {
        $this->success = $success;

        return $this;
    }
}
