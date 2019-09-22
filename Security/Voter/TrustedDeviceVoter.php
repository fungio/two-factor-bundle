<?php

namespace Fungio\TwoFactorBundle\Security\Voter;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Fungio\TwoFactorBundle\Model\Entity\RememberMeTokenInterface;
use Fungio\TwoFactorBundle\Model\Entity\UserInterface as FungioUserInterface;
use Fungio\TwoFactorBundle\Storage\UserStorageInterface;

/**
 * Check that logged user can remove only own devices.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Security\Voter
 */
class TrustedDeviceVoter extends Voter
{
    const REMOVE = 'remove';

    /**
     * @var UserStorageInterface
     */
    private $userStorage;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * TrustedDeviceVoter constructor.
     *
     * @param UserStorageInterface $userStorage
     * @param ObjectManager        $objectManager
     */
    public function __construct(UserStorageInterface $userStorage, ObjectManager $objectManager)
    {
        $this->userStorage   = $userStorage;
        $this->objectManager = $objectManager;
    }

    /**
     * @inheritDoc
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::REMOVE])) {
            return false;
        }

        if (!$subject instanceof RememberMeTokenInterface) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            // the user must be logged in; if not, deny access
            return false;
        }

        /** @var FungioUserInterface $fungioUser */
        $fungioUser = $this->objectManager->merge($this->userStorage->getUser());

        return $this->canRemove($fungioUser, $subject);
    }

    /**
     * @param FungioUserInterface      $user
     * @param RememberMeTokenInterface $token
     *
     * @return bool
     */
    private function canRemove(FungioUserInterface $user, RememberMeTokenInterface $token)
    {
        return $user === $token->getUser();
    }
}