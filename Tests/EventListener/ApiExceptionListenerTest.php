<?php

namespace Fungio\TwoFactorBundle\Tests\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\TranslatorInterface;
use TwoFAS\Api\Exception\AuthorizationException;
use TwoFAS\Api\Exception\ValidationException;
use Fungio\TwoFactorBundle\EventListener\ApiExceptionListener;

class ApiExceptionListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ApiExceptionListener
     */
    private $listener;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $session;

    /**
     * @var TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $translator;

    /**
     * @var Request
     */
    private $request;

    public function setUp()
    {
        parent::setUp();

        $this->session    = $this->getMockBuilder(Session::class)->disableOriginalConstructor()->getMock();
        $this->translator = $this->getMockBuilder(Translator::class)->disableOriginalConstructor()->getMock();
        $this->listener   = new ApiExceptionListener($this->session, $this->translator);
        $this->request    = new Request([], [], [], [], [], ['REQUEST_URI' => '/foo/bar']);
    }

    public function testValidationException()
    {
        $this->session->method('getFlashBag')->willReturn(new FlashBag());
        $this->translator->expects($this->once())->method('trans')->willReturn('authentication.code.required');
        $exception = new ValidationException(['code' => ['authentication.code.required']]);
        $event     = $this->getEvent();
        $event->setException($exception);

        $this->listener->onKernelException($event);

        /** @var RedirectResponse $response */
        $response = $event->getResponse();
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/foo/bar', $response->getTargetUrl());
        $this->assertContains('authentication.code.required', $this->session->getFlashBag()->get('danger'));
    }

    public function testAuthorizationException()
    {
        $exception = new AuthorizationException('foo');
        $event     = $this->getEvent();
        $event->setException($exception);

        $this->listener->onKernelException($event);

        $this->assertInstanceOf(AccessDeniedHttpException::class, $event->getException());
        $this->assertEquals('foo', $event->getException()->getMessage());
    }

    /**
     * @return GetResponseForExceptionEvent
     */
    private function getEvent()
    {
        /** @var HttpKernelInterface $kernel */
        $kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMockForAbstractClass();

        return new GetResponseForExceptionEvent($kernel, $this->request, HttpKernelInterface::MASTER_REQUEST, new \Exception());
    }
}
