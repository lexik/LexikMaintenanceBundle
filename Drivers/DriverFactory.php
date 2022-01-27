<?php

namespace Lexik\Bundle\MaintenanceBundle\Drivers;

use Predis\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;


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

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    protected $serviceName;

    const DATABASE_DRIVER = 'Lexik\Bundle\MaintenanceBundle\Drivers\DatabaseDriver';

    /**
     * Constructor driver factory
     *
     * @param DatabaseDriver      $dbDriver The databaseDriver Service
     * @param TranslatorInterface $translator The translator service
     * @param array               $driverOptions Options driver
     * @throws \ErrorException
     */
    public function __construct(DatabaseDriver $dbDriver, TranslatorInterface $translator, ContainerInterface $container, array $driverOptions)
    {
        $this->driverOptions = $driverOptions;

        if ( ! isset($this->driverOptions['class'])) {
            throw new \ErrorException('You need to define a driver class');
        }

        $this->dbDriver = $dbDriver;
        $this->translator = $translator;
        $this->container = $container;
        $this->serviceName = $driverOptions['options']['service'] ?? null;
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
            $options = $this->driverOptions['options'];
            if (!empty($this->serviceName)) {
                $options = array_merge($options, ['service' => $this->container->get($this->serviceName)]);
            }
            $driver = new $class($options);
        }

        $driver->setTranslator($this->translator);

        return $driver;
    }
}
