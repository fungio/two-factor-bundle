<?php

namespace Fungio\TwoFactorBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Fungio\TwoFactorBundle\Model\Entity\OptionInterface;
use Fungio\TwoFactorBundle\Model\Persister\ObjectPersisterInterface;

/**
 * Show status of Two Factor Authentication
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Command
 */
class StatusCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('fungio:status')
            ->setDescription('Two Factor Authentication Status');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $optionPersister = $this->getOptionPersister();
        /** @var OptionInterface|null $option */
        $option = $optionPersister->getRepository()->findOneBy(['name' => OptionInterface::STATUS]);

        if (is_null($option) || false === (bool) $option->getValue()) {
            $status = '<comment>disabled</comment>';
        } else {
            $status = '<info>enabled</info>';
        }

        $output->writeln('Two Factor Authentication Service Status: ' . $status);
    }

    /**
     * @return ObjectPersisterInterface
     */
    private function getOptionPersister()
    {
        return $this->getContainer()->get('fungio_two_factor.option_persister');
    }
}