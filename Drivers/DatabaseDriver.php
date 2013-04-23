<?php

namespace Lexik\Bundle\MaintenanceBundle\Drivers;

use Doctrine\Bundle\DoctrineBundle\Registry;

/**
 * Class driver for handle database
 *
 * @package LexikMaintenanceBundle
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class DatabaseDriver extends AbstractDriver
{
    protected $doctrine;
    protected $options;
    protected $db;

    /**
     *
     * @var PdoDriver
     */
    protected $pdoDriver;

    /**
     * Constructor
     *
     * @param Registry $doctrine The registry
     */
    public function __construct(Registry $doctrine = null)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * Set options from configuration
     *
     * @param array $options Options
     */
    public function setOptions($options)
    {
        $this->options = $options;

        if (isset($this->options['dsn'])) {
            $this->pdoDriver = new DsnQuery($this->options);
        } else {
            if (isset($this->options['connection'])) {
                $this->pdoDriver = new DefaultQuery($this->doctrine->getManager($this->options['connection']));
            } else {
                $this->pdoDriver = new DefaultQuery($this->doctrine->getManager());
            }
        }
    }

    /**
    * (non-PHPdoc)
    * @see Lexik\Bundle\MaintenanceBundle\Drivers.AbstractDriver::createLock()
    */
    protected function createLock()
    {
        $db = $this->pdoDriver->initDb();

        try {
            $ttl = null;
            if (isset($this->options['ttl']) && $this->options['ttl'] !== 0) {
                $now = new \Datetime('now');
                $ttl = $this->options['ttl'];
                $ttl = $now->modify(sprintf('+%s seconds', $ttl))->format('Y-m-d H:i:s');
            }
            $status = $this->pdoDriver->insertQuery($ttl, $db);
        } catch (\Exception $e) {
            $status = false;
        }

        return $status;
    }

    /**
     * (non-PHPdoc)
     * @see Lexik\Bundle\MaintenanceBundle\Drivers.AbstractDriver::createUnlock()
     */
    protected function createUnlock()
    {
        $db = $this->pdoDriver->initDb();

        try {
            $status = $this->pdoDriver->deleteQuery($db);
        } catch (\Exception $e) {
            $status = false;
        }

        return $status;
    }

    /**
     * (non-PHPdoc)
     * @see Lexik\Bundle\MaintenanceBundle\Drivers.AbstractDriver::isExists()
     */
    public function isExists()
    {
        $db = $this->pdoDriver->initDb();
        $data = $this->pdoDriver->selectQuery($db);

        if (!$data) {
            return null;
        }

        if (null !== $data[0]['ttl']) {
            $now = new \DateTime('now');
            $ttl = new \DateTime($data[0]['ttl']);

            if ($ttl < $now) {
                return $this->createUnlock();
            }
        }

        return true;
    }

    /**
     * (non-PHPdoc)
     * @see Lexik\Bundle\MaintenanceBundle\Drivers.AbstractDriver::getMessageLock()
     */
    public function getMessageLock($resultTest)
    {
        $message = '';
        if ($resultTest) {
            $message = $this->trans->trans('lexik_maintenance.success_lock_database', array(), 'maintenance');
        } else {
            $message = $this->trans->trans('lexik_maintenance.not_success_lock', array(), 'maintenance');
        }

        return $message;
    }

    /**
     * {@inheritDoc}
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
