<?php

namespace Lexik\Bundle\MaintenanceBundle\Drivers;

use Lexik\Bundle\MaintenanceBundle\Event\PostLockEvent;
use Lexik\Bundle\MaintenanceBundle\Event\PostUnlockEvent;
use Lexik\Bundle\MaintenanceBundle\Event\PreLockEvent;
use Lexik\Bundle\MaintenanceBundle\Event\PreUnlockEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Abstract class for drivers
 *
 * @package LexikMaintenanceBundle
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
abstract class AbstractDriver
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Constructor
     *
     * @param array $options Array of options
     */
    public function __construct(array $options = array())
    {
        $this->options = $options;
    }

    /**
     * Test if object exists
     *
     * @return boolean
     */
    abstract public function isExists();

    /**
     * Result of creation of lock
     *
     * @return boolean
     */
    abstract protected function createLock();

    /**
     * Result of create unlock
     *
     * @return boolean
     */
    abstract protected function createUnlock();

    /**
     * The feedback message
     *
     * @param boolean $resultTest The result of lock
     *
     * @return string
     */
    abstract public function getMessageLock($resultTest);

    /**
     * The feedback message
     *
     * @param boolean $resultTest The result of unlock
     *
     * @return string
     */
    abstract public function getMessageUnlock($resultTest);

    /**
     * The response of lock
     *
     * @return boolean
     */
    final public function lock()
    {
        if (!$this->isExists()) {
            $this->eventDispatcher->dispatch(PreLockEvent::NAME, new PreLockEvent());
            $success = $this->createLock();
            $this->eventDispatcher->dispatch(PostLockEvent::NAME, new PostLockEvent($success));

            return $success;
        } else {
            return false;
        }
    }

    /**
     * The response of unlock
     *
     * @return boolean
     */
    final public function unlock()
    {
        if ($this->isExists()) {
            $this->eventDispatcher->dispatch(PreUnlockEvent::NAME, new PreUnlockEvent());
            $success = $this->createUnlock();
            $this->eventDispatcher->dispatch(PostUnlockEvent::NAME, new PostUnlockEvent($success));

            return $success;
        } else {
            return false;
        }
    }

    /**
     * the choice of the driver to less pass or not the user
     *
     * @return boolean
     */
    public function decide()
    {
        return ($this->isExists());
    }

    /**
     * Options of driver
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set translatorlator
     *
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @return AbstractDriver
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;

        return $this;
    }
}
