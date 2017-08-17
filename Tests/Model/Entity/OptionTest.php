<?php

namespace TwoFAS\TwoFactorBundle\Tests\Model\Entity;

use TwoFAS\TwoFactorBundle\Model\Entity\Option;
use TwoFAS\TwoFactorBundle\Model\Entity\OptionInterface;

class OptionTest extends \PHPUnit_Framework_TestCase
{
    public function testNull()
    {
        $option = $this->getOption();

        $this->assertNull($option->getId());
        $this->assertNull($option->getName());
        $this->assertNull($option->getValue());
    }

    public function testGetName()
    {
        $option = $this->getOption();
        $option->setName(OptionInterface::TOKEN);

        $this->assertEquals(OptionInterface::TOKEN, $option->getName());
    }

    public function testGetValue()
    {
        $option = $this->getOption();
        $option->setValue('123');

        $this->assertEquals('123', $option->getValue());
    }

    /**
     * @return Option
     */
    protected function getOption()
    {
        return new Option();
    }
}
