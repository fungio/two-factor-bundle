<?php

namespace Fungio\TwoFactorBundle\Model\Entity;

use DateTime;

/**
 * Contract for Authentication Model class.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Model
 */
interface AuthenticationInterface
{
    /**
     * @param string $id
     *
     * @return AuthenticationInterface
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getId();

    /**
     * @param UserInterface $user
     *
     * @return AuthenticationInterface
     */
    public function setUser(UserInterface $user);

    /**
     * @return UserInterface
     */
    public function getUser();

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $type
     *
     * @return AuthenticationInterface
     */
    public function setType($type);

    /**
     * @param bool $verified
     *
     * @return AuthenticationInterface
     */
    public function setVerified($verified);

    /**
     * @return bool
     */
    public function isVerified();

    /**
     * @param bool $blocked
     *
     * @return AuthenticationInterface
     */
    public function setBlocked($blocked);

    /**
     * @return bool
     */
    public function isBlocked();

    /**
     * @param DateTime $date
     *
     * @return AuthenticationInterface
     */
    public function setCreatedAt(DateTime $date);

    /**
     * @return DateTime
     */
    public function getCreatedAt();

    /**
     * @param DateTime $date
     *
     * @return AuthenticationInterface
     */
    public function setValidTo(DateTime $date);

    /**
     * @return DateTime
     */
    public function getValidTo();
}