<?php

namespace Fungio\TwoFactorBundle\Tests\EventListener;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Translation\TranslatorInterface;
use Fungio\TwoFactorBundle\Event\FungioEvents;
use Fungio\TwoFactorBundle\EventListener\FlashSubscriber;

class FlashSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $translator;

    /**
     * @var FlashSubscriber
     */
    private $subscriber;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function setUp()
    {
        parent::setUp();

        $this->session    = new Session(new MockArraySessionStorage());
        $this->translator = $this->getMockBuilder(TranslatorInterface::class)->getMockForAbstractClass();
        $this->subscriber = new FlashSubscriber($this->session, $this->translator);
        $this->dispatcher = new EventDispatcher();
        $this->dispatcher->addSubscriber($this->subscriber);
    }

    public function testFlashSuccess()
    {
        $this->translator->method('trans')->willReturn('configure.totp.success');
        $this->dispatcher->dispatch(FungioEvents::INTEGRATION_USER_CONFIGURATION_COMPLETE_TOTP, new Event());

        $this->assertTrue($this->session->getFlashBag()->has('success'));
        $this->assertContains('configure.totp.success', $this->session->getFlashBag()->get('success'));
    }

    public function testFlashWarning()
    {
        $this->translator->method('trans')->willReturn('configure.totp.info');
        $this->dispatcher->dispatch(FungioEvents::CODE_REJECTED_CAN_RETRY, new Event());

        $this->assertTrue($this->session->getFlashBag()->has('info'));
        $this->assertContains('configure.totp.info', $this->session->getFlashBag()->get('info'));
    }

    public function testFlashInfo()
    {
        $this->translator->method('trans')->willReturn('configure.totp.warning');
        $this->dispatcher->dispatch(FungioEvents::CODE_REJECTED_CANNOT_RETRY, new Event());

        $this->assertTrue($this->session->getFlashBag()->has('warning'));
        $this->assertContains('configure.totp.warning', $this->session->getFlashBag()->get('warning'));
    }
}
