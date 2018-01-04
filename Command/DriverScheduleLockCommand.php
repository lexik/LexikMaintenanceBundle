<?php

namespace Lexik\Bundle\MaintenanceBundle\Command;

use Lexik\Bundle\MaintenanceBundle\Drivers\DriverStartdateInterface;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Create a lock action
 *
 * @package LexikMaintenanceBundle
 * @author  Wolfram Eberius <edrush@posteo.de>
 */
class DriverScheduleLockCommand extends AbstractLockCommand
{
    protected $startdate;

    public function getTtlFromInput(InputInterface $input)
    {
        return $input->getOption('ttl');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('lexik:maintenance:schedule-lock')
            ->setDescription('Schedule maintenance mode for your site...')
            ->addArgument('delay', InputArgument::OPTIONAL, 'Specify the time to wait until start of maintenance (does, so far, only work with database driver without dsn). Time in seconds.', null)
            ->addOption('startdate','s', InputOption::VALUE_OPTIONAL, 'Alternatively, specify the start date of maintenance (does, so far, only work with database driver without dsn). Format: \'yyyy-m-d h:m\'.', null)
            ->addOption('ttl','t', InputOption::VALUE_OPTIONAL, 'Overwrite time to live from your configuration, doesn\'t work with file or shm driver. Time in seconds.', null)
            ->setHelp(<<<EOT
    You can schedule maintenance to start in 60 minutes: <info>%command.full_name% 3600</info>
    You can alternatively set a start date of the lock with: <info>%command.full_name% -s "2017-12-24 18:00"</info>
    You can optionally set a time to live of the maintenance: <info>%command.full_name% 3600 --ttl 3600</info>
EOT
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $driver = $this->getDriver();

        if ($driver instanceof DriverStartdateInterface) {

            if (!is_null($this->startdate)) {
                // set start date from command line if given and driver supports it
                $driver->setStartDate($this->startdate);
                $driver->setTtl($this->ttl);
                $message = $driver->getMessageScheduleLock($driver->scheduleLock());
            } else {
                // this message is already generated within interact()
                $message = sprintf('<fg=red>Delay or start date must be set.</>', get_class($driver));
            }
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
     * Handle input options for start date / delay
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $startDate = null;

        if (null != $input->getArgument('delay')) {
            $value = $input->getArgument('delay');

            if (!is_numeric($value)) {
                throw new \InvalidArgumentException('Delay must be an integer.');
            }

            $startDate = new \DateTime();
            $intervalSpec = 'PT' . $value . 'S';
            $startDate->add(new \DateInterval($intervalSpec)); // add delay to current date time
        } elseif (null != $input->getOption('startdate')) {
            $value = $input->getOption('startdate');
            $startDate = \DateTime::createFromFormat('Y-m-d H:i', $value);

            if (false === $startDate) {
                throw new \InvalidArgumentException('Start date must be given in format: \'yyyy-m-d h:m\'.');
            }

        }

        $this->startdate = $startDate;
        
        $this->interactWithTtl($input, $output);
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
