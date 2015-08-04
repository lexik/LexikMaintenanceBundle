<?php

namespace Lexik\Bundle\MaintenanceBundle\Drivers;

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
    public function lock()
    {
        if (!$this->isExists()) {
            return $this->createLock();
        } else {
            return false;
        }
    }

    /**
     * The response of unlock
     *
     * @return boolean
     */
    public function unlock()
    {
        if ($this->isExists()) {
            return $this->createUnlock();
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
}
