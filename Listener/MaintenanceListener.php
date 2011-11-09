<?php

namespace Lexik\Bundle\MaintenanceBundle\Listener;

use Lexik\Bundle\MaintenanceBundle\Drivers\DriverFactory;
use Lexik\Bundle\MaintenanceBundle\Exception\ServiceUnavailableException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Listener to decide if user can access to the site
 *
 * @package LexikMaintenanceBundle
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class MaintenanceListener
{
    /**
     * Service driver factory
     *
     * @var \Lexik\Bundle\MaintenanceBundle\Drivers\DriverFactory
     */
    protected $driverFactory;

    /**
     * Authorized ips
     *
     * @var array
     */
    protected $authorizedIps;

    /**
     * Constructor Listener
     *
     * @param DriverFactory $driverFactory The driver factory
     * @param array         $authorizedIps The ips allowed
     */
    public function __construct(DriverFactory $driverFactory, $authorizedIps = null)
    {
        $this->driverFactory = $driverFactory;
        $this->authorizedIps = $authorizedIps;
    }

    /**
     * @param GetResponseEvent $event GetResponseEvent
     *
     * @return void
     *
     * @throws ServiceUnavailableException
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        // Get user's ip
        $ip = $request->server->get('REMOTE_ADDR');

        if ($this->hasAuthorizedIps() && (in_array($ip, $this->authorizedIps))) {
            return;
        }

        // Get driver class defined in your configuration
        $driver = $this->driverFactory->getDriver();

        if ($driver->decide() && HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            throw new ServiceUnavailableException();
        }

        return;
    }

    /**
     * Check if there are authorized's ips
     *
     * @return boolean
     */
    private function hasAuthorizedIps()
    {
        return null != $this->authorizedIps;
    }
}
