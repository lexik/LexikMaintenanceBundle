<?php

namespace Lexik\Bundle\MaintenanceBundle\Listener;

use Lexik\Bundle\MaintenanceBundle\Drivers\DriverFactory;
use Lexik\Bundle\MaintenanceBundle\Exception\ServiceUnavailableException;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
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

    /**
     * @var null|String
     */
    protected $path;

    /**
     * @var null|String
     */
    protected $host;

    /**
     * @var array|null
     */
    protected $ips;

    /**
     * @var array
     */
    protected $query;

    /**
     * @var array
     */
    protected $cookie;

    /**
     * @var null|String
     */
    protected $route;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * @var Int|null
     */
    protected $http_code;

    /**
     * @var null|String
     */
    protected $http_status;

    /**
     * @var bool
     */
    protected $handleResponse = false;

    /**
     * @var bool
     */
    protected $debug;

    /**
     * Constructor Listener
     *
     * Accepts a driver factory, and several arguments to be compared against the
     * incoming request.
     * When the maintenance mode is enabled, the request will be allowed to bypass
     * it if at least one of the provided arguments is not empty and matches the
     *  incoming request.
     *
     * @param DriverFactory $driverFactory The driver factory
     * @param String $path A regex for the path
     * @param String $host A regex for the host
     * @param array $ips The list of IP addresses
     * @param array $query Query arguments
     * @param array $cookie Cookies
     * @param String $route Route name
     * @param array $attributes Attributes
     * @param Int $http_code http status code for response
     * @param String $http_status http status message for response
     * @param bool $debug
     */
    public function __construct(
        DriverFactory $driverFactory,
        $path = null,
        $host = null,
        $ips = null,
        $query = array(),
        $cookie = array(),
        $route = null,
        $attributes = array(),
        $http_code = null,
        $http_status = null,
        $debug = false
    ) {
        $this->driverFactory = $driverFactory;
        $this->path = $path;
        $this->host = $host;
        $this->ips = $ips;
        $this->query = $query;
        $this->cookie = $cookie;
        $this->route = $route;
        $this->attributes = $attributes;
        $this->http_code = $http_code;
        $this->http_status = $http_status;
        $this->debug = $debug;
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

        if (is_array($this->query)) {
            foreach ($this->query as $key => $pattern) {
                if (!empty($pattern) && preg_match('{'.$pattern.'}', $request->get($key))) {
                    return;
                }
            }
        }

        if (is_array($this->cookie)) {
            foreach ($this->cookie as $key => $pattern) {
                if (!empty($pattern) && preg_match('{'.$pattern.'}', $request->cookies->get($key))) {
                    return;
                }
            }
        }

        if (is_array($this->attributes)) {
            foreach ($this->attributes as $key => $pattern) {
                if (!empty($pattern) && preg_match('{'.$pattern.'}', $request->attributes->get($key))) {
                    return;
                }
            }
        }

        if (null !== $this->path && !empty($this->path) && preg_match('{'.$this->path.'}', rawurldecode($request->getPathInfo()))) {
            return;
        }

        if (null !== $this->host && !empty($this->host) && preg_match('{'.$this->host.'}i', $request->getHost())) {
            return;
        }

        if (count($this->ips) !== 0 && $this->checkIps($request->getClientIp(), $this->ips)) {
            return;
        }

        $route = $request->get('_route');
        if (null !== $this->route && preg_match('{'.$this->route.'}', $route)  || (true === $this->debug && '_' === $route[0])) {
            return;
        }

        // Get driver class defined in your configuration
        $driver = $this->driverFactory->getDriver();

        if ($driver->decide() && HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            $this->handleResponse = true;
            throw new ServiceUnavailableException();
        }

        return;
    }

    /**
     * Rewrites the http code of the response
     *
     * @param FilterResponseEvent $event FilterResponseEvent
     * @return void
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if ($this->handleResponse && $this->http_code !== null) {
            $response = $event->getResponse();
            $response->setStatusCode($this->http_code, $this->http_status);
        }
    }

    /**
     * Checks if the requested ip is valid.
     *
     * @param string       $requestedIp
     * @param string|array $ips
     * @return boolean
     */
    protected function checkIps($requestedIp, $ips)
    {
        $ips = (array) $ips;

        $valid = false;
        $i = 0;

        while ($i<count($ips) && !$valid) {
            $valid = IpUtils::checkIp($requestedIp, $ips[$i]);
            $i++;
        }

        return $valid;
    }
}
