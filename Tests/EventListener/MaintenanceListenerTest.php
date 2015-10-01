<?php

namespace Lexik\Bundle\MaintenanceBundle\Tests\EventListener;

use Lexik\Bundle\MaintenanceBundle\Drivers\DriverFactory;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Test for the maintenance listener
 *
 * @package LexikMaintenanceBundle
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class MaintenanceListenerTest extends \PHPUnit_Framework_TestCase
{
    protected
        $container,
        $factory;

    /**
     * Create request and test the listener
     * for scenarios with permissive firewall
     * and restrictive with no arguments
     */
    public function testBaseRequest()
    {
        $driverOptions = array('class' => DriverFactory::DATABASE_DRIVER, 'options' => null);

        $request = Request::create('http://test.com/foo?bar=baz');
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $this->container = $this->initContainer();

        $this->factory = new DriverFactory($this->getDatabaseDriver(false),$this->getTranslator(), $driverOptions);
        $this->container->set('lexik_maintenance.driver.factory', $this->factory);

        $listener = new MaintenanceListenerTestWrapper($this->factory);
        $this->assertTrue($listener->onKernelRequest($event), 'Permissive factory should approve without args');

        $listener = new MaintenanceListenerTestWrapper($this->factory, 'path', 'host', array('ip'), array('query'), array('cookie'), 'route');
        $this->assertTrue($listener->onKernelRequest($event), 'Permissive factory should approve with args');

        $this->factory = new DriverFactory($this->getDatabaseDriver(true), $this->getTranslator(), $driverOptions);
        $this->container->set('lexik_maintenance.driver.factory', $this->factory);

        $listener = new MaintenanceListenerTestWrapper($this->factory);
        $this->assertFalse($listener->onKernelRequest($event), 'Restrictive factory should deny without args');

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, array(), array(), array(), null);
        $this->assertFalse($listener->onKernelRequest($event), 'Restrictive factory should deny without args');
    }

    /**
     * Create request and test the listener
     * for scenarios with permissive firewall
     * and path filters
     */
    public function testPathFilter()
    {
        $driverOptions = array('class' => DriverFactory::DATABASE_DRIVER, 'options' => null);

        $request = Request::create('http://test.com/foo?bar=baz');
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $this->container = $this->initContainer();

        $this->factory = new DriverFactory($this->getDatabaseDriver(true), $this->getTranslator(), $driverOptions);
        $this->container->set('lexik_maintenance.driver.factory', $this->factory);

        $listener = new MaintenanceListenerTestWrapper($this->factory, null);
        $this->assertFalse($listener->onKernelRequest($event), 'Restrictive factory should deny without path');

        $listener = new MaintenanceListenerTestWrapper($this->factory, '');
        $this->assertFalse($listener->onKernelRequest($event), 'Restrictive factory should deny with empty path');

        $listener = new MaintenanceListenerTestWrapper($this->factory, '/bar');
        $this->assertFalse($listener->onKernelRequest($event), 'Restrictive factory should deny on non matching path');

        $listener = new MaintenanceListenerTestWrapper($this->factory, '/foo');
        $this->assertTrue($listener->onKernelRequest($event), 'Restrictive factory should allow on matching path');
    }

    /**
     * Create request and test the listener
     * for scenarios with permissive firewall
     * and path filters
     */
    public function testHostFilter()
    {
        $driverOptions = array('class' => DriverFactory::DATABASE_DRIVER, 'options' => null);

        $request = Request::create('http://test.com/foo?bar=baz');
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $this->container = $this->initContainer();

        $this->factory = new DriverFactory($this->getDatabaseDriver(true), $this->getTranslator(), $driverOptions);
        $this->container->set('lexik_maintenance.driver.factory', $this->factory);

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null);
        $this->assertFalse($listener->onKernelRequest($event), 'Restrictive factory should deny without host');

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, '');
        $this->assertFalse($listener->onKernelRequest($event), 'Restrictive factory should deny with empty host');

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, 'www.google.com');
        $this->assertFalse($listener->onKernelRequest($event), 'Restrictive factory should deny on non matching host');

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, 'test.com');
        $this->assertTrue($listener->onKernelRequest($event), 'Restrictive factory should allow on matching host');

        $listener = new MaintenanceListenerTestWrapper($this->factory, '/barfoo', 'test.com');
        $this->assertTrue($listener->onKernelRequest($event), 'Restrictive factory should allow on non-matching path and matching host');
    }

    /**
     * Create request and test the listener
     * for scenarios with permissive firewall
     * and ip filters
     */
    public function testIPFilter()
    {
        $driverOptions = array('class' => DriverFactory::DATABASE_DRIVER, 'options' => null);

        $request = Request::create('http://test.com/foo?bar=baz');
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $this->container = $this->initContainer();

        $this->factory = new DriverFactory($this->getDatabaseDriver(true), $this->getTranslator(), $driverOptions);
        $this->container->set('lexik_maintenance.driver.factory', $this->factory);

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, null);
        $this->assertFalse($listener->onKernelRequest($event), 'Restrictive factory should deny without ips');

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, array());
        $this->assertFalse($listener->onKernelRequest($event), 'Restrictive factory should deny with empty ips');

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, array('8.8.4.4'));
        $this->assertFalse($listener->onKernelRequest($event), 'Restrictive factory should deny on non matching ips');

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, array('127.0.0.1'));
        $this->assertTrue($listener->onKernelRequest($event), 'Restrictive factory should allow on matching ips');

        $listener = new MaintenanceListenerTestWrapper($this->factory, '/barfoo', 'google.com', array('127.0.0.1'));
        $this->assertTrue($listener->onKernelRequest($event), 'Restrictive factory should allow on non-matching path and host and matching ips');
    }

    /**
     * @dataProvider routeProviderWithDebugContext
     */
    public function testRouteFilter($debug, $route, $expected)
    {
        $driverOptions = array('class' => DriverFactory::DATABASE_DRIVER, 'options' => null);

        $request = Request::create('');
        $request->attributes->set('_route', $route);

        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $this->container = $this->initContainer();

        $this->factory = new DriverFactory($this->getDatabaseDriver(true), $this->getTranslator(), $driverOptions);
        $this->container->set('lexik_maintenance.driver.factory', $this->factory);

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, null, array(), array(), $debug);

        $info = sprintf('Should be %s route %s with when we are %s debug env',
            $expected === true ? 'allow' : 'deny',
            $route,
            $debug === true ? 'in' : 'not in'
        );

        $this->assertTrue($listener->onKernelRequest($event) === $expected, $info);
    }

    public function routeProviderWithDebugContext()
    {
        $debug = array(true, false);
        $routes = array('route_1', '_route_started_with_underscore');

        $data = array();

        foreach ($debug as $isDebug) {
            foreach ($routes as $route) {
                $data[] = array($isDebug, $route, (true === $isDebug && '_' === $route[0]) ? false : true);
            }
        }

        return $data;
    }


    /**
     * Create request and test the listener
     * for scenarios with permissive firewall
     * and query filters
     */
    public function testQueryFilter()
    {
        $driverOptions = array('class' => DriverFactory::DATABASE_DRIVER, 'options' => null);

        $request = Request::create('http://test.com/foo?bar=baz');
        $postRequest = Request::create('http://test.com/foo?bar=baz', 'POST');
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);
        $postEvent = new GetResponseEvent($kernel, $postRequest, HttpKernelInterface::MASTER_REQUEST);

        $this->container = $this->initContainer();

        $this->factory = new DriverFactory($this->getDatabaseDriver(true), $this->getTranslator(), $driverOptions);
        $this->container->set('lexik_maintenance.driver.factory', $this->factory);

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, null, null);
        $this->assertFalse($listener->onKernelRequest($event), 'Restrictive factory should deny without query');

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, null, array());
        $this->assertFalse($listener->onKernelRequest($event), 'Restrictive factory should deny with empty query');

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, null, array('some' => 'attribute'));
        $this->assertFalse($listener->onKernelRequest($event), 'Restrictive factory should deny on non matching query');

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, null, array('attribute'));
        $this->assertFalse($listener->onKernelRequest($event), 'Restrictive factory should deny on non matching query');

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, null, array('bar' => 'baz'));
        $this->assertTrue($listener->onKernelRequest($event), 'Restrictive factory should allow on matching get query');

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, null, array('bar' => 'baz'));
        $this->assertTrue($listener->onKernelRequest($postEvent), 'Restrictive factory should allow on matching post query');

        $listener = new MaintenanceListenerTestWrapper($this->factory, '/barfoo', 'google.com', array('8.8.1.1'), array('bar' => 'baz'));
        $this->assertTrue($listener->onKernelRequest($event), 'Restrictive factory should allow on non-matching path, host and ip and matching query');
    }

    /**
     * Create request and test the listener
     * for scenarios with permissive firewall
     * and cookie filters
     */
    public function testCookieFilter()
    {
        $driverOptions = array('class' => DriverFactory::DATABASE_DRIVER, 'options' => null);

        $request = Request::create('http://test.com/foo', 'GET', array(), array('bar' => 'baz'));
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $this->container = $this->initContainer();

        $this->factory = new DriverFactory($this->getDatabaseDriver(true), $this->getTranslator(), $driverOptions);
        $this->container->set('lexik_maintenance.driver.factory', $this->factory);

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, null, null, null);
        $this->assertFalse($listener->onKernelRequest($event), 'Restrictive factory should deny without cookies');

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, null, null, array());
        $this->assertFalse($listener->onKernelRequest($event), 'Restrictive factory should deny with empty cookies');

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, null, null, array('some' => 'attribute'));
        $this->assertFalse($listener->onKernelRequest($event), 'Restrictive factory should deny on non matching cookie');

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, null, null, array('attribute'));
        $this->assertFalse($listener->onKernelRequest($event), 'Restrictive factory should deny on non matching cookie');

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, null, null, array('bar' => 'baz'));
        $this->assertTrue($listener->onKernelRequest($event), 'Restrictive factory should allow on matching cookie');

        $listener = new MaintenanceListenerTestWrapper($this->factory, '/barfoo', 'google.com', array('8.8.1.1'), array('bar' => 'baz'), array('bar' => 'baz'));
        $this->assertTrue($listener->onKernelRequest($event), 'Restrictive factory should allow on non-matching path, host, ip, query and matching cookie');
    }


    public function tearDown()
    {
        $this->container = null;
        $this->factory   = null;
    }

    /**
     * Init a container
     *
     * @return Container
     */
    protected function initContainer()
    {
        $container = new ContainerBuilder(new ParameterBag(array(
            'kernel.debug'          => false,
            'kernel.bundles'        => array('MaintenanceBundle' => 'Lexik\Bundle\MaintenanceBundle'),
            'kernel.cache_dir'      => sys_get_temp_dir(),
            'kernel.environment'    => 'dev',
            'kernel.root_dir'       => __DIR__.'/../../../../', // src dir
            'kernel.default_locale' => 'fr',
        )));

        return $container;
    }

    /**
     * Get a mock DatabaseDriver
     *
     * @param boolean $lock
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getDatabaseDriver($lock = false)
    {
        $db = $this->getMockbuilder('Lexik\Bundle\MaintenanceBundle\Drivers\DatabaseDriver')
            ->disableOriginalConstructor()
            ->getMock();

        $db->expects($this->any())
            ->method('isExists')
            ->will($this->returnValue($lock));

        $db->expects($this->any())
            ->method('decide')
            ->will($this->returnValue($lock));

        return $db;
    }

    /**
     * Get Translator
     *
     * @return \Symfony\Bundle\FrameworkBundle\Translation\Translator
     */
    public function getTranslator()
    {
        $translator = new Translator(
            $this->container,
            $this->getMock('Symfony\Component\Translation\MessageSelector')
        );

        return $translator;
    }
}
