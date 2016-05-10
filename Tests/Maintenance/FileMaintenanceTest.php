<?php

namespace Lexik\Bundle\MaintenanceBundle\Tests\Maintenance;

use Lexik\Bundle\MaintenanceBundle\Drivers\FileDriver;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * Test driver file.
 *
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class FileMaintenanceTest extends TestCase
{
    protected static $tmpDir;
    protected $container;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$tmpDir = sys_get_temp_dir().'/symfony2_finder';
    }

    public function setUp()
    {
        $this->container = $this->initContainer();
    }

    public function tearDown()
    {
        $this->container = null;
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionInvalidPath()
    {
        $fileM = new FileDriver(array());
        $fileM->setTranslator($this->getTranslator());
    }

    public function testLock()
    {
        $options = array('file_path' => self::$tmpDir.'/lock.lock');

        $fileM = new FileDriver($options);
        $fileM->setTranslator($this->getTranslator());
        $fileM->lock();

        $this->assertFileExists($options['file_path']);
    }

    public function testUnlock()
    {
        $options = array('file_path' => self::$tmpDir.'/lock.lock');

        $fileM = new FileDriver($options);
        $fileM->setTranslator($this->getTranslator());
        $fileM->lock();

        $fileM->unlock();

        $this->assertFileNotExists($options['file_path']);
    }

    public function testisExist()
    {
        $options = array('file_path' => self::$tmpDir.'/lock.lock', 'ttl' => 3600);

        $fileM = new FileDriver($options);
        $fileM->setTranslator($this->getTranslator());
        $fileM->lock();

        $this->assertTrue($fileM->isEndTime(3600));
    }

    public function testMessages()
    {
        $options = array('file_path' => self::$tmpDir.'/lock.lock', 'ttl' => 3600);

        $fileM = new FileDriver($options);
        $fileM->setTranslator($this->getTranslator());
        $fileM->lock();

        // lock
        $this->assertEquals($fileM->getMessageLock(true), 'lexik_maintenance.success_lock_file');
        $this->assertEquals($fileM->getMessageLock(false), 'lexik_maintenance.not_success_lock');

        // unlock
        $this->assertEquals($fileM->getMessageUnlock(true), 'lexik_maintenance.success_unlock');
        $this->assertEquals($fileM->getMessageUnlock(false), 'lexik_maintenance.not_success_unlock');
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
    }

    protected function initContainer()
    {
        $container = new ContainerBuilder(new ParameterBag(array(
            'kernel.debug' => false,
            'kernel.bundles' => array('MaintenanceBundle' => 'Lexik\Bundle\MaintenanceBundle'),
            'kernel.cache_dir' => sys_get_temp_dir(),
            'kernel.environment' => 'dev',
            'kernel.root_dir' => __DIR__.'/../../../../', // src dir
            'kernel.default_locale' => 'fr',
        )));

        return $container;
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
