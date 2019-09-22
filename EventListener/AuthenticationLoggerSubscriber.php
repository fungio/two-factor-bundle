<?php

namespace Fungio\TwoFactorBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Fungio\TwoFactorBundle\Event\CodeCheckEvent;
use Fungio\TwoFactorBundle\Event\FungioEvents;
use Fungio\TwoFactorBundle\Storage\TokenStorage;

/**
 * Listen for many events and log them.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\EventListener
 */
class AuthenticationLoggerSubscriber implements EventSubscriberInterface
{
    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * @param TokenStorage         $tokenStorage
     * @param LoggerInterface|null $logger
     */
    public function __construct(TokenStorage $tokenStorage, LoggerInterface $logger = null)
    {
        $this->tokenStorage = $tokenStorage;
        $this->logger       = $logger;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            FungioEvents::CODE_ACCEPTED              => 'logAuthentication',
            FungioEvents::CODE_REJECTED_CAN_RETRY    => 'logAuthentication',
            FungioEvents::CODE_REJECTED_CANNOT_RETRY => 'logAuthentication'
        ];
    }

    /**
     * @param CodeCheckEvent $event
     */
    public function logAuthentication(CodeCheckEvent $event)
    {
        $message = sprintf(
            'Fungio authentication: %s (%s) from user: %s',
            ($event->getCode()->accepted() ? 'accepted' : 'rejected'),
            ($event->getCode()->canRetry() ? 'can retry' : 'cannot retry'),
            $this->tokenStorage->getToken()->getUsername()
        );

        if (!is_null($this->logger)) {
            $this->logger->info($message);
        }
    }
}