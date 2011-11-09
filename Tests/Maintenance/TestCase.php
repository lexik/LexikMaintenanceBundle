<?php

namespace Lexik\Bundle\MaintenanceBundle\Tests\Maintenance;

/**
 *
 *
 * @package LexikMaintenanceBundle
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    static protected $files;

    static public function setUpBeforeClass()
    {
        $tmpDir = sys_get_temp_dir().'/symfony2_finder';
        self::$files = array(
            $tmpDir.'/lock.lock',
        );

        if (is_dir($tmpDir)) {
            self::tearDownAfterClass();
        } else {
            mkdir($tmpDir);
        }

        foreach (self::$files as $file) {
            if ('/' === $file[strlen($file) - 1]) {
                mkdir($file);
            } else {
                @touch($file);
            }
        }
    }

    static public function tearDownAfterClass()
    {
        foreach (array_reverse(self::$files) as $file) {
            if ('/' === $file[strlen($file) - 1]) {
                @rmdir($file);
            } else {
                @unlink($file);
            }
        }
    }
}
