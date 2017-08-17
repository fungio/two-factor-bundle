<?php

namespace TwoFAS\TwoFactorBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use TwoFAS\TwoFactorBundle\Command\DeleteAccountCommand;
use TwoFAS\TwoFactorBundle\Model\Entity\Option;
use TwoFAS\TwoFactorBundle\Model\Persister\InMemoryObjectPersister;
use TwoFAS\TwoFactorBundle\Model\Persister\InMemoryRepository;
use TwoFAS\TwoFactorBundle\Model\Persister\InMemoryRepositoryInterface;
use TwoFAS\TwoFactorBundle\Tests\UserEntity;

class DeleteAccountCommandTest extends KernelTestCase
{
    /**
     * @var Command
     */
    private $command;

    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * @var InMemoryRepositoryInterface
     */
    private $optionRepository;

    /**
     * @var InMemoryRepositoryInterface
     */
    private $userRepository;

    public function setUp()
    {
        parent::setUp();

        $kernel = $this->createKernel();
        $kernel->boot();
        $container = $kernel->getContainer();

        $application = new Application($kernel);
        $application->setAutoExit(false);
        $application->add(new DeleteAccountCommand());

        $this->command       = $application->find('twofas:account:delete');
        $this->commandTester = new CommandTester($this->command);

        $this->optionRepository = new InMemoryRepository(Option::class, 'id');
        $optionPersister        = new InMemoryObjectPersister($this->optionRepository);

        $this->userRepository = new InMemoryRepository(UserEntity::class, 'id');
        $userPersister        = new InMemoryObjectPersister($this->userRepository);

        $container->set('two_fas_two_factor.option_persister', $optionPersister);
        $container->set('two_fas_two_factor.user_persister', $userPersister);
    }

    public function testExit()
    {
        $helper = $this->command->getHelper('question');
        $helper->setInputStream($this->getInputStream("No\n"));

        $this->commandTester->execute(['command' => $this->command->getName()]);
        $output = $this->commandTester->getDisplay();
        $this->assertContains('Warning! This action will destroy all your 2FAS data from database! Are You sure?', $output);
    }

    public function testDeleteAccount()
    {
        $option = new Option();
        $option->setName('foo')->setValue('bar');

        $user = new UserEntity();
        $user->setId(1)->setUsername('admin');

        $this->optionRepository->add($option);
        $this->userRepository->add($user);

        $helper = $this->command->getHelper('question');
        $helper->setInputStream($this->getInputStream("Yes\n"));

        $this->commandTester->execute(['command' => $this->command->getName()]);
        $output = $this->commandTester->getDisplay();

        $this->assertContains('2FAS data is destroyed successfully.', $output);

        $this->assertCount(0, $this->optionRepository->findAll());
        $this->assertCount(0, $this->userRepository->findAll());
    }

    protected function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);

        return $stream;
    }
}
