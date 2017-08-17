<?php

namespace TwoFAS\TwoFactorBundle\Tests\Command;

use TwoFAS\TwoFactorBundle\Command\CreateEncryptionKeyCommand;

class CreateEncryptionKeyCommandTest extends CommandTestCase
{

    public function setUp()
    {
        $this->command = new CreateEncryptionKeyCommand();

        parent::setUp();
    }

    public function testGetExpectedOutput()
    {
        $this->applicationTester->run([$this->command->getName()]);

        $output = $this->applicationTester->getDisplay();

        $this->assertRegExp('/^\nYour Two FAS Encryption Key: [a-zA-Z0-9\/\+\=]+\n\nPut this key in your parameters.yml$/', $output);
    }
}
