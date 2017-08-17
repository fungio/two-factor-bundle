<?php

namespace TwoFAS\TwoFactorBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use TwoFAS\TwoFactorBundle\Event\CodeCheckEvent;
use TwoFAS\TwoFactorBundle\Event\TwoFASEvents;
use TwoFAS\TwoFactorBundle\Util\AuthenticationManager;

/**
 * Listen for code accepted event is fired (on authentication success)
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\EventListener
 */
class AuthenticationSubscriber implements EventSubscriberInterface
{
    /**
     * @var AuthenticationManager
     */
    private $authenticationManager;

    /**
     * AuthenticationListener constructor.
     *
     * @param AuthenticationManager $authenticationManager
     */
    public function __construct(AuthenticationManager $authenticationManager)
    {
        $this->authenticationManager = $authenticationManager;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            TwoFASEvents::CODE_ACCEPTED              => 'onAuthenticationSuccess',
            TwoFASEvents::CODE_REJECTED_CANNOT_RETRY => 'onAuthenticationFailure'
        ];
    }

    /**
     * @param CodeCheckEvent $event
     */
    public function onAuthenticationSuccess(CodeCheckEvent $event)
    {
        $code = $event->getCode();

        $this->authenticationManager->closeAuthentications($code->authentications());
    }

    /**
     * @param CodeCheckEvent $event
     */
    public function onAuthenticationFailure(CodeCheckEvent $event)
    {
        $code = $event->getCode();

        if (!$code->canRetry()) {
            $this->cannotRetry($code->authentications());
        }
    }

    /**
     * @param array $authenticationIds
     */
    private function cannotRetry(array $authenticationIds)
    {
        $this->authenticationManager->blockAuthentications($authenticationIds);
    }
}