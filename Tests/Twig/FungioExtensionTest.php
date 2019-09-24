<?php

namespace Fungio\TwoFactorBundle\Tests\Twig;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Fungio\TwoFactorBundle\Twig\FungioExtension;
use Fungio\TwoFactorBundle\Util\ConfigurationChecker;

class FungioExtensionTest extends KernelTestCase
{
    /**
     * @var ConfigurationChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configurationChecker;

    /**
     * @var AuthorizationCheckerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorizationChecker;

    /**
     * @var FungioExtension
     */
    private $extension;

    public function setUp()
    {
        $kernel                     = self::createKernel();
        $this->configurationChecker = $this->getMockBuilder(ConfigurationChecker::class)->disableOriginalConstructor()->getMock();
        $this->authorizationChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)->disableOriginalConstructor()->getMockForAbstractClass();
        $this->extension            = new FungioExtension($this->configurationChecker, $this->authorizationChecker, $kernel->getRootDir());
    }

    public function testCanRenderIfRememberedAndHasProvidedRole()
    {
        $this->authorizationChecker->expects($this->at(0))->method('isGranted')->with($this->equalTo('IS_AUTHENTICATED_TWO_FACTOR_REMEMBERED'))->willReturn(true);
        $this->authorizationChecker->expects($this->at(1))->method('isGranted')->with($this->equalTo('ROLE_ADMIN'))->willReturn(true);

        $this->assertTrue($this->extension->canRenderFungio('ROLE_ADMIN'));
    }

    public function testCanRenderIfNotConfigured()
    {
        $this->authorizationChecker->expects($this->at(0))->method('isGranted')->with($this->equalTo('IS_AUTHENTICATED_TWO_FACTOR_REMEMBERED'))->willReturn(false);
        $this->authorizationChecker->expects($this->at(1))->method('isGranted')->with($this->equalTo('ROLE_USER'))->willReturn(true);
        $this->configurationChecker->method('isSecondFactorEnabledForUser')->willReturn(false);

        $this->assertTrue($this->extension->canRenderFungio('ROLE_USER'));
    }

    public function testCannotRenderIfRememberedAndHasNotProvidedRole()
    {
        $this->authorizationChecker->expects($this->at(0))->method('isGranted')->with($this->equalTo('IS_AUTHENTICATED_TWO_FACTOR_REMEMBERED'))->willReturn(true);
        $this->authorizationChecker->expects($this->at(1))->method('isGranted')->with($this->equalTo('ROLE_ADMIN'))->willReturn(false);

        $this->assertFalse($this->extension->canRenderFungio('ROLE_ADMIN'));
    }

    public function testAssetWithoutTime()
    {
        $this->setExpectedException('InvalidArgumentException', 'File "not_existent_path.js" does not exists.');

        $path = 'not_existent_path.js';
        $this->extension->fileMTime($path);
    }

    public function testAssetWithTime()
    {
        $path       = '/bundles/fungiotwofactor/js/main.js';
        $actualPath = $this->extension->fileMTime($path);

        $this->assertNotEquals($path, $actualPath);
        $this->assertRegExp('/\?\d+/', $actualPath);
    }
}
