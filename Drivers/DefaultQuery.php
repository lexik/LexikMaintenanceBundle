<?php

namespace Lexik\Bundle\MaintenanceBundle\Drivers;

use Doctrine\ORM\EntityManager;

/**
 * Default Class for handle database with a doctrine connection
 *
 * @package LexikMaintenanceBundle
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class DefaultQuery extends PdoDriver
{
    /**
     * @var EntityManager
     */
    protected $em;

    const NAME_TABLE   = 'lexik_maintenance';

    /**
     * @param EntityManager $em Entity Manager
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * (non-PHPdoc)
     * @see Lexik\Bundle\MaintenanceBundle\Drivers.PdoDriver::initDb()
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
     * (non-PHPdoc)
     * @see Lexik\Bundle\MaintenanceBundle\Drivers.DatabaseQueryInterface::createTableQuery()
     */
    public function createTableQuery()
    {
        $this->db->exec(
            sprintf('CREATE TABLE IF NOT EXISTS %s (ttl datetime DEFAULT NULL)', self::NAME_TABLE)
        );
    }

    /**
     * (non-PHPdoc)
     * @see Lexik\Bundle\MaintenanceBundle\Drivers.DatabaseQueryInterface::deleteQuery()
     */
    public function deleteQuery($db)
    {
        return $this->exec($db, sprintf('DELETE FROM %s', self::NAME_TABLE));
    }

    /**
     * (non-PHPdoc)
     * @see Lexik\Bundle\MaintenanceBundle\Drivers.DatabaseQueryInterface::selectQuery()
     */
    public function selectQuery($db)
    {
        return $this->fetch($db, sprintf('SELECT ttl FROM %s', self::NAME_TABLE));
    }

    /**
     * (non-PHPdoc)
     * @see Lexik\Bundle\MaintenanceBundle\Drivers.DatabaseQueryInterface::insertQuery()
     */
    public function insertQuery($ttl, $db)
    {
        return $this->exec(
            $db, sprintf('INSERT INTO %s (ttl) VALUES (:ttl)',
            self::NAME_TABLE),
            array(':ttl' => $ttl)
        );
    }
}
