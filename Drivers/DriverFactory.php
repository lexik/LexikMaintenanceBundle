<?php

namespace Lexik\Bundle\MaintenanceBundle\Drivers;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Factory for create driver
 *
 * @package LexikMaintenanceBundle
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class DriverFactory
{
    /**
     * @var array
     */
    protected $driverOptions;

    /**
     * @var DatabaseDriver
     */
    protected $dbDriver;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    const DATABASE_DRIVER = 'Lexik\Bundle\MaintenanceBundle\Drivers\DatabaseDriver';

    /**
     * Constructor driver factory
     *
     * @param DatabaseDriver      $dbDriver The databaseDriver Service
     * @param TranslatorInterface $translator The translator service
     * @param array               $driverOptions Options driver
     * @throws \ErrorException
     */
    public function __construct(DatabaseDriver $dbDriver, TranslatorInterface $translator, array $driverOptions)
    {
        $this->driverOptions = $driverOptions;

        if ( ! isset($this->driverOptions['class'])) {
            throw new \ErrorException('You need to define a driver class');
        }

        $this->dbDriver = $dbDriver;
        $this->translator = $translator;
    }

    /**
     * Return the driver
     *
     * @return mixed
     * @throws \ErrorException
     */
    public function getDriver()
    {
        $class = $this->driverOptions['class'];

        if (!class_exists($class)) {
            throw new \ErrorException("Class '".$class."' not found in ".get_class($this));
        }

        if ($class === self::DATABASE_DRIVER) {
            $driver = $this->dbDriver;
            $driver->setOptions($this->driverOptions['options']);
        } else {
            $driver = new $class($this->driverOptions['options']);
        }

        $driver->setTranslator($this->translator);

        return $driver;
    }
}
