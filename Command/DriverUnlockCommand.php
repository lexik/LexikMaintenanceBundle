<?php

namespace Lexik\Bundle\MaintenanceBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

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
            ->setDescription('Unlock access to the site while maintenance...')
            ->setHelp(<<<EOT
    You can execute the unlock without a warning message wich you need to interact with:

    <info>%command.full_name% --no-interaction</info>
EOT
                );
    }

    /**
     * (non-PHPdoc)
     * @see Symfony\Component\Console\Command.Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->unlock($input, $output);

        $driver = $this->getContainer()->get('lexik_maintenance.driver.factory')->getDriver();

        $unlockMessage = $driver->getMessageUnlock($driver->unlock());

        $output->writeln('<info>'.$unlockMessage.'</info>');
    }

    /**
    * (non-PHPdoc)
    */
    protected function unlock(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelperSet()->get('dialog');
        $formatter = $this->getHelperSet()->get('formatter');

        if ($input->getOption('no-interaction', false)) {
            $confirmation = true;
        }
        else {
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
        }

        if ($confirmation) {
            return;
        } else {
            $output->writeln('<error>Action cancelled!</error>');
            exit;
        }
    }
}

