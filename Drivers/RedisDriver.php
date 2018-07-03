<?php

namespace Lexik\Bundle\MaintenanceBundle\Drivers;
use Predis\Client as PredisClient;

/**
 * Class RedisDriver
 *
 * @package Lexik\Bundle\MaintenanceBundle\Drivers
 */
class RedisDriver extends AbstractDriver implements DriverTtlInterface
{
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
        if (empty($options['service'])) {
            throw new \InvalidArgumentException('Option "service" must be defined in the RedisDriver configuration');
        }
        if (! $options['service'] instanceof PredisClient) {
            throw new \InvalidArgumentException(sprintf('Service must be a Predis/Client. (%s given)', get_class($options['service'])));
        }

        if (empty($options['key_name'])) {
            throw new \InvalidArgumentException('Option "key_name" must be defined in the RedisDriver configuration');
        }

        if (!isset($options['ttl']) || !is_numeric($options['ttl'])) {
            $options['ttl'] = 0;
        } else {
            $options['ttl'] = intval($options['ttl']);
        }

        parent::__construct($options);
        $this->redisInstance = $options['service'];
        $this->redisInstance->connect();
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
        return ($this->redisInstance->setex($this->options['key_name'], $this->options['ttl'], true) === 'OK');
    }

    /**
     * {@inheritdoc}
     */
    protected function createUnlock()
    {
        return $this->redisInstance->del(array($this->options['key_name'])) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function isExists()
    {
        return $this->redisInstance->exists($this->options['key_name']);
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
