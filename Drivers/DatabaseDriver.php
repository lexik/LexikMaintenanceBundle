<?php

namespace Lexik\Bundle\MaintenanceBundle\Drivers;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Lexik\Bundle\MaintenanceBundle\Drivers\Query\DefaultQuery;
use Lexik\Bundle\MaintenanceBundle\Drivers\Query\DsnQuery;

/**
 * Class driver for handle database
 *
 * @package LexikMaintenanceBundle
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class DatabaseDriver extends AbstractDriver implements DriverTtlInterface
{
    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var string
     */
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getMessageLock($resultTest)
    {
        $key = $resultTest ? 'lexik_maintenance.success_lock_database' : 'lexik_maintenance.not_success_lock';

        return $this->translator->trans($key, array(), 'maintenance');
    }

    /**
     * {@inheritDoc}
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
