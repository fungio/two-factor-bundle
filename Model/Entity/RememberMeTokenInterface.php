<?php

namespace TwoFAS\TwoFactorBundle\Model\Entity;

/**
 * Contract for RememberMeToken Model class.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Model
 */
interface RememberMeTokenInterface
{
    /**
     * @param string $series
     *
     * @return RememberMeTokenInterface
     */
    public function setSeries($series);

    /**
     * @return string
     */
    public function getSeries();

    /**
     * @param string $value
     *
     * @return RememberMeTokenInterface
     */
    public function setValue($value);

    /**
     * @return string
     */
    public function getValue();

    /**
     * @param string $class
     *
     * @return RememberMeTokenInterface
     */
    public function setClass($class);

    /**
     * @return string
     */
    public function getClass();

    /**
     * @param UserInterface $user
     *
     * @return RememberMeTokenInterface
     */
    public function setUser(UserInterface $user);

    /**
     * @return UserInterface
     */
    public function getUser();

    /**
     * @param string $browser
     *
     * @return RememberMeTokenInterface
     */
    public function setBrowser($browser);

    /**
     * @return string
     */
    public function getBrowser();

    /**
     * @param string $ip
     *
     * @return RememberMeTokenInterface
     */
    public function setIp($ip);

    /**
     * @return string
     */
    public function getIp();

    /**
     * @param \DateTime $createdAt
     *
     * @return RememberMeTokenInterface
     */
    public function setCreatedAt(\DateTime $createdAt);

    /**
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * @param \DateTime $lastUsedAt
     *
     * @return RememberMeTokenInterface
     */
    public function setLastUsedAt(\DateTime $lastUsedAt);

    /**
     * @return \DateTime
     */
    public function getLastUsedAt();
}