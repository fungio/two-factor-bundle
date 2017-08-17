<?php

namespace TwoFAS\TwoFactorBundle\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Contract for User Model class.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Model
 */
interface UserInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @param string $username
     *
     * @return UserInterface
     */
    public function setUsername($username);

    /**
     * @return string
     */
    public function getUsername();

    /**
     * @return array
     */
    public function getChannels();

    /**
     * @param array $channels
     *
     * @return UserInterface
     */
    public function setChannels(array $channels);

    /**
     * @return bool
     */
    public function isAnyChannelEnabled();

    /**
     * @param string $name
     *
     * @return bool
     */
    public function isChannelEnabled($name);

    /**
     * @param string $name
     *
     * @return UserInterface
     */
    public function enableChannel($name);

    /**
     * @param string $name
     *
     * @return UserInterface
     */
    public function disableChannel($name);

    /**
     * @param AuthenticationInterface $authentication
     *
     * @return UserInterface
     */
    public function addAuthentication(AuthenticationInterface $authentication);

    /**
     * @return ArrayCollection
     */
    public function getAuthentications();

    /**
     * @param RememberMeTokenInterface $token
     *
     * @return UserInterface
     */
    public function addToken(RememberMeTokenInterface $token);

    /**
     * @return ArrayCollection
     */
    public function getTokens();
}