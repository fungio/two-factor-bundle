<?php

namespace Fungio\TwoFactorBundle\EventListener;

use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Fungio\TwoFactorBundle\Event\CodeCheckEvent;
use Fungio\TwoFactorBundle\Security\Token\TwoFactorToken;
use Fungio\TwoFactorBundle\Storage\TokenStorage;

/**
 * Change current token to TwoFactorToken after second factor authentication success.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\EventListener
 */
class TokenListener
{
    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * TokenListener constructor.
     *
     * @param TokenStorage $tokenStorage
     */
    public function __construct(TokenStorage $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function onAuthenticationSuccess(CodeCheckEvent $event)
    {
        if (!$event->getCode()->accepted()) {
            throw new LogicException('Call success code event on not accepted code.');
        }

        /** @var UsernamePasswordToken|RememberMeToken $currentToken */
        $currentToken = $this->tokenStorage->getToken();

        if (!$this->tokenStorage->isValid($currentToken)) {
            throw new LogicException('Can\'t login with 2FAS with current token.');
        }

        $fungioToken = new TwoFactorToken(
            $currentToken->getUser(),
            $currentToken->getCredentials(),
            $currentToken->getProviderKey(),
            $currentToken->getRoles()
        );

        $this->tokenStorage->setToken($fungioToken);
    }
}