<?php

namespace Lexik\Bundle\MaintenanceBundle\Tests\EventListener;

use Lexik\Bundle\MaintenanceBundle\Drivers\DriverFactory;
use Lexik\Bundle\MaintenanceBundle\Listener\MaintenanceListener;

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
     * -when IP authorized exists
     * -when lock is active
     * -when an exception is throw
     */
    public function testDoRequest()
    {
        $driverOptions = array('class' => DriverFactory::DATABASE_DRIVER, 'options' => null);

        $request = Request::create('http://test.com/foo?bar=baz');
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $this->container = $this->initContainer();

        $this->factory = new DriverFactory($this->getDatabaseDriver(false),$this->getTranslator(), $driverOptions);
        $this->container->set('lexik_maintenance.driver.factory', $this->factory);

        $listener = new MaintenanceListener($this->factory, array());
        $this->assertNull($listener->onKernelRequest($event));

        $listener = new MaintenanceListener($this->factory, array('127.0.0.1'));
        $this->assertNull($listener->onKernelRequest($event));

        $this->factory = new DriverFactory($this->getDatabaseDriver(true), $this->getTranslator(), $driverOptions);
        $this->container->set('lexik_maintenance.driver.factory', $this->factory);

        $listener = new MaintenanceListener($this->factory, array());
        $this->setExpectedException('Lexik\Bundle\MaintenanceBundle\Exception\ServiceUnavailableException');
        $listener->onKernelRequest($event);
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
                'kernel.debug'       => false,
                'kernel.bundles'     => array('MaintenanceBundle' => 'Lexik\Bundle\MaintenanceBundle'),
                'kernel.cache_dir'   => sys_get_temp_dir(),
                'kernel.environment' => 'dev',
                'kernel.root_dir'    => __DIR__.'/../../../../' // src dir
        )));

        return $container;
    }

    /**
     * Get a mock DatabaseDriver
     *
     * @param boolean $lock
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
