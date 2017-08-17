<?php

namespace TwoFAS\TwoFactorBundle\Tests\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use TwoFAS\TwoFactorBundle\Security\Token\TwoFactorRememberMeToken;
use TwoFAS\TwoFactorBundle\Security\Voter\TwoFactorRememberedVoter;

class TwoFactorRememberedVoterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AccessDecisionManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $decisionManager;

    /**
     * @var TwoFactorRememberedVoter
     */
    private $voter;

    public function setUp()
    {
        parent::setUp();

        $this->decisionManager = $this->getMockForAbstractClass(AccessDecisionManagerInterface::class);

        $this->voter = new TwoFactorRememberedVoter($this->decisionManager);
    }

    public function testAuthenticatedFirstFactorFully()
    {
        $this->decisionManager->expects($this->at(0))->method('decide')->willReturn(false);
        $this->decisionManager->expects($this->at(1))->method('decide')->willReturn(true);
        $token = $this->getMockBuilder(UsernamePasswordToken::class)->disableOriginalConstructor()->getMock();
        $this->assertEquals(-1, $this->voter->vote($token, null, [TwoFactorRememberedVoter::IS_AUTHENTICATED_TWO_FACTOR_REMEMBERED]));
    }

    public function testAuthenticatedSecondFactorRemembered()
    {
        $this->decisionManager->expects($this->at(0))->method('decide')->willReturn(false);
        $this->decisionManager->expects($this->at(1))->method('decide')->willReturn(true);
        $token = $this->getMockBuilder(TwoFactorRememberMeToken::class)->disableOriginalConstructor()->getMock();
        $this->assertEquals(1, $this->voter->vote($token, null, [TwoFactorRememberedVoter::IS_AUTHENTICATED_TWO_FACTOR_REMEMBERED]));
    }

    public function testAuthenticatedSecondFactorFully()
    {
        $this->decisionManager->method('decide')->willReturn(true);
        $token = $this->getMockBuilder(TwoFactorRememberMeToken::class)->disableOriginalConstructor()->getMock();
        $this->assertEquals(1, $this->voter->vote($token, null, [TwoFactorRememberedVoter::IS_AUTHENTICATED_TWO_FACTOR_REMEMBERED]));
    }

    public function testNotAuthenticated()
    {
        $this->decisionManager->expects($this->at(0))->method('decide')->willReturn(false);
        $this->decisionManager->expects($this->at(1))->method('decide')->willReturn(false);

        $token = $this->getMockBuilder(AnonymousToken::class)->disableOriginalConstructor()->getMock();
        $this->assertEquals(-1, $this->voter->vote($token, null, [TwoFactorRememberedVoter::IS_AUTHENTICATED_TWO_FACTOR_REMEMBERED]));
    }

    public function testVoterNotSupports()
    {
        $token = $this->getMockBuilder(TwoFactorRememberMeToken::class)->disableOriginalConstructor()->getMock();
        $this->assertEquals(0, $this->voter->vote($token, null, ['ROLE_NOT_SUPPORT']));
    }
}
