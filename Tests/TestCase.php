<?php

namespace Lexik\Bundle\MaintenanceBundle\Tests;

require_once __DIR__.'/../../../../app/AppKernel.php';

/**
 * A PHPUnit testcase with some Symfony2 tools.
 *
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Symfony\Component\HttpKernel\AppKernel
     */
    protected $kernel;

    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var Symfony\Component\DependencyInjection\Container
     */
    protected $container;

    /**
     * Initialize kernel app and some Symfony2 services.
     *
     * @see \PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        // Boot the AppKernel in the test environment and with the debug.
        $this->kernel = new \AppKernel('test', true);
        $this->kernel->boot();

        // Store the container and the entity manager in test case properties
        $this->container = $this->kernel->getContainer();
        $this->entityManager = $this->container->get('doctrine')->getManager();

        $this->entityManager->getConnection()->beginTransaction();

        parent::setUp();
    }

    /**
     * @see \PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        $this->entityManager->getConnection()->rollback();

        // Shutdown the kernel.
        $this->kernel->shutdown();

        parent::tearDown();
    }
}
