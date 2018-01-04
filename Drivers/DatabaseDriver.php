<?php

namespace Lexik\Bundle\MaintenanceBundle\Drivers;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Lexik\Bundle\MaintenanceBundle\Drivers\Query\DefaultQuery;
use Lexik\Bundle\MaintenanceBundle\Drivers\Query\DsnQuery;
use Lexik\Bundle\MaintenanceBundle\Drivers\Query\PdoQuery;
use Lexik\Bundle\MaintenanceBundle\Drivers\Query\QueryStartdateInterface;

/**
 * Class driver for handle database
 *
 * @package LexikMaintenanceBundle
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class DatabaseDriver extends AbstractDriver implements DriverTtlInterface, DriverStartdateInterface
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
     * @var PdoQuery
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
            $this->pdoDriver->deleteStartdateQuery($db);
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
    public function scheduleLock()
    {
        $db = $this->pdoDriver->initDb();
        $status = false;
        
        if ($this->pdoDriver instanceof QueryStartdateInterface && !$this->isExists())
        {
            /* @var $this->pdoDriver QueryStartdateInterface */
            $startDate = null;
            if (isset($this->options['startdate']) && $this->options['startdate'] instanceof \DateTime) {
                $startDate = $this->options['startdate']->format('Y-m-d H:i:s');
                $ttl = null;
                if (isset($this->options['ttl']) && $this->options['ttl'] !== 0) {
                    $ttl = $this->options['ttl'];
                }
                try {
                    $data = $this->pdoDriver->selectStartdateQuery($db);
                    if (!empty($data)) {
                        // overwrite existing schedule
                        $this->pdoDriver->deleteStartdateQuery($db);
                    }

                    $status = $this->pdoDriver->insertStartdateQuery($ttl, $startDate, $db);
                } catch (\Exception $e) {
                    $status = false;
                }
            }
        }

        return $status;
    }

    /**
     * {@inheritdoc}
     */
    public function unscheduleLock()
    {
        $db = $this->pdoDriver->initDb();
        /* @var $this->pdoDriver QueryStartdateInterface */

        $data = $this->pdoDriver->selectStartdateQuery($db);
        if (empty($data)) {
            // overwrite existing schedule
            return false;
        }

        return $this->pdoDriver->deleteStartdateQuery($db);
    }

    /**
     * {@inheritdoc}
     */
    public function isExists()
    {
        $db = $this->pdoDriver->initDb();
        $data = $this->pdoDriver->selectQuery($db);

        if (empty($data)) {
            return false;
        }

        $ttlData = array_key_exists('ttl', $data[0]) === true ? $data[0]['ttl'] :
            (array_key_exists('TTL', $data[0]) === true ? $data[0]['TTL'] : null);
        if (null !== $ttlData) {
            $now = new \DateTime('now');
            $ttl = new \DateTime($ttlData);

            if ($ttl < $now) {
                return $this->createUnlock();
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isExistsSchedule()
    {
        $db = $this->pdoDriver->initDb();
        $data = $this->pdoDriver->selectStartdateQuery($db);

        if (empty($data)) {
            return false;
        }

        $ttlData = array_key_exists('ttl', $data[0]) === true ? $data[0]['ttl'] :
                (array_key_exists('TTL', $data[0]) === true ? $data[0]['TTL'] : null);
        $startDateData = array_key_exists('startdate', $data[0]) === true ? $data[0]['startdate'] :
                (array_key_exists('STARTDATE', $data[0]) === true ? $data[0]['STARTDATE'] : null);

        if ($ttlData === null || $startDateData === null) {
            return false;
        }

        //quick fix: set data
        $this->options['startdate'] = $startDateData;
        $this->options['ttl'] = $ttlData;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function lockWhenScheduled()
    {
        $status = false;

        if ($this->pdoDriver instanceof QueryStartdateInterface && !$this->isExists()) {
            /* @var $this ->pdoDriver QueryStartdateInterface */
            $db = $this->pdoDriver->initDb();
            $data = $this->pdoDriver->selectStartdateQuery($db);

            if (empty($data)) {
                $status = false;
            } else {
                $ttlData = array_key_exists('ttl', $data[0]) === true ? $data[0]['ttl'] :
                        (array_key_exists('TTL', $data[0]) === true ? $data[0]['TTL'] : null);
                $startDateData = array_key_exists('startdate', $data[0]) === true ? $data[0]['startdate'] :
                        (array_key_exists('STARTDATE', $data[0]) === true ? $data[0]['STARTDATE'] : null);

                if (null !== $startDateData) {
                    $now = new \DateTime('now');
                    $ttl = $ttlData ? $ttlData : 0;
                    $startDate = new \DateTime($startDateData);

                    if ($startDate < $now) {
                        $this->options['ttl'] = $ttl;
                        $this->lock();

                        $status = true;
                    }
                }
            }
        }

        return $status;
    }

    /**
     * {@inheritdoc}
     */
    public function isDelayed()
    {
        return (isset($this->options['startdate']) && $this->options['startdate'] instanceof \DateTime);
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
    public function getMessageScheduleLock($resultTest)
    {
        $key = $resultTest ? 'lexik_maintenance.success_schedule_database' : 'lexik_maintenance.not_success_schedule';

        return $this->translator->trans($key, array(), 'maintenance');
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageUnscheduleLock($resultTest)
    {
        $key = $resultTest ? 'lexik_maintenance.success_unschedule_database' : 'lexik_maintenance.not_success_unschedule';

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
    public function setStartDate($value)
    {
        $this->options['startdate'] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getStartDate()
    {
        return new \DateTime($this->options['startdate']);
    }

}
