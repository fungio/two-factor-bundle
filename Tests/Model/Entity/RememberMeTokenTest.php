<?php

namespace TwoFAS\TwoFactorBundle\Tests\Model\Entity;

use TwoFAS\TwoFactorBundle\Model\Entity\RememberMeToken;
use TwoFAS\TwoFactorBundle\Model\Entity\User;
use TwoFAS\TwoFactorBundle\Tests\DummyEntity;

class RememberMeTokenTest extends \PHPUnit_Framework_TestCase
{
    public function testNull()
    {
        $token = $this->getToken();

        $this->assertNull($token->getSeries());
        $this->assertNull($token->getValue());
        $this->assertNull($token->getClass());
        $this->assertNull($token->getUser());
        $this->assertNull($token->getBrowser());
        $this->assertNull($token->getLastUsedAt());
    }

    public function testGetSeries()
    {
        $token  = $this->getToken();
        $series = 'foo';
        $token->setSeries($series);

        $this->assertEquals($series, $token->getSeries());
    }

    public function testGetValue()
    {
        $token = $this->getToken();
        $value = 'bar';
        $token->setValue($value);

        $this->assertEquals($value, $token->getValue());
    }

    public function testGetClass()
    {
        $token = $this->getToken();
        $class = DummyEntity::class;
        $token->setClass($class);

        $this->assertEquals($class, $token->getClass());
    }

    public function testGetUsername()
    {
        $token = $this->getToken();
        $user  = new User();
        $token->setUser($user);

        $this->assertEquals($user, $token->getUser());
    }

    public function testGetBrowser()
    {
        $token   = $this->getToken();
        $browser = 'chrome';
        $token->setBrowser($browser);

        $this->assertEquals($browser, $token->getBrowser());
    }

    public function testGetLastUsed()
    {
        $token    = $this->getToken();
        $lastUsed = new \DateTime();
        $token->setLastUsedAt($lastUsed);

        $this->assertEquals($lastUsed, $token->getLastUsedAt());
    }

    /**
     * @return RememberMeToken
     */
    protected function getToken()
    {
        return new RememberMeToken();
    }
}
