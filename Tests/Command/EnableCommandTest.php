<?php

namespace Fungio\TwoFactorBundle\Tests\Command;

use Fungio\TwoFactorBundle\Command\EnableCommand;
use Fungio\TwoFactorBundle\Model\Entity\OptionInterface;

class EnableCommandTest extends CommandTestCase
{
    public function setUp()
    {
        $this->command = new EnableCommand();

        parent::setUp();
    }

    public function testCannotEnableWhenAccountNotExists()
    {
        $this->applicationTester->run([$this->command->getName()]);

        $output = $this->applicationTester->getDisplay();
        $this->assertContains('Fungio Login has not been set.', $output);
    }

    public function testEnableWhenOptionNotExists()
    {
        $this->mockAccountOptions();

        $this->applicationTester->run([$this->command->getName()]);

        $output = $this->applicationTester->getDisplay();
        $this->assertContains('Two Factor Authentication Service has been enabled', $output);
    }

    public function testEnableWhenDisabled()
    {
        $this->mockAccountOptions();
        $this->mockStatus(0);

        $this->applicationTester->run([$this->command->getName()]);

        $output = $this->applicationTester->getDisplay();
        $this->assertContains('Two Factor Authentication Service has been enabled', $output);
    }

    public function testEnableWhenEnabled()
    {
        $this->mockAccountOptions();
        $this->mockStatus(1);

        $this->applicationTester->run([$this->command->getName()]);

        $output = $this->applicationTester->getDisplay();
        $this->assertContains('Two Factor Authentication Service has been enabled', $output);
    }

    protected function mockAccountOptions()
    {
        $this->addOption($this->getOption(OptionInterface::LOGIN, uniqid()));
        $this->addOption($this->getOption(OptionInterface::TOKEN, uniqid()));
    }

    protected function mockStatus($status)
    {
        $this->addOption($this->getOption(OptionInterface::STATUS, $status));
    }
}
