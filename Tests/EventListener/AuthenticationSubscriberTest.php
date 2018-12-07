<?php

namespace TwoFAS\TwoFactorBundle\Tests\EventListener;

use TwoFAS\Api\Code\AcceptedCode;
use TwoFAS\Api\Code\RejectedCodeCannotRetry;
use TwoFAS\TwoFactorBundle\Event\CodeCheckEvent;
use TwoFAS\TwoFactorBundle\EventListener\AuthenticationSubscriber;
use TwoFAS\TwoFactorBundle\Util\AuthenticationManager;

class AuthenticationSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AuthenticationManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authenticationManager;

    /**
     * @var AuthenticationSubscriber
     */
    private $subscriber;

    public function setUp()
    {
        parent::setUp();

        $this->authenticationManager = $this->getMockBuilder(AuthenticationManager::class)->disableOriginalConstructor()->getMock();
        $this->subscriber            = new AuthenticationSubscriber($this->authenticationManager);
    }

    public function testAuthenticationSuccess()
    {
        $this->authenticationManager->expects($this->once())->method('closeAuthentications');
        $code  = new AcceptedCode([]);
        $event = new CodeCheckEvent($code);

        $this->subscriber->onAuthenticationSuccess($event);
    }

    public function testAuthenticationFailureCannotRetry()
    {
        $this->authenticationManager->expects($this->once())->method('blockAuthentications');
        $code  = new RejectedCodeCannotRetry([]);
        $event = new CodeCheckEvent($code);

        $this->subscriber->onAuthenticationFailure($event);
    }
}
