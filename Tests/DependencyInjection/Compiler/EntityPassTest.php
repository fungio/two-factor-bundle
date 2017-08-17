<?php

namespace TwoFAS\TwoFactorBundle\Tests\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use TwoFAS\TwoFactorBundle\DependencyInjection\Compiler\EntityPass;
use TwoFAS\TwoFactorBundle\Entity\Authentication;
use TwoFAS\TwoFactorBundle\Entity\Option;
use TwoFAS\TwoFactorBundle\Entity\RememberMeToken;
use TwoFAS\TwoFactorBundle\Entity\User;

class EntityPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityPass
     */
    private $pass;

    /**
     * @var ContainerBuilder
     */
    private $containerBuilder;

    public function setUp()
    {
        $this->containerBuilder = new ContainerBuilder();
        $this->pass             = new EntityPass($this->containerBuilder);
    }

    public function testDoctrineOrmDriver()
    {
        $this->containerBuilder->setParameter('two_fas_two_factor.db_driver', 'orm');

        $this->pass->process($this->containerBuilder);

        $this->assertTrue($this->containerBuilder->hasParameter(EntityPass::OPTION_CLASS));
        $this->assertTrue($this->containerBuilder->hasParameter(EntityPass::USER_CLASS));
        $this->assertTrue($this->containerBuilder->hasParameter(EntityPass::AUTHENTICATION_CLASS));
        $this->assertTrue($this->containerBuilder->hasParameter(EntityPass::REMEMBER_ME_CLASS));

        $option         = $this->containerBuilder->getParameter(EntityPass::OPTION_CLASS);
        $user           = $this->containerBuilder->getParameter(EntityPass::USER_CLASS);
        $authentication = $this->containerBuilder->getParameter(EntityPass::AUTHENTICATION_CLASS);
        $rememberMe     = $this->containerBuilder->getParameter(EntityPass::REMEMBER_ME_CLASS);

        $this->assertEquals(Option::class, $option);
        $this->assertEquals(User::class, $user);
        $this->assertEquals(Authentication::class, $authentication);
        $this->assertEquals(RememberMeToken::class, $rememberMe);
    }

    public function testCustomDriver()
    {
        $this->containerBuilder->setParameter('two_fas_two_factor.db_driver', 'custom');

        $this->pass->process($this->containerBuilder);

        $this->assertTrue($this->containerBuilder->hasParameter(EntityPass::OPTION_CLASS));
        $this->assertTrue($this->containerBuilder->hasParameter(EntityPass::USER_CLASS));
        $this->assertTrue($this->containerBuilder->hasParameter(EntityPass::AUTHENTICATION_CLASS));
        $this->assertTrue($this->containerBuilder->hasParameter(EntityPass::REMEMBER_ME_CLASS));

        $option         = $this->containerBuilder->getParameter(EntityPass::OPTION_CLASS);
        $user           = $this->containerBuilder->getParameter(EntityPass::USER_CLASS);
        $authentication = $this->containerBuilder->getParameter(EntityPass::AUTHENTICATION_CLASS);
        $rememberMe     = $this->containerBuilder->getParameter(EntityPass::REMEMBER_ME_CLASS);

        $this->assertEquals(\TwoFAS\TwoFactorBundle\Model\Entity\Option::class, $option);
        $this->assertEquals(\TwoFAS\TwoFactorBundle\Model\Entity\User::class, $user);
        $this->assertEquals(\TwoFAS\TwoFactorBundle\Model\Entity\Authentication::class, $authentication);
        $this->assertEquals(\TwoFAS\TwoFactorBundle\Model\Entity\RememberMeToken::class, $rememberMe);
    }

    public function testNotSupportedDriver()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'Invalid db driver');

        $this->containerBuilder->setParameter('two_fas_two_factor.db_driver', 'foo');

        $this->pass->process($this->containerBuilder);
    }
}
