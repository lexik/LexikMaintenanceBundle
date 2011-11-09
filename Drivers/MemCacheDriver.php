<?php

namespace Lexik\Bundle\MaintenanceBundle\Drivers;

/**
 * Class to handle a memcache driver
 *
 * @package LexikMaintenanceBundle
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class MemCacheDriver extends AbstractDriver
{
    /**
     * Value store in memcache
     *
     * @var string
     */
    const VALUE_TO_STORE = "maintenance";

    /**
     * The key store in memcache
     *
     * @var string keyName
     */
    protected $keyName;

    /**
     * MemCache instance
     *
     * @var \Memcache
     */
    protected $memcacheInstance;

    /**
     * Constructor memCacheDriver
     *
     * @param Translator $translator Translator service
     * @param array      $options    Options driver
     */
    public function __construct($translator, array $options = array())
    {
        parent::__construct($translator, $options);

        if ( ! isset($options['key_name'])) {
            throw new \InvalidArgumentException('$options[\'key_name\'] must be defined if Driver Memcache configuration is used');
        }

        if ( ! isset($options['host'])) {
            throw new \InvalidArgumentException('$options[\'host\'] must be defined if Driver Memcache configuration is used');
        }

        if ( ! isset($options['port'])) {
            throw new \InvalidArgumentException('$options[\'port\'] must be defined if Driver Memcache configuration is used');
        } elseif (! is_int($options['port'])) {
            throw new \InvalidArgumentException('$options[\'port\'] must be an integer if Driver Memcache configuration is used');
        }

        if (null !== $options) {
            $this->keyName = $options['key_name'];
            $this->memcacheInstance = new \Memcache;
            $this->memcacheInstance->connect($options['host'], $options['port']);
        }

        $this->options = $options;
    }

    /**
    * (non-PHPdoc)
    * @see Lexik\Bundle\MaintenanceBundle\Drivers.AbstractDriver::createLock()
    */
    protected function createLock()
    {
        return $this->memcacheInstance->set($this->keyName, self::VALUE_TO_STORE, false, (isset($this->options['ttl']) ? $this->options['ttl'] : 0));
    }

    /**
     * (non-PHPdoc)
     * @see Lexik\Bundle\MaintenanceBundle\Drivers.AbstractDriver::createUnlock()
     */
    protected function createUnlock()
    {
        return $this->memcacheInstance->delete($this->keyName);
    }

    /**
     * (non-PHPdoc)
     * @see Lexik\Bundle\MaintenanceBundle\Drivers.AbstractDriver::isExists()
     */
    public function isExists()
    {
        if (false !== $this->memcacheInstance->get($this->keyName)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * (non-PHPdoc)
     * @see Lexik\Bundle\MaintenanceBundle\Drivers.AbstractDriver::getMessageLock()
     */
    public function getMessageLock($resultTest)
    {
        $message = '';
        if ($resultTest) {
            $message = $this->trans->trans('lexik_maintenance.success_lock_memc', array(), 'maintenance');
        } else {
            $message = $this->trans->trans('lexik_maintenance.not_success_lock', array(), 'maintenance');
        }

        return $message;
    }

    /**
     * (non-PHPdoc)
     * @see Lexik\Bundle\MaintenanceBundle\Drivers.AbstractDriver::getMessageUnlock()
     */
    public function getMessageUnlock($resultTest)
    {
        $message = '';
        if ($resultTest) {
            $message = $this->trans->trans('lexik_maintenance.success_unlock', array(), 'maintenance');
        } else {
            $message = $this->trans->trans('lexik_maintenance.not_success_unlock', array(), 'maintenance');
        }

        return $message;
    }
}
