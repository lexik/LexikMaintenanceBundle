<?php

namespace Lexik\Bundle\MaintenanceBundle\Tests\Event;

use Lexik\Bundle\MaintenanceBundle\Drivers\DriverFactory;
use Lexik\Bundle\MaintenanceBundle\Event\PostLockEvent;
use Lexik\Bundle\MaintenanceBundle\Event\PostUnlockEvent;
use Lexik\Bundle\MaintenanceBundle\Event\PreLockEvent;
use Lexik\Bundle\MaintenanceBundle\Event\PreUnlockEvent;

class EventDispatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider createLockTestData
     * @param bool      $exists
     * @param bool|null $createLock
     * @param bool      $expectedEvents
     */
    public function testLock($exists, $createLock, $expectedEvents)
    {
        $eventDispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();
        if ($expectedEvents) {
            $eventDispatcher->expects(self::at(0))
                ->method('dispatch')
                ->with(PreLockEvent::NAME, new PreLockEvent());

            $eventDispatcher->expects(self::at(1))
                ->method('dispatch')
                ->with(PostLockEvent::NAME, new PostLockEvent($createLock));
        }

        $driver = $this->getMockBuilder('Lexik\Bundle\MaintenanceBundle\Drivers\FileDriver')->disableOriginalConstructor()->setMethods(array('isExists', 'createLock'))->getMock();
        $driver->expects(self::any())
            ->method('isExists')
            ->willReturn($exists);
        $driver->expects(self::any())
            ->method('createLock')
            ->willReturn($createLock);

        $driver->setEventDispatcher($eventDispatcher);
        $driver->lock();
    }

    public function createLockTestData()
    {
        return array(
            'Lock exists' => array(
                'exists' => true,
                'createLock' => null,
                'expectedEvents' => false,
            ),
            'Lock does not exists / Created successfully' => array(
                'exists' => false,
                'createLock' => true,
                'expectedEvents' => true,
            ),
            'Lock does not exists / Not created successfully' => array(
                'exists' => false,
                'createLock' => true,
                'expectedEvents' => true,
            ),
        );
    }

    /**
     * @dataProvider createUnlockTestData
     * @param $exists
     * @param $createLock
     * @param $expectedEvents
     */
    public function testUnlock($exists, $createUnlock, $expectedEvents)
    {
        $eventDispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();
        if ($expectedEvents) {
            $eventDispatcher->expects(self::at(0))
                ->method('dispatch')
                ->with(PreUnlockEvent::NAME, new PreUnlockEvent());

            $eventDispatcher->expects(self::at(1))
                ->method('dispatch')
                ->with(PostUnlockEvent::NAME, new PostUnlockEvent($createUnlock));
        }

        $driver = $this->getMockBuilder('Lexik\Bundle\MaintenanceBundle\Drivers\FileDriver')->disableOriginalConstructor()->setMethods(array('isExists', 'createUnlock'))->getMock();
        $driver->expects(self::any())
            ->method('isExists')
            ->willReturn($exists);
        $driver->expects(self::any())
            ->method('createUnlock')
            ->willReturn($createUnlock);

        $driver->setEventDispatcher($eventDispatcher);
        $driver->unlock();
    }

    public function createUnlockTestData()
    {
        return array(
            'Lock does not exists' => array(
                'exists' => false,
                'createUnlock' => null,
                'expectedEvents' => false,
            ),
            'Lock exists / Created successfully' => array(
                'exists' => true,
                'createUnlock' => true,
                'expectedEvents' => true,
            ),
            'Lock exists / Not created successfully' => array(
                'exists' => true,
                'createUnlock' => true,
                'expectedEvents' => true,
            ),
        );
    }

    /**
     * @param array $mocks
     * @param array $driverOptions
     * @return DriverFactory
     * @throws \ErrorException
     */
    public function createDriverFactory(array $mocks = array(), array $driverOptions = array())
    {
        $dbDriver = array_key_exists('dbDriver', $mocks) ? $mocks['dbDriver'] : $this->getMockBuilder('Lexik\Bundle\MaintenanceBundle\Drivers\DatabaseDriver')->disableOriginalConstructor()->getMock();
        $translator = array_key_exists('translator', $mocks) ? $mocks['translator'] : $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')->getMock();
        $eventDispatcher = array_key_exists('eventDispatcher', $mocks) ? $mocks['eventDispatcher'] : $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();

        return new DriverFactory($dbDriver, $translator, $eventDispatcher, $driverOptions);
    }
}
