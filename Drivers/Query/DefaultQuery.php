<?php

namespace Lexik\Bundle\MaintenanceBundle\Drivers\Query;

use Doctrine\ORM\EntityManager;

/**
 * Default Class for handle database with a doctrine connection
 *
 * @package LexikMaintenanceBundle
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class DefaultQuery extends PdoQuery implements QueryStartdateInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    const NAME_TABLE_LOCK   = 'lexik_maintenance';
    const NAME_TABLE_STARTDATE   = 'lexik_startdate';

    /**
     * @param EntityManager $em Entity Manager
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function initDb()
    {
        if (null === $this->db) {
            $db = $this->em->getConnection();
            $this->db = $db;
            $this->createTableQuery();
        }

        return $this->db;
    }

    /**
     * {@inheritdoc}
     */
    public function createTableQuery()
    {
        $type = $this->em->getConnection()->getDatabasePlatform()->getName() != 'mysql' ? 'timestamp' : 'datetime';

        if ($this->em->getConnection()->getDatabasePlatform()->getName() === 'oracle') {
            $lockTableFound = false;
            $startDateTableFound = false;
            foreach ($this->fetch($this->db, $this->em->getConnection()->getDatabasePlatform()->getListTablesSQL()) as $tableInfo) {
                if ($tableInfo['TABLE_NAME'] === strtoupper(self::NAME_TABLE_LOCK)) {
                    $lockTableFound = true;
                } elseif ($tableInfo['TABLE_NAME'] === strtoupper(self::NAME_TABLE_STARTDATE)) {
                    $startDateTableFound = true;
                }
            }

            if ($lockTableFound === false) {
                $this->db->exec(
                    sprintf('CREATE TABLE %s (ttl %s DEFAULT NULL)', strtoupper(self::NAME_TABLE_LOCK), $type)
                );
            }
            if ($startDateTableFound === false) {
                $this->db->exec(
                    sprintf('CREATE TABLE %s (ttl %s DEFAULT NULL, startdate %s DEFAULT NULL)', strtoupper(self::NAME_TABLE_STARTDATE), 'INT', $type)
                );
            }
        } else {
            $this->db->exec(
                sprintf('CREATE TABLE IF NOT EXISTS %s (ttl %s DEFAULT NULL)', self::NAME_TABLE_LOCK, $type)
            );
            $this->db->exec(
                sprintf('CREATE TABLE IF NOT EXISTS %s (ttl %s DEFAULT NULL, startdate %s DEFAULT NULL)', self::NAME_TABLE_STARTDATE, 'INT', $type)
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteQuery($db)
    {
        return $this->exec($db, sprintf('DELETE FROM %s', self::NAME_TABLE_LOCK));
    }

    /**
     * {@inheritdoc}
     */
    public function selectQuery($db)
    {
        return $this->fetch($db, sprintf('SELECT ttl FROM %s', self::NAME_TABLE_LOCK));
    }

    /**
     * {@inheritdoc}
     */
    public function insertQuery($ttl, $db)
    {
        return $this->exec(
            $db, sprintf('INSERT INTO %s (ttl) VALUES (:ttl)',
            self::NAME_TABLE_LOCK),
            array(':ttl' => $ttl)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function deleteStartdateQuery($db)
    {
        return $this->exec($db, sprintf('DELETE FROM %s', self::NAME_TABLE_STARTDATE));
    }

    /**
     * {@inheritdoc}
     */
    public function selectStartdateQuery($db)
    {
        return $this->fetch($db, sprintf('SELECT ttl, startdate FROM %s', self::NAME_TABLE_STARTDATE));
    }

    /**
     * {@inheritdoc}
     */
    public function insertStartdateQuery($ttl, $startDate, $db)
    {
        return $this->exec(
            $db, sprintf('INSERT INTO %s (ttl, startdate) VALUES (:ttl, :startdate)',
            self::NAME_TABLE_STARTDATE),
            array(':ttl' => $ttl, ':startdate' => $startDate)
        );
    }
}
