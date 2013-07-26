<?php

namespace Lexik\Bundle\MaintenanceBundle\Tests\EventListener;

use Lexik\Bundle\MaintenanceBundle\Listener\MaintenanceListener;
use Lexik\Bundle\MaintenanceBundle\Exception\ServiceUnavailableException;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class MaintenanceListenerTestWrapper extends MaintenanceListener
{
    /**
     * {@inheritdoc}
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        try {
            parent::onKernelRequest($event);
        }
        catch (ServiceUnavailableException $e) {
            return false;
        }

        return true;
    }
}
