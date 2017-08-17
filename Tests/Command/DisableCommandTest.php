<?php

namespace TwoFAS\TwoFactorBundle\Tests\Command;

use TwoFAS\TwoFactorBundle\Command\DisableCommand;
use TwoFAS\TwoFactorBundle\Model\Entity\OptionInterface;

class DisableCommandTest extends CommandTestCase
{
    public function setUp()
    {
        $this->command = new DisableCommand();

        parent::setUp();
    }

    public function testCannotEnableWhenAccountNotExists()
    {
        $this->applicationTester->run([$this->command->getName()]);

        $output = $this->applicationTester->getDisplay();
        $this->assertContains('TwoFAS Login has not been set.', $output);
    }

    public function testEnableWhenOptionNotExists()
    {
        $this->mockAccountOptions();

        $this->applicationTester->run([$this->command->getName()]);

        $output = $this->applicationTester->getDisplay();
        $this->assertContains('Two Factor Authentication Service has been disabled', $output);
    }

    public function testDisableWhenEnabled()
    {
        $this->mockAccountOptions();
        $this->mockStatus(1);

        $this->applicationTester->run([$this->command->getName()]);

        $output = $this->applicationTester->getDisplay();
        $this->assertContains('Two Factor Authentication Service has been disabled', $output);
    }

    public function testDisableWhenDisabled()
    {
        $this->mockAccountOptions();
        $this->mockStatus(0);

        $this->applicationTester->run([$this->command->getName()]);

        $output = $this->applicationTester->getDisplay();
        $this->assertContains('Two Factor Authentication Service has been disabled', $output);
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
