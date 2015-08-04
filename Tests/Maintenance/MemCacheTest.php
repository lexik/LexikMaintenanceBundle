<?php

namespace Lexik\Bundle\MaintenanceBundle\Tests\Maintenance;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use Lexik\Bundle\MaintenanceBundle\Drivers\MemCacheDriver;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;

/**
 * Test mem cache
 *
 * @package LexikMaintenanceBundle
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class MemCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructWithNotKeyName()
    {
        $memC = new MemCacheDriver(array());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructWithNotHost()
    {
        $memC = new MemCacheDriver(array('key_name' => 'mnt'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructWithNotPort()
    {
        $memC = new MemCacheDriver(array('key_name' => 'mnt', 'host' => '127.0.0.1'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructWithNotPortNumber()
    {
        $memC = new MemCacheDriver(array('key_name' => 'mnt', 'host' => '127.0.0.1', 'port' => 'roti'));
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
}
