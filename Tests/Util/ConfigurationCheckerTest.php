<?php

namespace Fungio\TwoFactorBundle\Tests\Util;

use Fungio\Api\Methods;
use Fungio\TwoFactorBundle\Cache\EmptyCacheStorage;
use Fungio\TwoFactorBundle\Model\Entity\Option;
use Fungio\TwoFactorBundle\Model\Entity\OptionInterface;
use Fungio\TwoFactorBundle\Model\Persister\InMemoryObjectPersister;
use Fungio\TwoFactorBundle\Model\Persister\InMemoryRepository;
use Fungio\TwoFactorBundle\Model\Persister\InMemoryRepositoryInterface;
use Fungio\TwoFactorBundle\Storage\UserStorageInterface;
use Fungio\TwoFactorBundle\Tests\UserEntity;
use Fungio\TwoFactorBundle\Util\ConfigurationChecker;

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

    public function testFungioDisabledWhenNotConfigured()
    {
        $this->assertFalse($this->checker->isFungioEnabled());
    }

    public function testFungioDisabled()
    {
        $option = $this->getOption();
        $option
            ->setName(OptionInterface::STATUS)
            ->setValue(0);

        $this->optionRepository->add($option);

        $this->assertFalse($this->checker->isFungioEnabled());
    }

    public function testFungioEnabled()
    {
        $option = $this->getOption();
        $option
            ->setName(OptionInterface::STATUS)
            ->setValue(1);

        $this->optionRepository->add($option);

        $this->assertTrue($this->checker->isFungioEnabled());
    }

    public function testIntegrationUserNotConfiguredWhenUserNotExists()
    {
        $this->userStorage->method('getUser')->willReturn(null);

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
