<?php

namespace Lexik\Bundle\MaintenanceBundle\Drivers;

use Lexik\Bundle\MaintenanceBundle\Drivers\DatabaseDriver;
use Lexik\Bundle\MaintenanceBundle\Drivers\DatabaseDriver\DatabaseDriverInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

/**
 * Factory for create driver
 *
 * @package LexikMaintenanceBundle
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class DriverFactory
{
    protected $driver;
    protected $dbDriver;
    protected $trans;

    const DATABASE_DRIVER = "Lexik\Bundle\MaintenanceBundle\Drivers\DatabaseDriver";

    /**
     * Constructor driver factory
     *
     * @param DatabaseDriver $dbDriver      The databaseDriver Service
     * @param Translator     $trans         The translator service
     * @param array          $driverOptions Options driver
     */
    public function __construct(DatabaseDriver $dbDriver, Translator $trans, array $driverOptions)
    {
        $this->driver = $driverOptions;

        if ( ! isset($this->driver['class'])) {
            throw new \ErrorException('You need to define a driver class');
        }

        $this->dbDriver = $dbDriver;
        $this->trans    = $trans;
    }

    /**
     * Return the driver
     *
     * @return mixte
     * @throws \ErrorException
     */
    public function getDriver()
    {
        $class = $this->driver['class'];
        if (class_exists($class)) {
            if ($class === self::DATABASE_DRIVER) {
                $driver = $this->dbDriver;
                $driver->setOptions($this->driver['options']);
                $driver->setTranslator($this->trans);
                return $driver;
            }
            return new $class($this->trans, $this->driver['options']);
        } else {
            throw new \ErrorException("Class '".$class."' not found in ".get_class($this));
        }
    }
}
