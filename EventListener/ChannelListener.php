<?php

namespace Fungio\TwoFactorBundle\EventListener;

use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Fungio\TwoFactorBundle\Event\ChannelStatusChangedEvent;
use Fungio\TwoFactorBundle\Security\Token\TwoFactorToken;
use Fungio\TwoFactorBundle\Storage\TokenStorage;

/**
 * Listen for channel status changes.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\EventListener
 */
class ChannelListener
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

    /**
     * @param ChannelStatusChangedEvent $event
     */
    public function onChannelEnabled(ChannelStatusChangedEvent $event)
    {
        if (!$event->getUser()->isChannelEnabled($event->getChannel())) {
            throw new LogicException('Call channel enabled event on disabled channel.');
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