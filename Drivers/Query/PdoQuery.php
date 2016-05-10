<?php

namespace Lexik\Bundle\MaintenanceBundle\Drivers\Query;

/**
 * Abstract class to handle PDO connection.
 *
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
abstract class PdoQuery
{
    /**
     * @var \PDO
     */
    protected $db;

    /**
     * @var array
     */
    protected $options;

    /**
     * Constructor PdoDriver.
     *
     * @param array $options Options driver
     */
    public function __construct(array $options = array())
    {
        $this->options = $options;
    }

    /**
     * Execute create query.
     */
    abstract public function createTableQuery();

    /**
     * Result of delete query.
     *
     * @param \PDO $db PDO instance
     *
     * @return bool
     */
    abstract public function deleteQuery($db);

    /**
     * Result of select query.
     *
     * @param \PDO $db PDO instance
     *
     * @return array
     */
    abstract public function selectQuery($db);

    /**
     * Result of insert query.
     *
     * @param int  $ttl ttl value
     * @param \PDO $db  PDO instance
     *
     * @return bool
     */
    abstract public function insertQuery($ttl, $db);

    /**
     * Initialize pdo connection.
     *
     * @return \PDO
     */
    abstract public function initDb();

    /**
     * Execute sql.
     *
     * @param \PDO   $db    PDO instance
     * @param string $query Query
     * @param array  $args  Arguments
     *
     * @return bool
     *
     * @throws \RuntimeException
     */
    protected function exec($db, $query, array $args = array())
    {
        $stmt = $this->prepareStatement($db, $query);

        foreach ($args as $arg => $val) {
            $stmt->bindValue($arg, $val, is_int($val) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }

        $success = $stmt->execute();

        if (!$success) {
            throw new \RuntimeException(sprintf('Error executing query "%s"', $query));
        }

        return $success;
    }

    /**
     * PrepareStatement.
     *
     * @param \PDO   $db    PDO instance
     * @param string $query Query
     *
     * @return Statement
     *
     * @throws \RuntimeException
     */
    protected function prepareStatement($db, $query)
    {
        try {
            $stmt = $db->prepare($query);
        } catch (\Exception $e) {
            $stmt = false;
        }

        if (false === $stmt) {
            throw new \RuntimeException('The database cannot successfully prepare the statement');
        }

        return $stmt;
    }

    /**
     * Fetch All.
     *
     * @param \PDO   $db    PDO instance
     * @param string $query Query
     * @param array  $args  Arguments
     *
     * @return array
     */
    protected function fetch($db, $query, array $args = array())
    {
        $stmt = $this->prepareStatement($db, $query);

        if (false === $stmt) {
            throw new \RuntimeException('The database cannot successfully prepare the statement');
        }

        foreach ($args as $arg => $val) {
            $stmt->bindValue($arg, $val, is_int($val) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }

        $stmt->execute();
        $return = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $return;
    }
}
