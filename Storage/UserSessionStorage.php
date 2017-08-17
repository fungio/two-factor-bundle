<?php

namespace TwoFAS\TwoFactorBundle\Storage;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use TwoFAS\Api\IntegrationUser;
use TwoFAS\TwoFactorBundle\Model\Entity\UserInterface as TwoFASUserInterface;
use TwoFAS\TwoFactorBundle\Util\IntegrationUserManager;
use TwoFAS\TwoFactorBundle\Util\UserManager;
use \LogicException;

/**
 * Store TwoFAS User, and Integration User in session.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Storage
 */
class UserSessionStorage implements UserStorageInterface
{
    const USER_KEY             = 'twofas_two_factor.session.user';
    const INTEGRATION_USER_KEY = 'twofas_two_factor.session.integration_user';

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
            throw new LogicException('Can\'t store TwoFAS User when not logged in.');
        }

        $user = $this->userManager->createUser($loggedUser);
        $this->session->set(self::USER_KEY, $user);

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function updateUser(TwoFASUserInterface $user)
    {
        $this->userManager->updateUser($user);
        $this->session->set(self::USER_KEY, $user);

        return $user;
    }

    /**
     * @inheritdoc
     */
    public function getIntegrationUser()
    {
        if ($this->session->has(self::INTEGRATION_USER_KEY)) {
            return unserialize($this->session->get(self::INTEGRATION_USER_KEY));
        }

        if (is_null($this->getUser())) {
            return null;
        }

        $integrationUser = $this->integrationUserManager->findByExternalId($this->getUser()->getId());

        if (!is_null($integrationUser)) {
            $this->session->set(self::INTEGRATION_USER_KEY, serialize($integrationUser));
        }

        return $integrationUser;

    }

    /**
     * @inheritdoc
     */
    public function storeIntegrationUser(TwoFASUserInterface $user)
    {
        $integrationUser = $this->integrationUserManager->createUser($user);
        $this->session->set(self::INTEGRATION_USER_KEY, serialize($integrationUser));

        return $integrationUser;
    }

    /**
     * @inheritDoc
     */
    public function updateIntegrationUser(IntegrationUser $user)
    {
        $integrationUser = $this->integrationUserManager->updateUser($user);
        $this->session->set(self::INTEGRATION_USER_KEY, serialize($integrationUser));

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