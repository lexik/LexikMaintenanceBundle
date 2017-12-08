<?php

namespace Lexik\Bundle\MaintenanceBundle\Command;

use Doctrine\DBAL\Driver;
use Lexik\Bundle\MaintenanceBundle\Drivers\AbstractDriver;
use Lexik\Bundle\MaintenanceBundle\Drivers\DriverStartdateInterface;
use Lexik\Bundle\MaintenanceBundle\Drivers\DriverTtlInterface;

use Nette\Utils\DateTime;
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
    protected $ttl;
    protected $startdate;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('lexik:maintenance:lock')
            ->setDescription('Lock access to the site while maintenance...')
            ->addArgument('ttl', InputArgument::OPTIONAL, 'Overwrite time to live from your configuration, doesn\'t work with file or shm driver. Time in seconds.', null)
            ->addOption('startdate','s', InputArgument::OPTIONAL, 'Specify the start date of maintenance (does, so far, only work with database driver without dsn). Format: \'yyyy-m-d h:m\'.', null)
            ->addOption('delay', 'd', InputArgument::OPTIONAL, 'Specify the time to wait until start of maintenance (does, so far, only work with database driver without dsn). Time in seconds.', null)
            ->setHelp(<<<EOT

    You can optionally set a time to live of the maintenance: <info>%command.full_name% 3600</info>
   
    You can set a start date or delay the start (from now) of the lock.    
    Set the start date with: <info>%command.full_name% -s "24.12.2017 18:00"</info>
    Or delay the lock with: <info>%command.full_name% -d 3600</info>

    You can execute the lock without a warning message which you need to interact with: <info>%command.full_name% --no-interaction</info>
    Or <info>%command.full_name% 3600 -n</info>
EOT
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $driver = $this->getDriver();
        $message = '';

        if ($input->isInteractive()) {
            if (!$this->askConfirmation('WARNING! Are you sure you wish to continue? (y/n)', $input, $output)) {
                $output->writeln('<error>Maintenance cancelled!</error>');
                return;
            }
        } elseif (null !== $input->getArgument('ttl')) {
            $this->ttl = $input->getArgument('ttl');
        } elseif ($driver instanceof DriverTtlInterface) {
            $this->ttl = $driver->getTtl();
        }

        if (!$input->isInteractive() && $driver instanceof DriverStartdateInterface) {
            $this->startdate = $this->interactStartDate($input);
        }

        // set ttl from command line if given and driver supports it
        if ($driver instanceof DriverTtlInterface) {
            $driver->setTtl($this->ttl);
        }

        if (!is_null($this->startdate)) {
            // set start date from command line if given and driver supports it
            if ($driver instanceof DriverStartdateInterface) {
                $driver->setStartDate($this->startdate);
                $message = $driver->getMessagePrepare($driver->prepareLock());
            } else {
                // this message is already generated within interact()
                // $message = sprintf('<fg=red>Start date / delay doesn\'t work with %s driver</>', get_class($driver));
            }
        } else {
            $message = $driver->getMessageLock($driver->lock());
        }
         
        
        $output->writeln('<info>'.$message.'</info>');
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $driver = $this->getDriver();
        $default = $driver->getOptions();

        $formatter = $this->getHelperSet()->get('formatter');

        if (null !== $input->getArgument('ttl') && !is_numeric($input->getArgument('ttl'))) {
            throw new \InvalidArgumentException('Time must be an integer');
        }

        $output->writeln(array(
            '',
            $formatter->formatBlock('You are about to launch maintenance', 'bg=red;fg=white', true),
            '',
        ));

        $ttl = null;
        if ($driver instanceof DriverTtlInterface) {
            if (null === $input->getArgument('ttl')) {
                $output->writeln(array(
                    '',
                    'Do you want to redefine maintenance life time ?',
                    'If yes enter the number of seconds. Press enter to continue',
                    '',
                ));

                $ttl = $this->askAndValidate(
                    $input,
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
                    1,
                    isset($default['ttl']) ? $default['ttl'] : 0
                );
            }

            $ttl = (int) $ttl;
            $this->ttl = $ttl ? $ttl : $input->getArgument('ttl');
        } else {
            $output->writeln(array(
                '',
                sprintf('<fg=red>Ttl doesn\'t work with %s driver</>', get_class($driver)),
                '',
            ));
        }

        if ($driver instanceof DriverStartdateInterface) {

            if (null != $input->getOption('startdate') && null != $input->getOption('delay')) {
                throw new \InvalidArgumentException('Please specify either start date or delay.');
            }
            $this->startdate = $this->interactStartDate($input);
            
        } else {
            $output->writeln(array(
                '',
                sprintf('<fg=red>Start date/delay doesn\'t work with %s driver</>', get_class($driver)),
                '',
            ));
        }
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

    /**
     * This method ensure that we stay compatible with symfony console 2.3 by using the deprecated dialog helper
     * but use the ConfirmationQuestion when available.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $question
     * @param $validator
     * @param int $attempts
     * @param null $default
     * @return mixed
     */
    protected function askAndValidate(InputInterface $input, OutputInterface $output, $question, $validator, $attempts = 1, $default = null) {
        if (!$this->getHelperSet()->has('question')) {
            return $this->getHelper('dialog')
                ->askAndValidate($output, $question, $validator, $attempts, $default);
        }

        $question = new \Symfony\Component\Console\Question\Question($question, $default);
        $question->setValidator($validator);
        $question->setMaxAttempts($attempts);

        return $this->getHelper('question')
            ->ask($input, $output, $question);
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

    /**
     * Handle input options for start date / delay
     * 
     * @return DateTime | null
     */
    private function interactStartDate(InputInterface $input)
    {
        $startDate = null;
        
        if (null != $input->getOption('startdate')) {
            $value = $input->getOption('startdate');
            $startDate = \DateTime::createFromFormat('Y-m-d H:i', $value);

            if (false === $startDate) {
                throw new \InvalidArgumentException('Start date must be given in format: \'yyyy-m-d h:m\'.');
            }

        } elseif (null != $input->getOption('delay')) {
            $value = $input->getOption('delay');

            if (!is_numeric($value)) {
                throw new \InvalidArgumentException('Delay must be an integer.');
            }

            $startDate = new \DateTime();
            $intervalSpec = 'PT' . $value . 'S';
            $startDate->add(new \DateInterval($intervalSpec)); // add delay to current date time
        }
        
        return $startDate;
    }
}
