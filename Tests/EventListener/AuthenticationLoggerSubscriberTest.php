<?php

namespace TwoFAS\TwoFactorBundle\Tests\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use TwoFAS\Api\Code\AcceptedCode;
use TwoFAS\Api\Code\RejectedCodeCannotRetry;
use TwoFAS\Api\Code\RejectedCodeCanRetry;
use TwoFAS\TwoFactorBundle\Event\CodeCheckEvent;
use TwoFAS\TwoFactorBundle\Event\TwoFASEvents;
use TwoFAS\TwoFactorBundle\EventListener\AuthenticationLoggerSubscriber;
use TwoFAS\TwoFactorBundle\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage as BaseTokenStorage;

class AuthenticationLoggerSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var AuthenticationLoggerSubscriber
     */
    private $subscriber;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function setUp()
    {
        parent::setUp();

        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $tokenStorage = new TokenStorage(new BaseTokenStorage());
        $token        = $this->getMockForAbstractClass(TokenInterface::class);
        $token->method('getUsername')->willReturn('admin');
        $tokenStorage->setToken($token);
        $this->subscriber = new AuthenticationLoggerSubscriber($tokenStorage, $this->logger);
        $this->dispatcher = new EventDispatcher();
        $this->dispatcher->addSubscriber($this->subscriber);
    }

    public function testAcceptedCodeLog()
    {
        $this->logger->expects($this->once())
            ->method('info')
            ->with($this->equalTo('TwoFAS authentication: accepted (cannot retry) from user: admin'));

        $event = new CodeCheckEvent(new AcceptedCode([]));

        $this->dispatcher->dispatch(TwoFASEvents::CODE_ACCEPTED, $event);
    }

    public function testRejectedCodeCanRetryLog()
    {
        $this->logger->expects($this->once())
            ->method('info')
            ->with($this->equalTo('TwoFAS authentication: rejected (can retry) from user: admin'));

        $event = new CodeCheckEvent(new RejectedCodeCanRetry([]));

        $this->dispatcher->dispatch(TwoFASEvents::CODE_REJECTED_CAN_RETRY, $event);
    }

    public function testRejectedCodeCannotRetryLog()
    {
        $this->logger->expects($this->once())
            ->method('info')
            ->with($this->equalTo('TwoFAS authentication: rejected (cannot retry) from user: admin'));

        $event = new CodeCheckEvent(new RejectedCodeCannotRetry([]));

        $this->dispatcher->dispatch(TwoFASEvents::CODE_REJECTED_CANNOT_RETRY, $event);
    }
}
