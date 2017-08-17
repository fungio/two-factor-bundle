<?php

namespace TwoFAS\TwoFactorBundle\Tests\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use TwoFAS\TwoFactorBundle\EventListener\BrowserListener;
use TwoFAS\TwoFactorBundle\Util\BrowserParser;

class BrowserListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BrowserParser
     */
    private $browserParser;

    /**
     * @var BrowserListener
     */
    private $listener;

    public function setUp()
    {
        parent::setUp();

        $this->browserParser = new BrowserParser();
        $this->listener      = new BrowserListener($this->browserParser);
    }

    public function testOnKernelRequest()
    {
        $event = $this->getEvent();

        $this->listener->onKernelRequest($event);

        $this->assertEquals('Chrome 55 on OS X El Capitan 10.11', $this->browserParser->toString());
        $this->assertEquals('192.168.0.1', $this->browserParser->getIp());
    }

    private function getEvent()
    {
        $request = new Request([], [], [], [], [], [
            'REMOTE_ADDR'     => '192.168.0.1',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.95 Safari/537.36'
        ]);

        return new GetResponseEvent($this->getMockForAbstractClass(HttpKernelInterface::class), $request, HttpKernelInterface::MASTER_REQUEST);
    }
}
