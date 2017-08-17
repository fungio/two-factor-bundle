<?php

namespace TwoFAS\TwoFactorBundle\Model\Entity;

/**
 * Model class for TwoFAS Remember Me functionality.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Model\Entity
 */
class RememberMeToken implements RememberMeTokenInterface
{
    /**
     * @var string
     */
    protected $series;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * @var string
     */
    protected $browser;

    /**
     * @var string
     */
    protected $ip;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $lastUsedAt;

    /**
     * @inheritDoc
     */
    public function getSeries()
    {
        return $this->series;
    }

    /**
     * @inheritDoc
     */
    public function setSeries($series)
    {
        $this->series = $series;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @inheritDoc
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @inheritDoc
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getBrowser()
    {
        return $this->browser;
    }

    /**
     * @inheritDoc
     */
    public function setBrowser($browser)
    {
        $this->browser = $browser;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @inheritDoc
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getLastUsedAt()
    {
        return $this->lastUsedAt;
    }

    /**
     * @inheritDoc
     */
    public function setLastUsedAt(\DateTime $lastLastUsedAt)
    {
        $this->lastUsedAt = $lastLastUsedAt;
        return $this;
    }
}