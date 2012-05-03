<?php

namespace Lexik\Bundle\MaintenanceBundle\Drivers;

use Doctrine\ORM\EntityManager;

/**
 * Class for handle database with a dsn connection
 *
 * @package LexikMaintenanceBundle
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class DsnQuery extends PdoDriver
{
    /**
     * (non-PHPdoc)
     * @see Lexik\Bundle\MaintenanceBundle\Drivers.DatabaseQueryInterface::initDb()
     */
    public function initDb()
    {
        if (null === $this->db) {

            if (!class_exists('PDO') || !in_array('mysql', \PDO::getAvailableDrivers(), true)) {
                throw new \RuntimeException('You need to enable PDO_Mysql extension for the profiler to run properly.');
            }

            $db = new \PDO($this->options['dsn'], $this->options['user'], $this->options['password']);
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
        $this->db->exec(sprintf('CREATE TABLE IF NOT EXISTS %s (ttl datetime DEFAULT NULL)', $this->options['table']));
    }

    /**
     * (non-PHPdoc)
     * @see Lexik\Bundle\MaintenanceBundle\Drivers.DatabaseQueryInterface::deleteQuery()
     */
    public function deleteQuery($db)
    {
        return $this->exec($db, sprintf('DELETE FROM %s', $this->options['table']));
    }

    /**
     * (non-PHPdoc)
     * @see Lexik\Bundle\MaintenanceBundle\Drivers.DatabaseQueryInterface::selectQuery()
     */
    public function selectQuery($db)
    {
        return $this->fetch($db, sprintf('SELECT ttl FROM %s', $this->options['table']));
    }

    /**
     * (non-PHPdoc)
     * @see Lexik\Bundle\MaintenanceBundle\Drivers.DatabaseQueryInterface::insertQuery()
     */
    public function insertQuery($ttl, $db)
    {
        return $this->exec(
            $db,
            sprintf('INSERT INTO %s (ttl) VALUES (:ttl)',
            $this->options['table']),
            array(':ttl' => $ttl)
        );
    }
}
