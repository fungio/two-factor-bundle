<?php

namespace TwoFAS\TwoFactorBundle\Tests\EventListener;


use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\RememberMe\RememberMeServicesInterface;
use TwoFAS\TwoFactorBundle\DependencyInjection\Factory\RememberMeServicesFactoryInterface;
use TwoFAS\TwoFactorBundle\EventListener\SecondFactorListener;
use TwoFAS\TwoFactorBundle\Storage\TokenStorage;
use TwoFAS\TwoFactorBundle\Util\ConfigurationChecker;

class SecondFactorListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TokenStorage|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tokenStorage;

    /**
     * @var AuthorizationCheckerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorizationChecker;

    /**
     * @var AuthenticationManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authenticationManager;

    /**
     * @var RememberMeServicesFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $rememberMeFactory;

    /**
     * @var RouterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $router;

    /**
     * @var SessionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $session;

    /**
     * @var ConfigurationChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configurationChecker;

    /**
     * @var array
     */
    private $firewalls;

    /**
     * @var SecondFactorListener
     */
    private $listener;

    /**
     * @var GetResponseEvent
     */
    private $event;

    public function setUp()
    {
        parent::setUp();

        $this->tokenStorage          = $this->getMockBuilder(TokenStorage::class)->disableOriginalConstructor()->getMock();
        $this->authorizationChecker  = $this->getMockForAbstractClass(AuthorizationCheckerInterface::class);
        $this->authenticationManager = $this->getMockForAbstractClass(AuthenticationManagerInterface::class);
        $this->rememberMeFactory     = $this->getMockForAbstractClass(RememberMeServicesFactoryInterface::class);
        $this->router                = $this->getMockForAbstractClass(RouterInterface::class);
        $this->session               = $this->getMockForAbstractClass(SessionInterface::class);
        $this->configurationChecker  = $this->getMockBuilder(ConfigurationChecker::class)->disableOriginalConstructor()->getMock();
        $this->firewalls             = ['main'];
        $this->listener              = new SecondFactorListener(
            $this->tokenStorage,
            $this->authorizationChecker,
            $this->authenticationManager,
            $this->rememberMeFactory,
            $this->router,
            $this->session,
            $this->configurationChecker,
            $this->firewalls
        );

        $this->event = $this->getEvent();
    }

    public function testSubRequest()
    {
        $this->tokenStorage->expects($this->never())->method('getToken');
        $this->event = new GetResponseEvent($this->getMockForAbstractClass(HttpKernelInterface::class), new Request(), HttpKernelInterface::SUB_REQUEST);
        $this->listener->onKernelRequest($this->event);
        $this->assertNull($this->event->getResponse());
    }

    public function testTokenIsNull()
    {
        $this->authorizationChecker->expects($this->never())->method('isGranted');
        $this->tokenStorage->method('getToken')->willReturn(null);
        $this->listener->onKernelRequest($this->event);
        $this->assertNull($this->event->getResponse());
    }

    public function testTokenIsNotValid()
    {
        $this->authorizationChecker->expects($this->never())->method('isGranted');
        $this->tokenStorage->method('getToken')->willReturn(new AnonymousToken('foo', 'admin'));
        $this->listener->onKernelRequest($this->event);
        $this->assertNull($this->event->getResponse());
    }

    public function testTwoFASNotSupportFirewall()
    {
        $this->authorizationChecker->expects($this->never())->method('isGranted');
        $token = new UsernamePasswordToken('admin', 'adminpass', 'fake_firewall');
        $this->tokenStorage->method('getToken')->willReturn($token);
        $this->listener->onKernelRequest($this->event);
        $this->assertNull($this->event->getResponse());
    }

    public function testGranted()
    {
        $this->passTokenValid();
        $this->rememberMeFactory->expects($this->never())->method('createInstance');
        $this->authorizationChecker->method('isGranted')->willReturn(true);
        $this->listener->onKernelRequest($this->event);
        $this->assertNull($this->event->getResponse());
    }

    public function testIsRemembered()
    {
        $this->passGranted();
        $this->configurationChecker->expects($this->never())->method('isTwoFASEnabled');
        $user = $this->getMockForAbstractClass(UserInterface::class);
        $user->method('getRoles')->willReturn([]);
        $token             = new RememberMeToken($user, 'main', 'foo');
        $rememberMeService = $this->getMockForAbstractClass(RememberMeServicesInterface::class);
        $rememberMeService->method('autoLogin')->willReturn($token);
        $this->rememberMeFactory->method('createInstance')->willReturn($rememberMeService);
        $this->authenticationManager->method('authenticate')->willReturn($token);

        $this->listener->onKernelRequest($this->event);
        $this->assertNull($this->event->getResponse());
    }

    public function testTwoFASEnabled()
    {
        $this->passRemembered();
        $this->configurationChecker->expects($this->never())->method('isSecondFactorEnabledForUser');
        $this->configurationChecker->method('isTwoFASEnabled')->willReturn(false);
        $this->listener->onKernelRequest($this->event);
        $this->assertNull($this->event->getResponse());
    }

    public function testSecondFactorEnabledForUser()
    {
        $this->passTwoFASEnabled();
        $this->router->expects($this->never())->method('generate');
        $this->configurationChecker->method('isSecondFactorEnabledForUser')->willReturn(false);

        $this->listener->onKernelRequest($this->event);
        $this->assertNull($this->event->getResponse());
    }

    public function testTwoFASCheckRoute()
    {
        $this->passUserConfigured();
        $this->router->method('generate')->willReturn('/2fas/check');

        $request     = new Request([], [], [], [], [], [
            'REQUEST_URI' => '/2fas/check'
        ]);
        $this->event = new GetResponseEvent($this->getMockForAbstractClass(HttpKernelInterface::class), $request, HttpKernelInterface::MASTER_REQUEST);

        $this->listener->onKernelRequest($this->event);
        $this->assertNull($this->event->getResponse());
    }

    public function testRedirect()
    {
        $this->passSecondFactorEnabledForUser();

        $event = $this->getEvent();
        $this->listener->onKernelRequest($event);

        /** @var RedirectResponse $response */
        $response = $event->getResponse();
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/2fas/check', $response->getTargetUrl());
    }

    /**
     * @return GetResponseEvent
     */
    private function getEvent()
    {
        return new GetResponseEvent($this->getMockForAbstractClass(HttpKernelInterface::class), new Request(), HttpKernelInterface::MASTER_REQUEST);
    }

    private function passTokenValid()
    {
        $token = new UsernamePasswordToken('admin', 'adminpass', 'main');
        $this->tokenStorage->method('getToken')->willReturn($token);
        $this->tokenStorage->method('isValid')->willReturn(true);
    }

    private function passGranted()
    {
        $this->passTokenValid();
        $this->authorizationChecker->method('isGranted')->willReturn(false);
    }

    private function passRemembered()
    {
        $this->passGranted();
        $rememberMeService = $this->getMockForAbstractClass(RememberMeServicesInterface::class);
        $rememberMeService->method('autoLogin')->willReturn(null);
        $this->rememberMeFactory->method('createInstance')->willReturn($rememberMeService);
    }

    private function passTwoFASEnabled()
    {
        $this->passRemembered();
        $this->configurationChecker->method('isTwoFASEnabled')->willReturn(true);
    }

    private function passUserConfigured()
    {
        $this->passTwoFASEnabled();
        $user = $this->getMockForAbstractClass(UserInterface::class);
        $user->method('getRoles')->willReturn([]);
        $this->tokenStorage->getToken()->setUser($user);
        $this->configurationChecker->method('isSecondFactorEnabledForUser')->willReturn(true);
    }

    private function passSecondFactorEnabledForUser()
    {
        $this->passUserConfigured();
        $this->router->method('generate')->willReturn('/2fas/check');
        $request     = new Request([], [], [], [], [], [
            'REQUEST_URI' => '/foo/bar'
        ]);
        $this->event = new GetResponseEvent($this->getMockForAbstractClass(HttpKernelInterface::class), $request, HttpKernelInterface::MASTER_REQUEST);
    }
}
