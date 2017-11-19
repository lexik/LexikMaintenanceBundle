<?php

namespace Lexik\Bundle\MaintenanceBundle\Tests\Maintenance;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use Lexik\Bundle\MaintenanceBundle\Drivers\RedisDriver;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;

/**
 * Test redis
 *
 * @package LexikMaintenanceBundle
 * @author  Burak Bolat <brkblt@gmail.com>
 */
class RedisTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructWithNotKeyName()
    {
        $redis = new RedisDriver(array());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructWithNotHost()
    {
        $redis = new RedisDriver(array('key_name' => 'mnt'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructWithNotPort()
    {
        $redis = new RedisDriver(array('key_name' => 'mnt', 'host' => '127.0.0.1'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructWithNotPortNumber()
    {
        $redis = new RedisDriver(array('key_name' => 'mnt', 'host' => '127.0.0.1', 'port' => 'roti'));
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
