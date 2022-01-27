<?php

namespace Lexik\Bundle\MaintenanceBundle\Tests\Maintenance;

use Lexik\Bundle\MaintenanceBundle\Drivers\RedisDriver;

/**
 * Class RedisDriverTest
 *
 * @package Lexik\Bundle\MaintenanceBundle\Tests\Maintenance
 */
class RedisDriverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructWithoutKeyName()
    {
        $redis = new RedisDriver(array());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructWithoutConnectionParameters()
    {
        $redis = new RedisDriver(array('key_name' => 'foo'));
    }

    public function testTtlIsSetWithDefaultValue()
    {
        $redis = new RedisDriver(array('key_name' => 'foo', 'connection_parameters' => 'localhost'));

        $this->assertTrue($redis->hasTtl());
        $this->assertEquals(0, $redis->getTtl());
    }

    public function testTtlIsSetWithCustomValue()
    {
        $redis = new RedisDriver(array('key_name' => 'foo', 'connection_parameters' => 'localhost', 'ttl' => 1234));

        $this->assertTrue($redis->hasTtl());
        $this->assertEquals(1234, $redis->getTtl());
    }

    public function testTtlIsSetWithCustomValueAsString()
    {
        $redis = new RedisDriver(array('key_name' => 'foo', 'connection_parameters' => 'localhost', 'ttl' => '1234'));

        $this->assertTrue($redis->hasTtl());
        $this->assertEquals(1234, $redis->getTtl());
    }

    public function testKeyName()
    {
        $redis = new RedisDriver(array('key_name' => 'foo', 'connection_parameters' => 'localhost'));

        $property = new \ReflectionProperty($redis, 'keyName');
        $property->setAccessible(true);

        $this->assertEquals('foo', $property->getValue($redis));
    }

    public function testRedisInstance()
    {
        $redis = new RedisDriver(array('key_name' => 'foo', 'connection_parameters' => 'localhost'));

        $property = new \ReflectionProperty($redis, 'redisInstance');
        $property->setAccessible(true);

        $this->assertNotEmpty($property->getValue($redis));
        $this->assertInstanceOf('\Predis\Client', $property->getValue($redis));
    }
}
