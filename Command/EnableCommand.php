<?php

namespace Fungio\TwoFactorBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Enable Two Factor Authentication
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Command
 */
class EnableCommand extends SwitchCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('fungio:enable')
            ->setDescription('Enable Two Factor Authentication');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkOptions();
        $this->switchStatus(true);
        $output->writeln('Two Factor Authentication Service has been <info>enabled.</info>');
    }
}