<?php

namespace Fungio\TwoFactorBundle\Tests\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Fungio\Api\Code\AcceptedCode;
use Fungio\Api\Code\RejectedCodeCannotRetry;
use Fungio\Api\Code\RejectedCodeCanRetry;
use Fungio\TwoFactorBundle\Event\CodeCheckEvent;
use Fungio\TwoFactorBundle\Event\FungioEvents;
use Fungio\TwoFactorBundle\EventListener\AuthenticationLoggerSubscriber;
use Fungio\TwoFactorBundle\Storage\TokenStorage;
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
            ->with($this->equalTo('Fungio authentication: accepted (cannot retry) from user: admin'));

        $event = new CodeCheckEvent(new AcceptedCode([]));

        $this->dispatcher->dispatch(FungioEvents::CODE_ACCEPTED, $event);
    }

    public function testRejectedCodeCanRetryLog()
    {
        $this->logger->expects($this->once())
            ->method('info')
            ->with($this->equalTo('Fungio authentication: rejected (can retry) from user: admin'));

        $event = new CodeCheckEvent(new RejectedCodeCanRetry([]));

        $this->dispatcher->dispatch(FungioEvents::CODE_REJECTED_CAN_RETRY, $event);
    }

    public function testRejectedCodeCannotRetryLog()
    {
        $this->logger->expects($this->once())
            ->method('info')
            ->with($this->equalTo('Fungio authentication: rejected (cannot retry) from user: admin'));

        $event = new CodeCheckEvent(new RejectedCodeCannotRetry([]));

        $this->dispatcher->dispatch(FungioEvents::CODE_REJECTED_CANNOT_RETRY, $event);
    }
}
