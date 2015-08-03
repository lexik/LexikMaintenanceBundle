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
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('lexik:maintenance:unlock')
            ->setDescription('Unlock access to the site while maintenance...')
            ->setHelp(<<<EOT
    You can execute the unlock without a warning message which you need to interact with:

    <info>%command.full_name% --no-interaction</info>
EOT
                );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->confirmUnlock($input, $output)) {
            return;
        }

        $driver = $this->getContainer()->get('lexik_maintenance.driver.factory')->getDriver();

        $unlockMessage = $driver->getMessageUnlock($driver->unlock());

        $output->writeln('<info>'.$unlockMessage.'</info>');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     */
    protected function confirmUnlock(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelperSet()->get('dialog');
        $formatter = $this->getHelperSet()->get('formatter');

        if ($input->getOption('no-interaction', false)) {
            $confirmation = true;
        } else {
            // confirm
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

        if (!$confirmation) {
            $output->writeln('<error>Action cancelled!</error>');
        }

        return $confirmation;
    }
}
