<?php

namespace Lexik\Bundle\MaintenanceBundle\Command;

use Lexik\Bundle\MaintenanceBundle\Drivers\AbstractDriver;
use Lexik\Bundle\MaintenanceBundle\Drivers\DriverTtlInterface;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Create a lock action
 *
 * @package LexikMaintenanceBundle
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class DriverLockCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('lexik:maintenance:lock')
            ->setDescription('Lock access to the site while maintenance...')
            ->addArgument('ttl', InputArgument::OPTIONAL, 'Overwrite time to life from your configuration, doesn\'t work with file or shm driver. Time in seconds.', null)
            ->setHelp(<<<EOT

    You can optionally set a time to life of the maintenance

   <info>%command.full_name% 3600</info>

    You can execute the lock without a warning message which you need to interact with:

    <info>%command.full_name% --no-interaction</info>

    Or

    <info>%command.full_name% 3600 -n</info>
EOT
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $driver = $this->getDriver();
        $dialog = $this->getHelperSet()->get('dialog');

        if ($input->isInteractive()) {
            if (!$dialog->askConfirmation($output, '<question>WARNING! Are you sure you wish to continue? (y/n)</question>', 'y')) {
                $output->writeln('<error>Maintenance cancelled!</error>');
                exit;
            }
        }

        // set ttl from command line if given and driver supports it
        if ($driver instanceof DriverTtlInterface && null !== $input->getArgument('ttl')) {
            $driver->setTtl($input->getArgument('ttl'));
        }

        $output->writeln('<info>'.$driver->getMessageLock($driver->lock()).'</info>');
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $driver = $this->getDriver();
        $default = $driver->getOptions();

        $dialog = $this->getHelperSet()->get('dialog');
        $formatter = $this->getHelperSet()->get('formatter');

        if (null !== $input->getArgument('ttl') && !is_numeric($input->getArgument('ttl'))) {
            throw new \InvalidArgumentException('Time must be an integer');
        }

        $output->writeln(array(
            '',
            $formatter->formatBlock('You are about to launch maintenance', 'bg=red;fg=white', true),
            '',
        ));

        if ($driver instanceof DriverTtlInterface) {
            if (null === $input->getArgument('ttl')) {
                $output->writeln(array(
                    '',
                    'Do you want to redefine maintenance life time ?',
                    'If yes enter the number of seconds. Press enter to continue',
                    '',
                ));

                $ttl = $dialog->askAndValidate(
                    $output,
                    sprintf('<info>%s</info> [<comment>Default value in your configuration: %s</comment>]%s ', 'Set time', $driver->hasTtl() ? $driver->getTtl() : 'unlimited', ':'),
                    function($value) use ($default) {
                        if (!is_numeric($value) && null === $default) {
                            return null;
                        } elseif (!is_numeric($value)) {
                            throw new \InvalidArgumentException('Time must be an integer');
                        }
                            return $value;
                    },
                    false,
                    isset($default['ttl']) ? $default['ttl'] : 0
                );

                // override argument, to use when setting driver's ttl
                $input->setArgument('ttl', $ttl);
            }

        } else {
            $output->writeln(array(
                '',
                sprintf('<fg=red>Ttl doesn\'t work with %s driver</>', get_class($driver)),
                '',
            ));
        }
    }

    /**
     * Get driver
     *
     * @return AbstractDriver
     */
    private function getDriver()
    {
        return $this->getContainer()->get('lexik_maintenance.driver.factory')->getDriver();
    }
}

