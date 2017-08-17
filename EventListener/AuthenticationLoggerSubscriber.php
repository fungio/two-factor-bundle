<?php

namespace TwoFAS\TwoFactorBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use TwoFAS\TwoFactorBundle\Event\CodeCheckEvent;
use TwoFAS\TwoFactorBundle\Event\TwoFASEvents;
use TwoFAS\TwoFactorBundle\Storage\TokenStorage;

/**
 * Listen for many events and log them.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\EventListener
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
            TwoFASEvents::CODE_ACCEPTED              => 'logAuthentication',
            TwoFASEvents::CODE_REJECTED_CAN_RETRY    => 'logAuthentication',
            TwoFASEvents::CODE_REJECTED_CANNOT_RETRY => 'logAuthentication'
        ];
    }

    /**
     * @param CodeCheckEvent $event
     */
    public function logAuthentication(CodeCheckEvent $event)
    {
        $message = sprintf(
            'TwoFAS authentication: %s (%s) from user: %s',
            ($event->getCode()->accepted() ? 'accepted' : 'rejected'),
            ($event->getCode()->canRetry() ? 'can retry' : 'cannot retry'),
            $this->tokenStorage->getToken()->getUsername()
        );

        if (!is_null($this->logger)) {
            $this->logger->info($message);
        }
    }
}