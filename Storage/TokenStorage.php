<?php

namespace TwoFAS\TwoFactorBundle\Storage;

use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Wrapper for TokenStorage - add validation method.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Storage
 */
class TokenStorage implements TokenStorageInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $storage;

    /**
     * TokenStorage constructor.
     *
     * @param TokenStorageInterface $storage
     */
    public function __construct(TokenStorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @inheritDoc
     */
    public function getToken()
    {
        return $this->storage->getToken();
    }

    /**
     * @inheritDoc
     */
    public function setToken(TokenInterface $token = null)
    {
        $this->storage->setToken($token);
    }

    /**
     * @param TokenInterface|null $token
     *
     * @return bool
     */
    public function isValid(TokenInterface $token = null)
    {
        return ($token instanceof UsernamePasswordToken || $token instanceof RememberMeToken);
    }
}