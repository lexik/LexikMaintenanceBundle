<?php

namespace Lexik\Bundle\MaintenanceBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Translation\Formatter\MessageFormatter;
use Symfony\Component\Translation\MessageSelector;

class TestHelper
{
    public static function getTranslator(ContainerBuilder $container, MessageSelector $messageSelector)
    {
        if (Kernel::VERSION_ID < 30300) {
            // symfony 2
            $translator = new Translator(
                $container,
                $messageSelector
            );
        } elseif (Kernel::VERSION_ID >= 30300 && Kernel::VERSION_ID < 40000) {
            // symfony 3
            $translator = new Translator(
                $container,
                $messageSelector,
                'en'
            );
        } else {
            // symfony 4
            $translator = new Translator(
                $container,
                new MessageFormatter($messageSelector),
                'en'
            );
        }

        return $translator;
    }
}
