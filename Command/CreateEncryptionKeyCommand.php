<?php

namespace Fungio\TwoFactorBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Fungio\Encryption\AESGeneratedKey;

/**
 * Creates encryption key for encrypt Two FAS Data (Login and Token)
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Command
 */
class CreateEncryptionKeyCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('fungio:encryption-key:create')
            ->setDescription('Create encryption key for Two FAS Data');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $key = new AESGeneratedKey();

        $output->writeln([
                '',
                '<info>Your Two FAS Encryption Key:</info> ' . base64_encode($key->getValue()),
                '',
                'Put this key in your parameters.yml'
            ]
        );
    }
}