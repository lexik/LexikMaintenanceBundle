<?php

namespace Lexik\Bundle\MaintenanceBundle\Drivers;

use Symfony\Component\Config\FileLocator;

class FileDriver extends AbstractDriver
{
    protected $filePath;

    /**
     * Constructor
     *
     * @param Translator $translator Translator service
     * @param array      $options    Options driver
     */
    public function __construct($translator, array $options = array())
    {
        parent::__construct($translator, $options);

        if ( ! isset($options['file_path'])) {
            throw new \InvalidArgumentException('$options[\'file_path\'] cannot be defined if Driver File configuration is used');
        }
        if (null !== $options) {
            $this->filePath = $options['file_path'];
        }

        $this->options = $options;
    }

    /**
     * (non-PHPdoc)
     * @see Lexik\Bundle\MaintenanceBundle\Drivers.AbstractDriver::createLock()
     */
    protected function createLock()
    {
        return (fopen($this->filePath, 'w+'));
    }

    /**
     * (non-PHPdoc)
     * @see Lexik\Bundle\MaintenanceBundle\Drivers.AbstractDriver::createUnlock()
     */
    protected function createUnlock()
    {
        return @unlink($this->filePath);
    }

    /**
     * (non-PHPdoc)
     * @see Lexik\Bundle\MaintenanceBundle\Drivers.AbstractDriver::isExists()
     */
    public function isExists()
    {
        if (file_exists($this->filePath)) {
            if (isset($this->options['ttl']) && is_numeric($this->options['ttl'])) {
                $this->isEndTime($this->options['ttl']);
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Test if time to life is expired
     *
     * @param integer $timeTtl The ttl value
     *
     * @return boolean
     */
    public function isEndTime($timeTtl)
    {
        $now = new \DateTime('now');
        $accessTime = date("Y-m-d H:i:s.", filemtime($this->filePath));
        $accessTime = new \DateTime($accessTime);
        $accessTime->modify(sprintf('+%s seconds', $timeTtl));

        if ($accessTime < $now) {
            return $this->createUnlock();
        } else {
            return true;
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
            $message = $this->trans->trans('lexik_maintenance.success_lock_file', array(), 'maintenance');
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
