<?php

namespace TwoFAS\TwoFactorBundle\Tests\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use TwoFAS\TwoFactorBundle\Security\Token\TwoFactorToken;
use TwoFAS\TwoFactorBundle\Security\Voter\TwoFactorAuthenticatedVoter;

class TwoFactorAuthenticatedVoterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AccessDecisionManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $decisionManager;

    /**
     * @var TwoFactorAuthenticatedVoter
     */
    private $voter;

    public function setUp()
    {
        parent::setUp();

        $this->decisionManager = $this->getMockForAbstractClass(AccessDecisionManagerInterface::class);

        $this->voter = new TwoFactorAuthenticatedVoter($this->decisionManager);
    }

    public function testAuthenticatedFirstFactorFully()
    {
        $this->decisionManager->method('decide')->willReturn(true);
        $token = $this->getMockBuilder(UsernamePasswordToken::class)->disableOriginalConstructor()->getMock();
        $this->assertEquals(-1, $this->voter->vote($token, null, [TwoFactorAuthenticatedVoter::IS_AUTHENTICATED_TWO_FACTOR_FULLY]));
    }

    public function testAuthenticatedSecondFactorFully()
    {
        $this->decisionManager->method('decide')->willReturn(true);
        $token = $this->getMockBuilder(TwoFactorToken::class)->disableOriginalConstructor()->getMock();
        $this->assertEquals(1, $this->voter->vote($token, null, [TwoFactorAuthenticatedVoter::IS_AUTHENTICATED_TWO_FACTOR_FULLY]));
    }

    public function testNotAuthenticated()
    {
        $this->decisionManager->method('decide')->willReturn(false);

        $token = $this->getMockBuilder(AnonymousToken::class)->disableOriginalConstructor()->getMock();
        $this->assertEquals(-1, $this->voter->vote($token, null, [TwoFactorAuthenticatedVoter::IS_AUTHENTICATED_TWO_FACTOR_FULLY]));
    }

    public function testVoterNotSupports()
    {
        $token = $this->getMockBuilder(TwoFactorToken::class)->disableOriginalConstructor()->getMock();
        $this->assertEquals(0, $this->voter->vote($token, null, ['ROLE_NOT_SUPPORT']));
    }
}
