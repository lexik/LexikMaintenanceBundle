<?php

namespace Lexik\Bundle\MaintenanceBundle\Tests\Maintenance;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Lexik\Bundle\MaintenanceBundle\Drivers\DriverFactory;

/**
 * Test driver factory
 *
 * @package LexikMaintenanceBundle
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class DriverFactoryTest extends \PHPUnit_Framework_TestCase
{
    protected $factory;
    protected $container;

    public function setUp()
    {
        $driverOptions = array(
            'class' => '\Lexik\Bundle\MaintenanceBundle\Drivers\FileDriver',
            'options' => array('file_path' => sys_get_temp_dir().'/lock'));

        $this->container = $this->initContainer();

        $this->factory = new DriverFactory($this->getDatabaseDriver(), $this->getTranslator(), $driverOptions);
        $this->container->set('lexik_maintenance.driver.factory', $this->factory);
    }

    protected function tearDown()
    {
        $this->factory = null;
    }

    public function testDriver()
    {
        $driver = $this->factory->getDriver();
        $this->assertInstanceOf('\Lexik\Bundle\MaintenanceBundle\Drivers\FileDriver', $driver);
    }

    /**
     * @expectedException ErrorException
     */
    public function testExceptionConstructor()
    {
        $factory = new DriverFactory($this->getDatabaseDriver(), $this->getTranslator(), array());
    }

    public function testWithDatabaseChoice()
    {
        $driverOptions = array('class' => DriverFactory::DATABASE_DRIVER, 'options' => null);

        $factory = new DriverFactory($this->getDatabaseDriver(), $this->getTranslator(), $driverOptions);

        $this->container->set('lexik_maintenance.driver.factory', $factory);

        $this->assertInstanceOf('Lexik\Bundle\MaintenanceBundle\Drivers\DatabaseDriver', $factory->getDriver());
    }

    public function testExceptionGetDriver()
    {
        $driverOptions = array('class' => '\Unknown', 'options' => null);

        $factory = new DriverFactory($this->getDatabaseDriver(), $this->getTranslator(), $driverOptions);
        $this->container->set('lexik_maintenance.driver.factory', $factory);

        try {
            $factory->getDriver();
        } catch (\ErrorException $expected) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

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

    protected function getDatabaseDriver()
    {
        $db = $this->getMockbuilder('Lexik\Bundle\MaintenanceBundle\Drivers\DatabaseDriver')
                ->disableOriginalConstructor()
                ->getMock();

        return $db;
    }

    public function getTranslator()
    {
        $translator = new Translator(
            $this->container,
            $this->getMock('Symfony\Component\Translation\MessageSelector')
        );

        return $translator;
    }
}
