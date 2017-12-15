<?php

namespace Lexik\Bundle\MaintenanceBundle\Command;

use Lexik\Bundle\MaintenanceBundle\Drivers\DriverStartdateInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Create an unlock action
 *
 * @package LexikMaintenanceBundle
 * @author  Wolfram Eberius <edrush@posteo.de>
 */
class DriverUnscheduleLockCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('lexik:maintenance:unschedule-lock')
            ->setDescription('Remove schedule maintenance mode for your site......')
            ->setHelp(<<<EOT
    You can remove a scheduled maintenance: <info>%command.full_name% --no-interaction</info>
EOT
                );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->confirmUnschedule($input, $output)) {
            return;
        }

        $driver = $this->getContainer()->get('lexik_maintenance.driver.factory')->getDriver();

        if ($driver instanceof DriverStartdateInterface) {
            $message = $driver->getMessageUnscheduleLock($driver->unscheduleLock());
        } else {
            $message = array(
                '',
                sprintf('<fg=red>Delay / start date doesn\'t work with %s driver</>', get_class($driver)),
                '',
            );
        }

        $output->writeln('<info>'.$message.'</info>');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     */
    protected function confirmUnschedule(InputInterface $input, OutputInterface $output)
    {
        $formatter = $this->getHelperSet()->get('formatter');

        if ($input->getOption('no-interaction', false)) {
            $confirmation = true;
        } else {
            // confirm
            $output->writeln(array(
                '',
                $formatter->formatBlock('You are about to unschedule your maintenance mode.', 'bg=green;fg=white', true),
                '',
            ));

            $confirmation = $this->askConfirmation(
                'WARNING! Are you sure you wish to continue? (y/n) ',
                $input,
                $output
            );
        }

        if (!$confirmation) {
            $output->writeln('<error>Action cancelled!</error>');
        }

        return $confirmation;
    }

    /**
     * This method ensure that we stay compatible with symfony console 2.3 by using the deprecated dialog helper
     * but use the ConfirmationQuestion when available.
     *
     * @param $question
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    protected function askConfirmation($question, InputInterface $input, OutputInterface $output) {
        if (!$this->getHelperSet()->has('question')) {
            return $this->getHelper('dialog')
                ->askConfirmation($output, '<question>' . $question . '</question>', 'y');
        }

        return $this->getHelper('question')
            ->ask($input, $output, new \Symfony\Component\Console\Question\ConfirmationQuestion($question));
    }
}
