<?php

namespace Lexik\Bundle\MaintenanceBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 *
 * @package LexikMaintenanceBundle
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class LexikMaintenanceExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('database.xml');

        if (isset($config['driver']['ttl'])) {
            $config['driver']['options']['ttl'] = $config['driver']['ttl'];
        }

        $container->setParameter('lexik_maintenance.driver', $config['driver']);

        $container->setParameter('lexik_maintenance.authorized.path', $config['authorized']['path']);
        $container->setParameter('lexik_maintenance.authorized.host', $config['authorized']['host']);
        $container->setParameter('lexik_maintenance.authorized.ips', $config['authorized']['ips']);
        $container->setParameter('lexik_maintenance.authorized.query', $config['authorized']['query']);
        $container->setParameter('lexik_maintenance.authorized.cookie', $config['authorized']['cookie']);
        $container->setParameter('lexik_maintenance.authorized.route', $config['authorized']['route']);
        $container->setParameter('lexik_maintenance.authorized.attributes', $config['authorized']['attributes']);
        $container->setParameter('lexik_maintenance.response.http_code', $config['response']['code']);
        $container->setParameter('lexik_maintenance.response.http_status', $config['response']['status']);

        if (isset($config['driver']['options']['dsn'])) {
            $this->registerDsnconfiguration($config['driver']['options']);
        }
    }

    /**
     * Load dsn configuration
     *
     * @param array $options A configuration array
     *
     * @throws InvalidArgumentException
     */
    protected function registerDsnConfiguration($options)
    {
        if ( ! isset($options['table'])) {
            throw new InvalidArgumentException('You need to define table for dsn use');
        }

        if ( ! isset($options['user'])) {
            throw new InvalidArgumentException('You need to define user for dsn use');
        }

        if ( ! isset($options['password'])) {
            throw new InvalidArgumentException('You need to define password for dsn use');
        }
    }
}
