<?php

namespace TwoFAS\TwoFactorBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\DependencyInjection\ContainerInterface;
use TwoFAS\TwoFactorBundle\Model\Entity\Option;
use TwoFAS\TwoFactorBundle\Model\Persister\InMemoryObjectPersister;
use TwoFAS\TwoFactorBundle\Model\Persister\InMemoryRepository;
use TwoFAS\TwoFactorBundle\Model\Persister\InMemoryRepositoryInterface;
use TwoFAS\Account\Integration;
use TwoFAS\Account\Key;

class CommandTestCase extends KernelTestCase
{
    /**
     * @var Application
     */
    protected $application;

    /**
     * @var Command
     */
    protected $command;

    /**
     * @var ApplicationTester
     */
    protected $applicationTester;

    /**
     * @var InMemoryRepositoryInterface
     */
    protected $optionRepository;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function setUp()
    {
        parent::setUp();

        $kernel = $this->createKernel();
        $kernel->boot();
        $this->container = $kernel->getContainer();

        $this->application = new Application($kernel);
        $this->application->setAutoExit(false);
        $this->application->add($this->command);

        $this->applicationTester = new ApplicationTester($this->application);
        $this->optionRepository  = new InMemoryRepository(Option::class, 'id');
        $optionPersister         = new InMemoryObjectPersister($this->optionRepository);

        $this->container->set('two_fas_two_factor.option_persister', $optionPersister);
    }

    /**
     * @return Integration
     */
    protected function getIntegration()
    {
        $integration = new Integration();
        $integration
            ->setId(rand(1, 1000))
            ->setLogin(uniqid());

        return $integration;
    }

    /**
     * @return Key
     */
    protected function getIntegrationKey()
    {
        return new Key(uniqid(uniqid(), true));
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return Option
     */
    protected function getOption($name, $value)
    {
        $option = new Option();
        $option
            ->setName($name)
            ->setValue($value);
        return $option;
    }

    protected function addOption($option)
    {
        $this->optionRepository->add($option);
    }
}
