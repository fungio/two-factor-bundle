<?php

namespace Fungio\TwoFactorBundle\Tests\Command;

use Fungio\TwoFactorBundle\Command\StatusCommand;
use Fungio\TwoFactorBundle\Model\Entity\OptionInterface;

class StatusCommandTest extends CommandTestCase
{
    public function setUp()
    {
        $this->command = new StatusCommand();
        parent::setUp();
    }

    public function testStatusIfOptionNotExists()
    {
        $this->applicationTester->run([$this->command->getName()]);

        $output = $this->applicationTester->getDisplay();
        $this->assertContains('Two Factor Authentication Service Status: disabled', $output);
    }

    public function testStatusEnabled()
    {
        $this->addOption($this->getOption(OptionInterface::STATUS, 1));

        $this->applicationTester->run([$this->command->getName()]);

        $output = $this->applicationTester->getDisplay();
        $this->assertContains('Two Factor Authentication Service Status: enabled', $output);
    }

    public function testStatusDisabled()
    {
        $this->addOption($this->getOption(OptionInterface::STATUS, 0));

        $this->applicationTester->run([$this->command->getName()]);

        $output = $this->applicationTester->getDisplay();
        $this->assertContains('Two Factor Authentication Service Status: disabled', $output);
    }
}
