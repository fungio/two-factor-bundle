<?php

namespace Fungio\TwoFactorBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Disable Two Factor Authentication
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Command
 */
class DisableCommand extends SwitchCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('fungio:disable')
            ->setDescription('Disable Two Factor Authentication');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkOptions();
        $this->switchStatus(false);
        $output->writeln('Two Factor Authentication Service has been <comment>disabled.</comment>');
    }
}