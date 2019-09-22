<?php

namespace Fungio\TwoFactorBundle\Storage;

use Fungio\Api\IntegrationUser;
use Fungio\TwoFactorBundle\Model\Entity\UserInterface;

/**
 * Contract for user storages.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Storage
 */
interface UserStorageInterface
{
    /**
     * @return UserInterface|null
     */
    public function getUser();

    /**
     * @return UserInterface
     */
    public function createUser();

    /**
     * @param UserInterface $user
     *
     * @return UserInterface
     */
    public function updateUser(UserInterface $user);

    /**
     * @param UserInterface $user
     *
     * @return IntegrationUser
     */
    public function storeIntegrationUser(UserInterface $user);

    /**
     * @param IntegrationUser $user
     *
     * @return IntegrationUser
     */
    public function updateIntegrationUser(IntegrationUser $user);
}
