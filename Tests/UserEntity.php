<?php

namespace TwoFAS\TwoFactorBundle\Tests;

use TwoFAS\TwoFactorBundle\Model\Entity\User;

/**
 * User entity for test only.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Tests
 */
class UserEntity extends User
{
    /**
     * @param $id
     *
     * @return UserEntity
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }
}