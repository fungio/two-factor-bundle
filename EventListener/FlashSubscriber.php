<?php

namespace Fungio\TwoFactorBundle\EventListener;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\TranslatorInterface;
use Fungio\TwoFactorBundle\Event\FungioEvents;

/**
 * Listen for many events and add flash messages to session.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\EventListener
 */
class FlashSubscriber implements EventSubscriberInterface
{
    /**
     * @var array
     */
    private $messages = [
        FungioEvents::INTEGRATION_USER_CONFIGURATION_COMPLETE_TOTP => 'configure.totp.success',
        FungioEvents::CODE_REJECTED_CAN_RETRY                      => 'authentication.code.can_retry',
        FungioEvents::CODE_REJECTED_CANNOT_RETRY                   => 'authentication.code.cannot_retry'
    ];

    /**
     * @var Session
     */
    private $session;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param Session             $session
     * @param TranslatorInterface $translator
     */
    public function __construct(Session $session, TranslatorInterface $translator)
    {
        $this->session    = $session;
        $this->translator = $translator;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            FungioEvents::INTEGRATION_USER_CONFIGURATION_COMPLETE_TOTP => 'success',
            FungioEvents::CODE_REJECTED_CAN_RETRY                      => 'info',
            FungioEvents::CODE_REJECTED_CANNOT_RETRY                   => 'warning'
        ];
    }

    /**
     * @param Event  $event
     * @param string $eventName
     */
    public function success(Event $event, $eventName)
    {
        $this->addFlash('success', $eventName);
    }

    /**
     * @param Event  $event
     * @param string $eventName
     */
    public function info(Event $event, $eventName)
    {
        $this->addFlash('info', $eventName);
    }

    /**
     * @param Event  $event
     * @param string $eventName
     */
    public function warning(Event $event, $eventName)
    {
        $this->addFlash('warning', $eventName);
    }

    /**
     * @param string $type
     * @param string $eventName
     */
    private function addFlash($type, $eventName)
    {
        $this->session->getFlashBag()->add($type, $this->translator->trans($this->messages[$eventName]));
    }
}