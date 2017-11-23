<?php

namespace Lexik\Bundle\MaintenanceBundle\Drivers;

/**
 * Class to handle a redis driver
 *
 * @package LexikMaintenanceBundle
 * @author  Burak Bolat <brkblt@gmail.com>
 */
class RedisDriver extends AbstractDriver implements DriverTtlInterface
{
    /**
     * Value store in redis
     *
     * @var string
     */
    const VALUE_TO_STORE = "maintenance";

    /**
     * The key store in redis
     *
     * @var string keyName
     */
    protected $keyName;

    /**
     * Redis instance
     *
     * @var \Redis
     */
    protected $redisInstance;

    /**
     * Constructor RedisDriver
     *
     * @param array $options    Options driver
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);

        if ( ! isset($options['key_name'])) {
            throw new \InvalidArgumentException('$options[\'key_name\'] must be defined if Driver Redis configuration is used');
        }

        if ( ! isset($options['host'])) {
            throw new \InvalidArgumentException('$options[\'host\'] must be defined if Driver Redis configuration is used');
        }

        if ( ! isset($options['port'])) {
            throw new \InvalidArgumentException('$options[\'port\'] must be defined if Driver Redis configuration is used');
        } elseif (! is_int($options['port'])) {
            throw new \InvalidArgumentException('$options[\'port\'] must be an integer if Driver Redis configuration is used');
        }

        if (null !== $options) {
            $this->keyName = $options['key_name'];
            $this->redisInstance = new \Predis\Client([
                'host' => $options['host'],
                'port' => $options['port'],
            ]);
        }

        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    protected function createLock()
    {
        return $this->redisInstance->setex($this->keyName, isset($this->options['ttl']) ? $this->options['ttl'] : 0, self::VALUE_TO_STORE);
    }

    /**
     * {@inheritdoc}
     */
    protected function createUnlock()
    {
        return $this->redisInstance->del($this->keyName) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function isExists()
    {
        return $this->redisInstance->exists($this->keyName) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageLock($resultTest)
    {
        $key = $resultTest ? 'lexik_maintenance.success_lock_redis' : 'lexik_maintenance.not_success_lock';

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
