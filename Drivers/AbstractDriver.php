<?php

namespace Lexik\Bundle\MaintenanceBundle\Drivers;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;

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
     * @var Translator
     */
    protected $trans;

    /**
     * Constructor
     *
     * @param Translator $trans   Translator service
     * @param array      $options Array of options
     */
    public function __construct(Translator $trans, array $options = array())
    {
        $this->options = $options;
        $this->trans   = $trans;
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
     * Set time to life for overwrite your basic configuration
     *
     * @param integer $value ttl value
     */
    public function setTtl($value)
    {
        $this->options['ttl'] = $value;
    }

    /**
     * Return time to life
     *
     * @return integer
     */
    public function getTtl()
    {
        return $this->options['ttl'];
    }

    /**
     * Set translator
     *
     * @param Translator $trans Translator service
     */
    public function setTranslator(Translator $trans)
    {
        $this->trans = $trans;
    }
}
