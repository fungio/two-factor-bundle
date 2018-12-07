<?php

namespace TwoFAS\TwoFactorBundle\Storage;

use TwoFAS\Api\IntegrationUser;
use TwoFAS\TwoFactorBundle\Model\Entity\UserInterface;

/**
 * Contract for user storages.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Storage
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
