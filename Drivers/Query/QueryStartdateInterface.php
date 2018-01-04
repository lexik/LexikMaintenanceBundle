<?php

namespace Lexik\Bundle\MaintenanceBundle\Drivers\Query;

use Doctrine\ORM\EntityManager;

/**
 * Interface for PdoQuery classes that support start date.
 *
 * @package LexikMaintenanceBundle
 * @author  Wolfram Eberius <edrush@posteo.de>
 */
interface QueryStartdateInterface
{
    /**
     * Result of delete query
     *
     * @param \PDO $db PDO instance
     *
     * @return boolean
     */
    public function deleteStartdateQuery($db);

    /**
     * Result of select query
     *
     * @param \PDO $db PDO instance
     *
     * @return array
     */
    public function selectStartdateQuery($db);

    /**
     * Result of insert query
     *
     * @param integer $ttl ttl value
     * @param \DateTime $startDate start date value
     * @param \PDO    $db  PDO instance
     *
     * @return boolean
     */
    public function insertStartdateQuery($ttl, $startDate, $db);
}

