<?php

namespace TwoFAS\TwoFactorBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Psr\SimpleCache\CacheInterface;
use TwoFAS\TwoFactorBundle\Model\Entity\OptionInterface;
use TwoFAS\TwoFactorBundle\Model\Entity\UserInterface;
use TwoFAS\TwoFactorBundle\Model\Persister\ObjectPersisterInterface;

/**
 * Deletes all 2FAS data from database.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Command
 */
class DeleteAccountCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('twofas:account:delete')
            ->setDescription('Delete all 2FAS data from database.');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->confirm($input, $output)) {
            return;
        }

        $this->deleteAccount();

        $output->writeln('2FAS data is destroyed <info>successfully</info>.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return bool
     */
    protected function confirm(InputInterface $input, OutputInterface $output)
    {
        $style = new OutputFormatterStyle('red', null, ['bold', 'blink']);
        $output->getFormatter()->setStyle('fire', $style);

        $helper = $this->getHelper('question');
        $output->writeln('');
        $question = new ConfirmationQuestion('<fire>Warning!</fire> This action will destroy all your 2FAS data from database! <comment>Are You sure? (yes/no)</comment> ', false);

        return $helper->ask($input, $output, $question);
    }

    protected function deleteAccount()
    {
        $optionPersister = $this->getOptionPersister();
        $options         = $optionPersister->getRepository()->findAll();
        $userPersister   = $this->getUserPersister();
        $users           = $userPersister->getRepository()->findAll();

        array_map(function(OptionInterface $option) use ($optionPersister) {
            $optionPersister->removeEntity($option);
        }, $options);

        array_map(function(UserInterface $user) use ($userPersister) {
            $userPersister->removeEntity($user);
        }, $users);

        $this->getCache()->clear();
    }

    /**
     * @return ObjectPersisterInterface
     */
    private function getOptionPersister()
    {
        return $this->getContainer()->get('two_fas_two_factor.option_persister');
    }

    /**
     * @return ObjectPersisterInterface
     */
    private function getUserPersister()
    {
        return $this->getContainer()->get('two_fas_two_factor.user_persister');
    }

    /**
     * @return CacheInterface
     */
    protected function getCache()
    {
        return $this->getContainer()->get('two_fas_two_factor.cache.storage');
    }
}
