<?php

namespace Fungio\TwoFactorBundle\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Fungio\Api\IntegrationUser;
use Fungio\Api\Methods;

/**
 * Model class for Fungio User.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Model
 */
class User implements UserInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var IntegrationUser
     */
    protected $integrationUser;

    /**
     * @var array
     */
    protected $channels = [];

    /**
     * @var ArrayCollection
     */
    protected $authentications;

    /**
     * @var ArrayCollection
     */
    protected $tokens;

    public function __construct()
    {
        $this->authentications = new ArrayCollection();
        $this->tokens          = new ArrayCollection();

        array_map(function ($method) {
            $this->channels[$method] = false;
        }, Methods::getAllowedMethods());
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @inheritdoc
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getIntegrationUser()
    {
        return $this->integrationUser;
    }

    /**
     * @inheritdoc
     */
    public function setIntegrationUser(IntegrationUser $integrationUser)
    {
        $this->integrationUser = $integrationUser;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * @inheritDoc
     */
    public function setChannels(array $channels)
    {
        $this->channels = $channels;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isAnyChannelEnabled()
    {
        return in_array(true, $this->channels, true);
    }

    /**
     * @inheritdoc
     */
    public function isChannelEnabled($name)
    {
        if (!array_key_exists($name, $this->channels)) {
            return false;
        }

        return $this->channels[$name];
    }

    /**
     * @inheritdoc
     */
    public function enableChannel($name)
    {
        $this->channels[$name] = true;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function disableChannel($name)
    {
        $this->channels[$name] = false;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addAuthentication(AuthenticationInterface $authentication)
    {
        $this->authentications->add($authentication);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAuthentications()
    {
        return $this->authentications;
    }

    /**
     * @inheritDoc
     */
    public function addToken(RememberMeTokenInterface $token)
    {
        $this->tokens->add($token);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTokens()
    {
        return $this->tokens;
    }
}
