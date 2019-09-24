<?php

namespace Fungio\TwoFactorBundle\Tests;

use Fungio\TwoFactorBundle\Model\Entity\User;

/**
 * User entity for test only.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Tests
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