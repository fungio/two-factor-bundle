<?php

namespace TwoFAS\TwoFactorBundle\Tests\EventListener;

use Symfony\Bundle\FrameworkBundle\EventListener\TestSessionListener as BaseTestSessionListener;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * This class fix a bug with session in functional tests:
 *
 * @link https://github.com/symfony/symfony/issues/13450
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Tests\EventListener
 */
class TestSessionListener extends BaseTestSessionListener
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        // bootstrap the session
        $session = $this->getSession();
        if (!$session) {
            return;
        }

        $cookies = $event->getRequest()->cookies;

        if ($cookies->has($session->getName())) {
            if ($session->getId() != $cookies->get($session->getName())) {
                $session->setId($cookies->get($session->getName()));
            }
        }
    }
}