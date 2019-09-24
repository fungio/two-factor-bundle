<?php

namespace Fungio\TwoFactorBundle\Util;

use Symfony\Component\Security\Core\User\UserInterface;
use Fungio\TwoFactorBundle\Model\Entity\UserInterface as FungioUserInterface;
use Fungio\TwoFactorBundle\Model\Persister\ObjectPersisterInterface;

/**
 * Class for manages Fungio Users.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Util
 */
class UserManager
{
    /**
     * @var ObjectPersisterInterface
     */
    private $userPersister;

    /**
     * UserManager constructor.
     *
     * @param ObjectPersisterInterface $userPersister
     */
    public function __construct(ObjectPersisterInterface $userPersister)
    {
        $this->userPersister = $userPersister;
    }

    /**
     * @param int $id
     *
     * @return FungioUserInterface|null
     */
    public function findById($id)
    {
        /** @var FungioUserInterface|null $user */
        $user = $this->userPersister->getRepository()->find($id);

        return $user;
    }

    /**
     * @param string $username
     *
     * @return FungioUserInterface|null
     */
    public function findByUserName($username)
    {
        /** @var FungioUserInterface|null $user */
        $user = $this->userPersister->getRepository()->findOneBy(['username' => $username]);

        return $user;
    }

    /**
     * @param UserInterface $loggedUser
     *
     * @return FungioUserInterface
     */
    public function createUser(UserInterface $loggedUser)
    {
        /** @var FungioUserInterface $user */
        $user = $this->userPersister->getEntity();
        $user->setUsername($loggedUser->getUsername());
        $this->updateUser($user);

        return $user;
    }

    /**
     * @param FungioUserInterface $user
     */
    public function updateUser(FungioUserInterface $user)
    {
        $this->userPersister->saveEntity($user);
    }
}