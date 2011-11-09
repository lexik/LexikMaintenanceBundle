<?php

namespace Lexik\Bundle\MaintenanceBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Create an unlock action
 *
 * @package LexikMaintenanceBundle
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class DriverUnlockCommand extends ContainerAwareCommand
{
    /**
     * (non-PHPdoc)
     * @see Symfony\Component\Console\Command.Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('lexik:maintenance:unlock')
            ->setDescription('Unlock access to the site while maintenance...');
    }

    /**
     * (non-PHPdoc)
     * @see Symfony\Component\Console\Command.Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $driver = $this->getContainer()->get('lexik_maintenance.driver.factory')->getDriver();

        $unlockMessage = $driver->getMessageUnlock($driver->unlock());

        $output->writeln('<info>'.$unlockMessage.'</info>');
    }

    /**
    * (non-PHPdoc)
    * @see Symfony\Component\Console\Command.Command::interact()
    */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelperSet()->get('dialog');
        $formatter = $this->getHelperSet()->get('formatter');

        // confirme
        $output->writeln(array(
            '',
            $formatter->formatBlock('You are about to unlock your server.', 'bg=green;fg=white', true),
            '',
        ));

        $confirmation = $dialog->askConfirmation(
            $output,
            '<question>WARNING! Are you sure you wish to continue? (y/n) </question>',
            'y'
        );

        if ($confirmation) {
            return;
        } else {
            $output->writeln('<error>Action cancelled!</error>');
            exit;
        }
    }
}
