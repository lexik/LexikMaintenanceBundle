<?php

namespace Lexik\Bundle\MaintenanceBundle\Drivers;

/**
 * Class RedisDriver
 *
 * @package Lexik\Bundle\MaintenanceBundle\Drivers
 */
class RedisDriver extends AbstractDriver implements DriverTtlInterface
{
    /**
     * The key to store in redis.
     *
     * @var string
     */
    protected $keyName;

    /**
     * @var \Predis\Client
     */
    protected $redisInstance;


    /**
     * RedisDriver constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);

        if (!isset($options['key_name'])) {
            throw new \InvalidArgumentException('$options[\'key_name\'] must be defined if Driver Redis configuration is used');
        }

        if (!isset($options['connection_parameters'])) {
            throw new \InvalidArgumentException('$options[\'connection_parameters\'] must be defined if Driver Redis configuration is used');
        }

        if (null !== $options) {
            if (!isset($options['ttl']) || !is_numeric($options['ttl'])) {
                $options['ttl'] = 0;
            } else {
                $options['ttl'] = intval($options['ttl']);
            }

            $this->keyName = $options['key_name'];

            $this->redisInstance = new \Predis\Client($options['connection_parameters']);
            $this->redisInstance->connect();
        }

        $this->options = $options;
    }

    function __destruct()
    {
        if (isset($this->redisInstance)) {
            $this->redisInstance->disconnect();
        }

        unset($this->redisInstance);
    }


    /**
     * {@inheritdoc}
     */
    protected function createLock()
    {
        return ($this->redisInstance->setex($this->keyName, $this->options['ttl'], true) === 'OK');
    }

    /**
     * {@inheritdoc}
     */
    protected function createUnlock()
    {
        return $this->redisInstance->del(array($this->keyName)) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function isExists()
    {
        return $this->redisInstance->exists($this->keyName);
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
}