<?php

namespace TwoFAS\TwoFactorBundle\Util;

use Symfony\Component\Security\Core\User\UserInterface;
use TwoFAS\TwoFactorBundle\Model\Entity\UserInterface as TwoFASUserInterface;
use TwoFAS\TwoFactorBundle\Model\Persister\ObjectPersisterInterface;

/**
 * Class for manages TwoFAS Users.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Util
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
     * @return TwoFASUserInterface|null
     */
    public function findById($id)
    {
        /** @var TwoFASUserInterface|null $user */
        $user = $this->userPersister->getRepository()->find($id);

        return $user;
    }

    /**
     * @param string $username
     *
     * @return TwoFASUserInterface|null
     */
    public function findByUserName($username)
    {
        /** @var TwoFASUserInterface|null $user */
        $user = $this->userPersister->getRepository()->findOneBy(['username' => $username]);

        return $user;
    }

    /**
     * @param UserInterface $loggedUser
     *
     * @return TwoFASUserInterface
     */
    public function createUser(UserInterface $loggedUser)
    {
        /** @var TwoFASUserInterface $user */
        $user = $this->userPersister->getEntity();
        $user->setUsername($loggedUser->getUsername());
        $this->updateUser($user);

        return $user;
    }

    /**
     * @param TwoFASUserInterface $user
     */
    public function updateUser(TwoFASUserInterface $user)
    {
        $this->userPersister->saveEntity($user);
    }
}