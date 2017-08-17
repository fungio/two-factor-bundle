<?php

namespace TwoFAS\TwoFactorBundle\Model\Entity;

use DateTime;

/**
 * Model class for TwoFAS Authentication.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Model
 */
class Authentication implements AuthenticationInterface
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var bool
     */
    protected $verified = false;

    /**
     * @var bool
     */
    protected $blocked = false;

    /**
     * @var DateTime
     */
    protected $createdAt;

    /**
     * @var DateTime
     */
    protected $validTo;

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @inheritdoc
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isVerified()
    {
        return $this->verified;
    }

    /**
     * @inheritdoc
     */
    public function setVerified($verified)
    {
        $this->verified = (bool) $verified;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isBlocked()
    {
        return $this->blocked;
    }

    /**
     * @inheritDoc
     */
    public function setBlocked($blocked)
    {
        $this->blocked = (bool) $blocked;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getValidTo()
    {
        return $this->validTo;
    }

    /**
     * @inheritdoc
     */
    public function setValidTo(DateTime $validTo)
    {
        $this->validTo = $validTo;
        return $this;
    }
}