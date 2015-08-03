<?php

namespace Lexik\Bundle\MaintenanceBundle\Drivers;

/**
 * Class to handle a shared memory driver
 *
 * @package LexikMaintenanceBundle
 * @author  Audrius Karabanovas <audrius@karabanovas.net>
 */
class ShmDriver extends AbstractDriver
{
    /**
     * Value store in shm
     *
     * @var string
     */
    const VALUE_TO_STORE = "maintenance";

    /**
     * Variable key
     *
     * @var integer
     */
    const VARIABLE_KEY = 1;

    /**
     * The key store in shm
     *
     * @var string keyName
     */
    protected $keyName;


    /**
     * Shared memory block ID
     *
     * @var resource
     */
    protected $shmId;

    /**
     * Constructor shmDriver
     *
     * @param Translator $translator Translator service
     * @param array      $options    Options driver
     */
    public function __construct($translator, array $options = array())
    {
        parent::__construct($translator, $options);

        $key = ftok(__FILE__, 'm');
        $this->shmId = shm_attach($key, 100, 0666);
        if (!$this->shmId) {
            throw new \RuntimeException('Can\'t allocate shared memory');
        }
        $this->options = $options;
    }

    /**
     * Detach from shared memory
     */
    public function __destruct()
    {
        if ($this->shmId) {
            shm_detach($this->shmId);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function createLock()
    {
        if ($this->shmId) {
            return shm_put_var($this->shmId, self::VARIABLE_KEY, self::VALUE_TO_STORE);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function createUnlock()
    {
        if ($this->shmId) {
            return shm_remove_var($this->shmId, self::VARIABLE_KEY);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isExists()
    {
        if ($this->shmId) {
            if (!shm_has_var($this->shmId, self::VARIABLE_KEY) ) {
                return false;
            }
            $data = shm_get_var($this->shmId, self::VARIABLE_KEY);
            return ($data == self::VALUE_TO_STORE);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageLock($resultTest)
    {
        $key = $resultTest ? 'lexik_maintenance.success_lock_shm' : 'lexik_maintenance.not_success_lock';

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
}
