<?php

namespace Fungio\TwoFactorBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Fungio\TwoFactorBundle\Util\BrowserParser;

/**
 * Get browser information from request.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\EventListener
 */
class BrowserListener
{
    /**
     * @var BrowserParser
     */
    private $browserParser;

    /**
     * @param BrowserParser $browserParser
     */
    public function __construct(BrowserParser $browserParser)
    {
        $this->browserParser = $browserParser;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $this->browserParser->analyse($request->headers->get('user-agent'));
        $this->browserParser->setIp($request->getClientIp());
    }
}