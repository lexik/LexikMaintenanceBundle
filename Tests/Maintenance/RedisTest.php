<?php

namespace Lexik\Bundle\MaintenanceBundle\Tests\Maintenance;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use Lexik\Bundle\MaintenanceBundle\Drivers\RedisDriver;

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

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructWithNotTtl()
    {
        $redis = new RedisDriver(array('key_name' => 'mnt', 'host' => '127.0.0.1', 'ttl' => 'roti'));
    }

    public function testConstruct()
    {
        $redis = new RedisDriver(array('key_name' => 'mnt', 'host' => '127.0.0.1', 'port' => 6379));

        $this->assertInstanceOf('Lexik\Bundle\MaintenanceBundle\Drivers\RedisDriver', $redis);
    }

    public function testConstructWithTtl()
    {
        $redis = new RedisDriver(array('key_name' => 'mnt', 'host' => '127.0.0.1', 'port' => 6379, 'ttl' => 10));

        $this->assertInstanceOf('Lexik\Bundle\MaintenanceBundle\Drivers\RedisDriver', $redis);
    }

    public function testConstructWithTtlZero()
    {
        $redis = new RedisDriver(array('key_name' => 'mnt', 'host' => '127.0.0.1', 'port' => 6379, 'ttl' => 0));

        $this->assertInstanceOf('Lexik\Bundle\MaintenanceBundle\Drivers\RedisDriver', $redis);
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
