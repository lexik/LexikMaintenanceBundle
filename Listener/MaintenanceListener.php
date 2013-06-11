<?php

namespace Lexik\Bundle\MaintenanceBundle\Listener;

use Lexik\Bundle\MaintenanceBundle\Drivers\DriverFactory;
use Lexik\Bundle\MaintenanceBundle\Exception\ServiceUnavailableException;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

use Symfony\Component\HttpFoundation\IpUtils;

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
     * Authorized data
     *
     * @var array
     */
    protected $authorizedIps;
    protected $path;
    protected $host;
    protected $ips;
    protected $query;
    protected $route;
    protected $attributes;

    /**
     * Constructor Listener
     *
     * @param DriverFactory $driverFactory The driver factory
     * @param array         $authorizedIps The ips allowed
     * @param String        $authorizedUri The regex to match the request uri
     * @param String        $authorizedRoute The regex to match the current route
     */
    public function __construct(DriverFactory $driverFactory, $path = null, $host = null, $ips = null, $query = array(), $route = null, $attributes = array())
    {
        $this->driverFactory = $driverFactory;
        $this->path = $path;
        $this->host = $host;
        $this->ips = $ips;
        $this->query = $query;
        $this->route = $route;
        $this->attributes = $attributes;
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
        
        if(is_array($this->query)) {
            foreach ($this->query as $key => $pattern) {
                if (!empty($pattern) && preg_match('{'.$pattern.'}', $request->get($key))) {
                    return false;
                }
            }
        }

        if(is_array($this->attributes)) {
            foreach ($this->attributes as $key => $pattern) {
                if (!empty($pattern) && preg_match('{'.$pattern.'}', $request->attributes->get($key))) {
                    return false;
                }
            }
        }

        if (null !== $this->path && !empty($this->path) && preg_match('{'.$this->path.'}', rawurldecode($request->getPathInfo()))) {
            return;
        }

        if (null !== $this->host && !empty($this->host) && preg_match('{'.$this->host.'}i', $request->getHost())) {
            return;
        }

        if (count($this->ips) !== 0 && IpUtils::checkIp($request->getClientIp(), $this->ips)) {
            return true;
        }

        if (null !== $this->route && !preg_match('{'.$this->route.'}', $request->get('_route'))) {
            return;
        }

        // Get driver class defined in your configuration
        $driver = $this->driverFactory->getDriver();

        if ($driver->decide() && HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            throw new ServiceUnavailableException();
        }

        return;
    }
}
