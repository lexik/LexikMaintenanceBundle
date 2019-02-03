<?php

namespace Lexik\Bundle\MaintenanceBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Translation\Formatter\MessageFormatter;
use Symfony\Component\Translation\IdentityTranslator;

class TestHelper
{
    public static function getTranslator(ContainerBuilder $container, IdentityTranslator $identityTranslator)
    {
        return new Translator(
            $container,
            new MessageFormatter($identityTranslator),
            'en'
        );
    }
}
