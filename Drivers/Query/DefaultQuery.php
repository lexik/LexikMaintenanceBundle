<?php

namespace Lexik\Bundle\MaintenanceBundle\Drivers\Query;

use Doctrine\ORM\EntityManager;

/**
 * Default Class for handle database with a doctrine connection
 *
 * @package LexikMaintenanceBundle
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class DefaultQuery extends PdoQuery
{
    /**
     * @var EntityManager
     */
    protected $em;

    const NAME_TABLE   = 'lexik_maintenance';

    /**
     * @param EntityManager $em      Entity Manager
     * @param array         $options Options driver
     */
    public function __construct(EntityManager $em, array $options = array())
    {
        $this->em = $em;
        parent::__construct($options);
    }

    /**
     * {@inheritdoc}
     */
    public function initDb()
    {
        if (null === $this->db) {
            $db = $this->em->getConnection();
            $this->db = $db;
            if (!isset($this->options['table_created']) || !$this->options['table_created']) {
                $this->createTableQuery();
            }
        }

        return $this->db;
    }

    /**
     * {@inheritdoc}
     */
    public function createTableQuery()
    {
        $type = $this->em->getConnection()->getDatabasePlatform()->getName() != 'mysql' ? 'timestamp' : 'datetime';

        $this->db->exec(
            sprintf('CREATE TABLE IF NOT EXISTS %s (ttl %s DEFAULT NULL)', self::NAME_TABLE, $type)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function deleteQuery($db)
    {
        return $this->exec($db, sprintf('DELETE FROM %s', self::NAME_TABLE));
    }

    /**
     * {@inheritdoc}
     */
    public function selectQuery($db)
    {
        return $this->fetch($db, sprintf('SELECT ttl FROM %s', self::NAME_TABLE));
    }

    /**
     * {@inheritdoc}
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
