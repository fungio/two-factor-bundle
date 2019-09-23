<?php

namespace Fungio\TwoFactorBundle\Storage;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use TwoFAS\Api\IntegrationUser;
use Fungio\TwoFactorBundle\Model\Entity\UserInterface as FungioUserInterface;
use Fungio\TwoFactorBundle\Util\IntegrationUserManager;
use Fungio\TwoFactorBundle\Util\UserManager;
use \LogicException;

/**
 * Store Fungio User, and Integration User in session.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Storage
 */
class UserSessionStorage implements UserStorageInterface
{
    const USER_KEY             = 'fungio_two_factor.session.user';
    const INTEGRATION_USER_KEY = 'fungio_two_factor.session.integration_user';

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var IntegrationUserManager
     */
    private $integrationUserManager;

    /**
     * @param SessionInterface       $session
     * @param TokenStorageInterface  $tokenStorage
     * @param ObjectManager          $objectManager
     * @param UserManager            $userManager
     * @param IntegrationUserManager $integrationUserManager
     */
    public function __construct(
        SessionInterface $session,
        TokenStorageInterface $tokenStorage,
        ObjectManager $objectManager,
        UserManager $userManager,
        IntegrationUserManager $integrationUserManager
    ) {
        $this->session                = $session;
        $this->tokenStorage           = $tokenStorage;
        $this->objectManager          = $objectManager;
        $this->userManager            = $userManager;
        $this->integrationUserManager = $integrationUserManager;
    }

    /**
     * @inheritdoc
     */
    public function getUser()
    {
        if ($this->session->has(self::USER_KEY)) {
            return $this->objectManager->merge($this->session->get(self::USER_KEY));
        }

        $loggedUser = $this->getLoggedUser();

        if (null === $loggedUser) {
            return null;
        }

        $user = $this->userManager->findByUserName($loggedUser->getUsername());


        if (!is_null($user)) {
            $integrationUser = $this->integrationUserManager->findByExternalId($user->getId());
            $user->setIntegrationUser($integrationUser);
            $this->session->set(self::USER_KEY, $user);
        }

        return $user;
    }

    /**
     * @inheritdoc
     */
    public function createUser()
    {
        $loggedUser = $this->getLoggedUser();

        if (null === $loggedUser) {
            throw new LogicException('Can\'t store Fungio User when not logged in.');
        }

        $user            = $this->userManager->createUser($loggedUser);
        $integrationUser = $this->storeIntegrationUser($user);

        $user->setIntegrationUser($integrationUser);
        $this->session->set(self::USER_KEY, $user);

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function updateUser(FungioUserInterface $user)
    {
        $this->userManager->updateUser($user);
        $this->session->set(self::USER_KEY, $user);

        return $user;
    }

    /**
     * @inheritdoc
     */
    public function storeIntegrationUser(FungioUserInterface $user)
    {
        $integrationUser = $this->integrationUserManager->createUser($user);

        return $integrationUser;
    }

    /**
     * @inheritDoc
     */
    public function updateIntegrationUser(IntegrationUser $user)
    {
        $integrationUser = $this->integrationUserManager->updateUser($user);

        return $integrationUser;
    }

    /**
     * @return UserInterface|null
     */
    private function getLoggedUser()
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return null;
        }

        return $user;
    }
}
