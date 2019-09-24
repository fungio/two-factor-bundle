<?php

namespace Fungio\TwoFactorBundle\Tests\Model\Entity;

use Fungio\TwoFactorBundle\Model\Entity\Authentication;
use Fungio\TwoFactorBundle\Model\Entity\User;
use Fungio\TwoFactorBundle\Model\Entity\UserInterface;

class AuthenticationTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaults()
    {
        $authentication = $this->getAuthentication();

        $this->assertNull($authentication->getId());
        $this->assertNull($authentication->getUser());
        $this->assertNull($authentication->getType());
        $this->assertNull($authentication->getCreatedAt());
        $this->assertNull($authentication->getValidTo());
        $this->assertFalse($authentication->isVerified());
        $this->assertFalse($authentication->isBlocked());
    }

    public function testGetId()
    {
        $authentication = $this->getAuthentication();
        $authentication->setId(123);

        $this->assertEquals(123, $authentication->getId());
    }

    public function testGetUser()
    {
        $user           = new User();
        $authentication = $this->getAuthentication();
        $authentication->setUser($user);

        $this->assertInstanceOf(UserInterface::class, $authentication->getUser());
        $this->assertEquals($user, $authentication->getUser());
    }

    public function testGetType()
    {
        $authentication = $this->getAuthentication();
        $authentication->setType('totp');

        $this->assertEquals('totp', $authentication->getType());
    }

    public function testGetCreatedAt()
    {
        $date           = new \DateTime();
        $authentication = $this->getAuthentication();
        $authentication->setCreatedAt($date);

        $this->assertEquals($date, $authentication->getCreatedAt());
    }

    public function testGetValidTo()
    {
        $date           = new \DateTime();
        $authentication = $this->getAuthentication();
        $authentication->setValidTo($date);

        $this->assertEquals($date, $authentication->getValidTo());
    }

    public function testIsVerified()
    {
        $authentication = $this->getAuthentication();
        $authentication->setVerified(true);

        $this->assertTrue($authentication->isVerified());
    }

    public function testIsBlocked()
    {
        $authentication = $this->getAuthentication();
        $authentication->setBlocked(true);

        $this->assertTrue($authentication->isBlocked());
    }

    /**
     * @return Authentication
     */
    protected function getAuthentication()
    {
        return new Authentication();
    }
}
