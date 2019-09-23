<?php

namespace Fungio\TwoFactorBundle\Tests\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use TwoFAS\Api\IntegrationUser;
use Fungio\TwoFactorBundle\Event\IntegrationUserConfigurationCompleteEvent;
use Fungio\TwoFactorBundle\EventListener\TrustedDeviceListener;
use Fungio\TwoFactorBundle\Model\Entity\RememberMeToken;
use Fungio\TwoFactorBundle\Model\Persister\InMemoryObjectPersister;
use Fungio\TwoFactorBundle\Model\Persister\InMemoryRepository;
use Fungio\TwoFactorBundle\Model\Persister\InMemoryRepositoryInterface;
use Fungio\TwoFactorBundle\Storage\UserStorageInterface;
use Fungio\TwoFactorBundle\Tests\UserEntity;

class TrustedDeviceListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InMemoryRepositoryInterface
     */
    private $tokenRepository;

    /**
     * @var InMemoryRepositoryInterface
     */
    private $userRepository;

    /**
     * @var TrustedDeviceListener
     */
    private $listener;

    public function setUp()
    {
        parent::setUp();

        $this->userRepository  = new InMemoryRepository(UserEntity::class, 'id');
        $this->tokenRepository = new InMemoryRepository(RememberMeToken::class, 'series');
        $tokenPersister        = new InMemoryObjectPersister($this->tokenRepository);
        $userStorage           = $this
            ->getMockBuilder(UserStorageInterface::class)
            ->setMethods(['has'])
            ->getMockForAbstractClass();
        $userStorage->method('has')->willReturn(false);

        $objectManager         = $this->getMockForAbstractClass(ObjectManager::class);
        $this->listener        = new TrustedDeviceListener($userStorage, $objectManager, $tokenPersister);

        $user = new UserEntity();
        $user->setId('4');

        $token = new RememberMeToken();
        $token->setUser($user);

        $user->addToken($token);

        $this->userRepository->add($user);
        $objectManager->method('merge')->willReturn($user);
    }

    public function testRemoveTrustedDevices()
    {
        $this->listener->onTotpSecretChanged($this->getEvent());

        /** @var UserEntity $user */
        $user = $this->userRepository->find('4');
        $this->assertCount(0, $user->getTokens());
    }

    /**
     * @return IntegrationUserConfigurationCompleteEvent
     */
    private function getEvent()
    {
        $integrationUser = new IntegrationUser();
        $integrationUser->setExternalId(4);

        return new IntegrationUserConfigurationCompleteEvent($integrationUser);
    }
}

