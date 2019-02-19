<?php

namespace Lexik\Bundle\MaintenanceBundle\Drivers;

/**
 * Class to handle a memcached driver
 *
 * @package LexikMaintenanceBundle
 */
class MemcachedDriver extends AbstractDriver implements DriverTtlInterface
{
    /**
     * Value store in memcache
     *
     * @var string
     */
    const VALUE_TO_STORE = 'maintenance';

    /**
     * The key store in memcache
     *
     * @var string keyName
     */
    protected $key;

    /**
     * Memcached instance
     *
     * @var \Memcached
     */
    protected $memcached;

    /**
     * @param array $options
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);

        if (isset($options['key']) === false) {
            throw new \InvalidArgumentException('Option "key" must be defined if driver Memcached configuration is used');
        }

        if (isset($options['servers']) === false || empty($options['servers']) === true) {
            throw new \InvalidArgumentException('Option "servers" must be defined if driver Memcached configuration is used');
        }

        $this->key = $options['key'];
        // TODO: A configured Memcached instance should be injected into the constructor for easier testing.
        $this->setMemcached(new \Memcached());
        foreach ($options['servers'] as $server) {
            if (isset($server['host']) === false) {
                throw new \InvalidArgumentException('Option "host" must be defined for each server if driver Memcached configuration is used');
            }

            if (isset($server['port']) === false || is_int($server['port']) === false) {
                throw new \InvalidArgumentException('Option "port" must be defined as an integer for each server if driver Memcached configuration is used');
            }

            $this->getMemcached()->addServer($server['host'], $server['port']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isExists()
    {
        if (false !== $this->getMemcached()->get($this->key)) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageLock($resultTest)
    {
        $key = $resultTest ? 'lexik_maintenance.success_lock_memc' : 'lexik_maintenance.not_success_lock';

        return $this->translator->trans($key, array(), 'maintenance');
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageUnlock($resultTest)
    {
        $key = $resultTest ? 'lexik_maintenance.success_unlock' : 'lexik_maintenance.not_success_unlock';

        return $this->translator->trans($key, array(), 'maintenance');
    }

    /**
     * {@inheritdoc}
     */
    public function setTtl($value)
    {
        $this->options['ttl'] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getTtl()
    {
        return $this->options['ttl'];
    }

    /**
     * {@inheritdoc}
     */
    public function hasTtl()
    {
        return isset($this->options['ttl']);
    }

    /**
     * {@inheritdoc}
     */
    protected function createLock()
    {
        return $this->getMemcached()->set($this->key, self::VALUE_TO_STORE, (isset($this->options['ttl']) ? $this->options['ttl'] : 0));
    }

    /**
     * {@inheritdoc}
     */
    protected function createUnlock()
    {
        return $this->getMemcached()->delete($this->key);
    }

    /**
     * @return \Memcached
     */
    private function getMemcached()
    {
        return $this->memcached;
    }

    /**
     * @param \Memcached $memcached
     * @return MemcachedDriver
     */
    private function setMemcached($memcached)
    {
        $this->memcached = $memcached;

        return $this;
    }
}
