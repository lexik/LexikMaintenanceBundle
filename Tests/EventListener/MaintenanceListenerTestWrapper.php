<?php

namespace Lexik\Bundle\MaintenanceBundle\Tests\EventListener;

use Lexik\Bundle\MaintenanceBundle\Listener\MaintenanceListener;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Lexik\Bundle\MaintenanceBundle\Exception\ServiceUnavailableException;

class MaintenanceListenerTestWrapper extends MaintenanceListener {

    public function onKernelRequest(GetResponseEvent $event) {
        try {
            parent::onKernelRequest($event);
        }
        catch (ServiceUnavailableException $e) {
            return false;
        }

        return true;
    }
}

?>
