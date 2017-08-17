<?php

namespace TwoFAS\TwoFactorBundle\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use TwoFAS\TwoFactorBundle\Event\IntegrationUserConfigurationCompleteEvent;
use TwoFAS\TwoFactorBundle\Model\Entity\UserInterface;
use TwoFAS\TwoFactorBundle\Model\Persister\ObjectPersisterInterface;
use TwoFAS\TwoFactorBundle\Storage\UserStorageInterface;

/**
 * Listen for TOTP Secret changed (in configuration totp controller action)
 * and remove trusted devices.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\EventListener
 */
class TrustedDeviceListener
{
    /**
     * @var UserStorageInterface
     */
    private $userStorage;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ObjectPersisterInterface
     */
    private $tokenPersister;

    /**
     * @param UserStorageInterface     $userStorage
     * @param ObjectManager            $objectManager
     * @param ObjectPersisterInterface $tokenPersister
     */
    public function __construct(UserStorageInterface $userStorage, ObjectManager $objectManager, ObjectPersisterInterface $tokenPersister)
    {
        $this->userStorage    = $userStorage;
        $this->objectManager  = $objectManager;
        $this->tokenPersister = $tokenPersister;
    }

    /**
     * @param IntegrationUserConfigurationCompleteEvent $event
     */
    public function onTotpSecretChanged(IntegrationUserConfigurationCompleteEvent $event)
    {
        /** @var UserInterface $user */
        $user = $this->objectManager->merge($this->userStorage->getUser());

        $this->removeTrustedDevices($user);
    }

    /**
     * @param UserInterface $user
     */
    private function removeTrustedDevices(UserInterface $user)
    {
        foreach ($user->getTokens() as $token) {
            $this->tokenPersister->removeEntity($token);
        }
    }
}

