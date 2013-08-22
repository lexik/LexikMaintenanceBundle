<?php

namespace Lexik\Bundle\MaintenanceBundle\Command;

use Lexik\Bundle\MaintenanceBundle\Drivers\FileDriver;

use Lexik\Bundle\MaintenanceBundle\Drivers\ShmDriver;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
    protected $driver;

    /**
     * (non-PHPdoc)
     * @see Symfony\Component\Console\Command.Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('lexik:maintenance:lock')
            ->setDescription('Lock access to the site while maintenance...');

        $this->addOption(
            'set-ttl', 'ttl',
            InputOption::VALUE_NONE,
            'Overwrite time to life from your configuration, doesn\'t work with file or shm driver. Time in seconds.',
            null
        );

        $this->setHelp(<<<EOT

    You can optinally set a time to life of the maintenance

   <info>%command.full_name% --set-ttl ...</info>

    You can execute the lock without a warning message wich you need to interact with:

    <info>%command.full_name% --no-interaction</info>
EOT
                );
    }

    /**
     * (non-PHPdoc)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->lock($input, $output);

        $this->driver->setTtl($input->getOption('set-ttl'));

        $lockMessage = $this->driver->getMessageLock($this->driver->lock());

        $output->writeln('<info>'.$lockMessage.'</info>');
    }

    /**
     * (non-PHPdoc)
     * @see Symfony\Component\Console\Command.Command::interact()
     */
    protected function lock(InputInterface $input, OutputInterface $output)
    {
        $this->driver = $this->getContainer()->get('lexik_maintenance.driver.factory')->getDriver();

        $formatter = $this->getHelperSet()->get('formatter');
        $dialog = $this->getHelperSet()->get('dialog');

        if ($input->getOption('no-interaction', false)) {
            $confirmation = true;
        }
        else {
            // confirme
            $output->writeln(array(
                '',
                $formatter->formatBlock('You are about to launch maintenance', 'bg=red;fg=white', true),
                '',
            ));

            $confirmation = $dialog->askConfirmation(
                $output,
                '<question>WARNING! Are you sure you wish to continue? (y/n)</question>',
                'y'
            );
        }

        if ($confirmation === true) {
            // pass option ttl in command ?
            $optiontTtl = false !== $input->getOption('set-ttl') ? true : false;

            // Get default value
            $default = $this->driver->getOptions();

            if (false === $optiontTtl && !($this->driver instanceof FileDriver) && !($this->driver instanceof ShmDriver)) {
                $output->writeln(array(
                            '',
                            'Do you want to redefine maintenance life time ?',
                            'If yes enter the number of seconds',
                            '<fg=red>This doesn\'t work with file driver</>',
                            '',
                ));

                $ttl = $dialog->askAndValidate(
                    $output,
                    sprintf('<info>%s</info> [<comment>Default value in your configuration: %s</comment>]%s ', 'Set time', isset($default['ttl']) ? $default['ttl'] : 'unlimited', ':'),
                    function($value) use($default) {
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

                $input->setOption('set-ttl', $ttl);
            } else {
                $input->setOption('set-ttl', isset($default['ttl']) ? $default['ttl'] : null);
            }
        } else {
            $output->writeln('<error>Maintenance cancelled!</error>');
            return exit;
        }
    }
}

