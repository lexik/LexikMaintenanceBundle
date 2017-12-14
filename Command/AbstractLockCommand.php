<?php

namespace Lexik\Bundle\MaintenanceBundle\Command;

use Lexik\Bundle\MaintenanceBundle\Drivers\AbstractDriver;
use Lexik\Bundle\MaintenanceBundle\Drivers\DriverTtlInterface;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Create a lock action
 *
 * @package LexikMaintenanceBundle
 * @author  Wolfram Eberius <edrush@posteo.de>
 */
abstract class AbstractLockCommand extends ContainerAwareCommand
{
    protected $ttl;

    /**
     * @param InputInterface $input
     * @return integer|null
     */
    abstract protected function getTtlFromInput(InputInterface $input);
    
    protected function interactWithTtl(InputInterface $input, OutputInterface $output)
    {
        $ttl = null;
        
        $driver = $this->getDriver();
        $default = $driver->getOptions();
        
        if ($driver instanceof DriverTtlInterface) {
            if (null === $this->getTtlFromInput($input)) {
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
            $this->ttl = $ttl ? $ttl : $this->getTtlFromInput($input);
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
    protected function getDriver()
    {
        return $this->getContainer()->get('lexik_maintenance.driver.factory')->getDriver();
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
}
