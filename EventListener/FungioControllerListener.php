<?php

namespace Fungio\TwoFactorBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Fungio\TwoFactorBundle\Controller\Controller as FungioController;
use Fungio\TwoFactorBundle\Storage\TokenStorage;
use Fungio\TwoFactorBundle\Util\ConfigurationChecker;

/**
 * Listen for call Fungio Controller and check that user is logged.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\EventListener
 */
class FungioControllerListener
{
    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var ConfigurationChecker
     */
    private $configurationChecker;

    /**
     * @param TokenStorage $tokenStorage
     * @param ConfigurationChecker  $configurationChecker
     */
    public function __construct(TokenStorage $tokenStorage, ConfigurationChecker $configurationChecker)
    {
        $this->tokenStorage         = $tokenStorage;
        $this->configurationChecker = $configurationChecker;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        /*
         * $controller passed can be either a class or a Closure.
         * This is not usual in Symfony but it may happen.
         * If it is a class, it comes in array format
         */
        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof FungioController) {
            $token = $this->tokenStorage->getToken();

            if (null === $token || !is_object($token->getUser())) {
                throw new AccessDeniedHttpException('This user does not have access to this section.');
            }

            if (!$this->configurationChecker->isFungioConfigured()) {
                throw new AccessDeniedHttpException('You have to create 2FAS account to have access to this section.');
            }
        }
    }
}