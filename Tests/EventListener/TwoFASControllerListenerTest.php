<?php

namespace TwoFAS\TwoFactorBundle\Tests\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use TwoFAS\TwoFactorBundle\Controller\DashboardController;
use TwoFAS\TwoFactorBundle\EventListener\TwoFASControllerListener;
use TwoFAS\TwoFactorBundle\Model\Entity\User;
use TwoFAS\TwoFactorBundle\Storage\TokenStorage;
use TwoFAS\TwoFactorBundle\Tests\DummyEntity;
use TwoFAS\TwoFactorBundle\Util\ConfigurationChecker;

class TwoFASControllerListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TokenStorage|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tokenStorage;

    /**
     * @var ConfigurationChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configurationChecker;

    /**
     * @var TwoFASControllerListener
     */
    private $listener;

    public function setUp()
    {
        parent::setUp();

        $this->tokenStorage         = $this->getMockBuilder(TokenStorage::class)->disableOriginalConstructor()->getMock();
        $this->configurationChecker = $this
            ->getMockBuilder(ConfigurationChecker::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new TwoFASControllerListener($this->tokenStorage, $this->configurationChecker);
    }

    public function testNotArrayOfControllers()
    {
        $this->tokenStorage->expects($this->never())->method('getToken');
        $event = new FilterControllerEvent($this->getKernel(), 'is_array', new Request(), HttpKernelInterface::MASTER_REQUEST);
        $this->listener->onKernelController($event);
    }

    public function testNoTwoFASController()
    {
        $this->tokenStorage->expects($this->never())->method('getToken');
        $controller = new DummyEntity();
        $event      = new FilterControllerEvent($this->getKernel(), [$controller, 'getId'], new Request(), HttpKernelInterface::MASTER_REQUEST);
        $this->listener->onKernelController($event);
    }

    public function testNotLoggedUserInTwoFASController()
    {
        $this->setExpectedException(AccessDeniedHttpException::class, 'This user does not have access to this section.');
        $this->tokenStorage->method('getToken')->willReturn(null);
        $controller = new DashboardController();
        $event      = new FilterControllerEvent($this->getKernel(), [$controller, 'indexAction'], new Request(), HttpKernelInterface::MASTER_REQUEST);
        $this->listener->onKernelController($event);
    }

    public function testTokenHasNotUser()
    {
        $this->setExpectedException(AccessDeniedHttpException::class, 'This user does not have access to this section.');
        $token = $this->getMockForAbstractClass(TokenInterface::class);
        $token->method('getUser')->willReturn(null);
        $this->tokenStorage->method('getToken')->willReturn($token);
        $controller = new DashboardController();
        $event      = new FilterControllerEvent($this->getKernel(), [$controller, 'indexAction'], new Request(), HttpKernelInterface::MASTER_REQUEST);
        $this->listener->onKernelController($event);
    }

    public function testTwoFASNotConfigured()
    {
        $this->setExpectedException(AccessDeniedHttpException::class, 'You have to create 2FAS account to have access to this section.');
        $token = $this->getMockForAbstractClass(TokenInterface::class);
        $token->method('getUser')->willReturn(new User());
        $this->tokenStorage->method('getToken')->willReturn($token);
        $this->configurationChecker->method('isTwoFASConfigured')->willReturn(false);
        $controller = new DashboardController();
        $event      = new FilterControllerEvent($this->getKernel(), [$controller, 'indexAction'], new Request(), HttpKernelInterface::MASTER_REQUEST);
        $this->listener->onKernelController($event);
    }

    /**
     * @return HttpKernelInterface
     */
    private function getKernel()
    {
        return $this->getMockForAbstractClass(HttpKernelInterface::class);
    }
}
