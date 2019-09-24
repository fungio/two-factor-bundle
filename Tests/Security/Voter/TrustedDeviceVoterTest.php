<?php

namespace Fungio\TwoFactorBundle\Tests\Security\Voter;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\User\UserInterface;
use Fungio\TwoFactorBundle\Model\Entity\RememberMeToken;
use Fungio\TwoFactorBundle\Model\Entity\User as FungioUser;
use Fungio\TwoFactorBundle\Security\Token\TwoFactorToken;
use Fungio\TwoFactorBundle\Security\Voter\TrustedDeviceVoter;
use Fungio\TwoFactorBundle\Storage\UserStorageInterface;

class TrustedDeviceVoterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var TrustedDeviceVoter
     */
    private $voter;


    public function setUp()
    {
        parent::setUp();

        $this->objectManager = $this->getMockBuilder(ObjectManager::class)->setMethods(['merge'])->getMockForAbstractClass();

        /** @var UserStorageInterface $userStorage */
        $userStorage = $this->getMockBuilder(UserStorageInterface::class)->getMockForAbstractClass();

        $this->voter = new TrustedDeviceVoter($userStorage, $this->objectManager);
    }

    public function testCanRemove()
    {
        $token = $this->getMockBuilder(TwoFactorToken::class)->disableOriginalConstructor()->getMock();
        $token->method('getUser')->willReturn($this->getMockBuilder(UserInterface::class)->disableOriginalConstructor()->getMockForAbstractClass());

        $rememberMeToken = new RememberMeToken();
        $user            = new FungioUser();

        $user->addToken($rememberMeToken);
        $rememberMeToken->setUser($user);

        $this->objectManager->method('merge')->willReturn($user);

        $this->assertEquals(1, $this->voter->vote($token, $rememberMeToken, ['remove']));
    }

    public function testCannotRemove()
    {
        $token = $this->getMockBuilder(TwoFactorToken::class)->disableOriginalConstructor()->getMock();
        $token->method('getUser')->willReturn($this->getMockBuilder(UserInterface::class)->disableOriginalConstructor()->getMockForAbstractClass());

        $rememberMeToken  = $this->getRememberMeToken();
        $rememberMeToken2 = $this->getRememberMeToken();
        $user             = $this->getUser();
        $user2            = $this->getUser();

        $user->addToken($rememberMeToken);
        $user2->addToken($rememberMeToken2);

        $rememberMeToken->setUser($user);
        $rememberMeToken2->setUser($user2);

        $this->objectManager->method('merge')->willReturn($user);

        $this->assertEquals(-1, $this->voter->vote($token, $rememberMeToken2, ['remove']));
    }

    public function testNotLoginUser()
    {
        $token = $this->getMockBuilder(TwoFactorToken::class)->disableOriginalConstructor()->getMock();
        $token->method('getUser')->willReturn(null);

        $rememberMeToken = new RememberMeToken();
        $user            = new FungioUser();

        $user->addToken($rememberMeToken);
        $rememberMeToken->setUser($user);

        $this->objectManager->method('merge')->willReturn($user);

        $this->assertEquals(-1, $this->voter->vote($token, $rememberMeToken, ['remove']));
    }

    public function testNotSupportsRole()
    {
        $token = $this->getMockBuilder(TwoFactorToken::class)->disableOriginalConstructor()->getMock();
        $this->assertEquals(0, $this->voter->vote($token, $this->getRememberMeToken(), ['edit']));
    }

    public function testNotSupportsToken()
    {
        $token = $this->getMockBuilder(TwoFactorToken::class)->disableOriginalConstructor()->getMock();
        $this->assertEquals(0, $this->voter->vote($token, $this->getUser(), ['remove']));
    }

    /**
     * @return RememberMeToken
     */
    private function getRememberMeToken()
    {
        return new RememberMeToken();
    }

    /**
     * @return FungioUser
     */
    private function getUser()
    {
        return new FungioUser();
    }
}
