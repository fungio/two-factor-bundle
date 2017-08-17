<?php

namespace TwoFAS\TwoFactorBundle\Tests\Util;

use TwoFAS\Api\IntegrationUser;
use TwoFAS\Api\Methods;
use TwoFAS\TwoFactorBundle\Cache\EmptyCacheStorage;
use TwoFAS\TwoFactorBundle\Model\Entity\Option;
use TwoFAS\TwoFactorBundle\Model\Entity\OptionInterface;
use TwoFAS\TwoFactorBundle\Model\Persister\InMemoryObjectPersister;
use TwoFAS\TwoFactorBundle\Model\Persister\InMemoryRepository;
use TwoFAS\TwoFactorBundle\Model\Persister\InMemoryRepositoryInterface;
use TwoFAS\TwoFactorBundle\Storage\UserStorageInterface;
use TwoFAS\TwoFactorBundle\Tests\UserEntity;
use TwoFAS\TwoFactorBundle\Util\ConfigurationChecker;

class ConfigurationCheckerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InMemoryRepositoryInterface
     */
    private $optionRepository;

    /**
     * @var UserStorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userStorage;

    /**
     * @var ConfigurationChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $checker;

    public function setUp()
    {
        parent::setUp();

        $this->optionRepository = new InMemoryRepository(Option::class, 'id');
        $optionPersister        = new InMemoryObjectPersister($this->optionRepository);
        $this->userStorage      = $this->getMockForAbstractClass(UserStorageInterface::class);

        $this->checker = new ConfigurationChecker($optionPersister, $this->userStorage, new EmptyCacheStorage());
    }

    public function testTwoFASDisabledWhenNotConfigured()
    {
        $this->assertFalse($this->checker->isTwoFASEnabled());
    }

    public function testTwoFASDisabled()
    {
        $option = $this->getOption();
        $option
            ->setName(OptionInterface::STATUS)
            ->setValue(0);

        $this->optionRepository->add($option);

        $this->assertFalse($this->checker->isTwoFASEnabled());
    }

    public function testTwoFASEnabled()
    {
        $option = $this->getOption();
        $option
            ->setName(OptionInterface::STATUS)
            ->setValue(1);

        $this->optionRepository->add($option);

        $this->assertTrue($this->checker->isTwoFASEnabled());
    }

    public function testIntegrationUserNotConfiguredWhenUserNotExists()
    {
        $this->userStorage->method('getUser')->willReturn(null);

        $this->assertFalse($this->checker->isSecondFactorEnabledForUser());
    }

    public function testIntegrationUserNotConfiguredWhenIntegrationUserNotExists()
    {
        $this->userStorage->method('getUser')->willReturn($this->getUser());
        $this->userStorage->method('getIntegrationUser')->willReturn(null);

        $this->assertFalse($this->checker->isSecondFactorEnabledForUser());
    }

    public function testIntegrationUserNotConfiguredWhenHasNoActiveMethod()
    {
        $this->userStorage->method('getUser')->willReturn($this->getUser());
        $this->userStorage->method('getIntegrationUser')->willReturn(new IntegrationUser());

        $this->assertFalse($this->checker->isSecondFactorEnabledForUser());
    }

    public function testUserConfigured()
    {
        $user = $this->getUser();
        $user
            ->setUsername('tom')
            ->enableChannel(Methods::TOTP);

        $this->userStorage->method('getUser')->willReturn($user);

        $this->assertTrue($this->checker->isSecondFactorEnabledForUser());
    }

    public function testUserNotConfigured()
    {
        $user = $this->getUser();
        $user
            ->setUsername('tom');

        $this->userStorage->method('getUser')->willReturn($user);

        $this->assertFalse($this->checker->isSecondFactorEnabledForUser());
    }

    public function testUserNotConfiguredIfNotExists()
    {
        $this->userStorage->method('getUser')->willReturn(null);

        $this->assertFalse($this->checker->isSecondFactorEnabledForUser());
    }

    /**
     * @return Option
     */
    protected function getOption()
    {
        return new Option();
    }

    /**
     * @return UserEntity
     */
    protected function getUser()
    {
        return new UserEntity();
    }
}
