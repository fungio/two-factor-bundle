<?php

namespace TwoFAS\TwoFactorBundle\Features\Context;

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use TwoFAS\Encryption\Cryptographer;
use TwoFAS\TwoFactorBundle\Model\Entity\OptionInterface;
use TwoFAS\TwoFactorBundle\Model\Entity\UserInterface;
use TwoFAS\TwoFactorBundle\Model\Persister\ObjectPersisterInterface;
use TwoFAS\TwoFactorBundle\Util\UserManager;

/**
 * Context for prepare app for tests (before, after hooks)
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Features\Context
 */
class MainContext implements KernelAwareContext
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @inheritDoc
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel    = $kernel;
        $this->container = $kernel->getContainer();
    }

    /**
     * @BeforeScenario @ajax
     */
    public function beforeAjax(BeforeScenarioScope $scope)
    {
        $this->prepareRoutesForJS('test');
    }

    /**
     * Generate routes for JS in test environment because test mode have own session
     * and ajax requests doesn't work if we not prefixed base_url with app_test.php
     * After scenario it backed up to prod.
     *
     * @param string $env
     */
    private function prepareRoutesForJS($env)
    {
        $context = $this->container->get('router')->getContext();

        switch ($env) {
            case 'test':
                $context->setBaseUrl('/app_test.php');
                break;
            default:
                $context->setBaseUrl('/');
                break;
        }

        $this->runCommand('fos:js-routing:dump');
    }

    /**
     * @param string $command
     * @param array  $arguments
     */
    private function runCommand($command, array $arguments = [])
    {
        $console = new Application($this->kernel);
        $console->setAutoExit(false);

        $inputs = array_merge(['command' => $command], $arguments);
        $input  = new ArrayInput($inputs);

        $output = new NullOutput();
        $console->run($input, $output);
    }

    /**
     * @AfterScenario @ajax
     */
    public function afterAjax(AfterScenarioScope $scope)
    {
        $this->prepareRoutesForJS('prod');
    }

    /**
     * @BeforeScenario
     */
    public function beforeScenario(BeforeScenarioScope $scope)
    {
        $this->cleanDatabase();
    }

    private function cleanDatabase()
    {
        $this->dropSchema();
        $this->createSchema();
    }

    private function dropSchema()
    {
        $this->runCommand('doctrine:schema:drop', ['--force' => null]);
    }

    private function createSchema()
    {
        $this->runCommand('doctrine:schema:create');
    }

    /**
     * @AfterScenario
     */
    public function afterScenario(AfterScenarioScope $scope)
    {
        $this->cleanDatabase();
    }

    /**
     * @Given Account created
     */
    public function accountCreated()
    {
        $this->fillUsers();
        $this->fillOptions();
    }

    /**
     * @Given Channel :channelName for user :username is disabled
     */
    public function channelIsDisabled($channelName, $username)
    {
        /** @var UserManager $userManager */
        $userManager = $this->container->get('two_fas_two_factor.util.user_manager');
        $user = $userManager->findByUserName($username);
        $user->disableChannel($channelName);

        $userManager->updateUser($user);
    }

    private function fillUsers()
    {
        $userNames = ['admin', 'user_totp', 'user_sms', 'user_call', 'user_email'];

        /** @var ObjectPersisterInterface $userPersister */
        $userPersister = $this->container->get('two_fas_two_factor.user_persister');

        array_map(function($username) use ($userPersister) {
            /** @var UserInterface $user */
            $user = $userPersister->getEntity();
            $user->setUsername($username);

            if ('admin' != $username) {
                $channel = explode('_', $username)[1];
                $user->enableChannel($channel);
            }

            $userPersister->saveEntity($user);
        }, $userNames);
    }

    private function fillOptions()
    {
        /** @var ObjectPersisterInterface $optionPersister */
        $optionPersister   = $this->container->get('two_fas_two_factor.option_persister');
        $encryptionStorage = $this->container->get('two_fas_two_factor.storage.encryption_storage');
        $cryptographer     = Cryptographer::getInstance($encryptionStorage);

        /** @var OptionInterface $login */
        $login = $optionPersister->getEntity();
        $login->setName(OptionInterface::LOGIN)->setValue($cryptographer->encrypt('admin'));

        /** @var OptionInterface $token */
        $token = $optionPersister->getEntity();
        $token->setName(OptionInterface::TOKEN)->setValue($cryptographer->encrypt('adminpass'));

        /** @var OptionInterface $status */
        $status = $optionPersister->getEntity();
        $status->setName(OptionInterface::STATUS)->setValue(0);

        $optionPersister->saveEntity($login);
        $optionPersister->saveEntity($token);
        $optionPersister->saveEntity($status);
    }

    /**
     * @Given Second factor enabled
     */
    public function secondFactorEnabled()
    {
        /** @var ObjectPersisterInterface $optionPersister */
        $optionPersister = $this->container->get('two_fas_two_factor.option_persister');

        /** @var OptionInterface|null $option */
        $option = $optionPersister->getRepository()->findOneBy(['name' => OptionInterface::STATUS]);
        $option->setValue(1);

        $optionPersister->saveEntity($option);
    }
}