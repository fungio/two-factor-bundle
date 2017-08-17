<?php

namespace TwoFAS\TwoFactorBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TwoFAS\TwoFactorBundle\Model\Entity\OptionInterface;
use TwoFAS\TwoFactorBundle\Model\Persister\ObjectPersisterInterface;

/**
 * Show status of Two Factor Authentication
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Command
 */
class StatusCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('twofas:status')
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
        return $this->getContainer()->get('two_fas_two_factor.option_persister');
    }
}