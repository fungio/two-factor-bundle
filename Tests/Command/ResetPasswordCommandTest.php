<?php

namespace Fungio\TwoFactorBundle\Tests\Command;

use Symfony\Component\Console\Helper\QuestionHelper;
use Fungio\TwoFactorBundle\Command\ResetPasswordCommand;
use TwoFAS\Account\Exception\NotFoundException;
use TwoFAS\Account\Exception\PasswordResetAttemptsRemainingIsReachedException;
use TwoFAS\Account\TwoFAS;
use Exception;

class ResetPasswordCommandTest extends CommandTestCase
{
    /**
     * @var QuestionHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $questionHelper;

    /**
     * @var Fungio|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sdk;

    public function setUp()
    {
        $this->command = new ResetPasswordCommand();

        parent::setUp();

        $command              = $this->application->find('fungio:account:create');
        $this->questionHelper = $this->getMockBuilder(QuestionHelper::class)->setMethods(['ask'])->getMock();
        $this->sdk            = $this
            ->getMockBuilder(TwoFAS::class)
            ->disableOriginalConstructor()
            ->setMethods(['resetPassword'])
            ->getMock();
        $this->sdk->setBaseUrl('http://localhost');
        $this->container->set('fungio_two_factor.sdk.account', $this->sdk);
        $command->getHelperSet()->set($this->questionHelper, 'question');
    }

    public function testPasswordResetWithValidEmail()
    {
        $this->questionHelper->expects($this->at(0))
            ->method('ask')
            ->will($this->returnValue('test@test.com'));

        $this->applicationTester->run([$this->command->getName()]);

        $output = $this->applicationTester->getDisplay();
        $this->assertContains('Instructions on how to reset your password were sent to your email. Please check your inbox.', $output);
    }

    public function testEmptyEmailValidation()
    {
        $this->questionHelper->expects($this->at(0))
            ->method('ask')
            ->will($this->throwException(new Exception('The email can not be empty')));

        $this->applicationTester->run([$this->command->getName()]);

        $output = $this->applicationTester->getDisplay();
        $this->assertContains('The email can not be empty', $output);

    }

    public function testInvalidEmailValidation()
    {
        $this->questionHelper->expects($this->at(0))
            ->method('ask')
            ->will($this->throwException(new Exception('Enter a valid email address.')));

        $this->applicationTester->run([$this->command->getName()]);

        $output = $this->applicationTester->getDisplay();
        $this->assertContains('Enter a valid email address.', $output);
    }

    public function testEmailNotExists()
    {
        $this->questionHelper->expects($this->at(0))
            ->method('ask')
            ->will($this->returnValue('test@test.com'));

        $this->sdk->method('resetPassword')
            ->will(
                $this->throwException(
                    new NotFoundException('No data matching given criteria')
                )
            );

        $this->applicationTester->run([$this->command->getName()]);

        $output = $this->applicationTester->getDisplay();
        $this->assertContains('E-mail does not exists', $output);
    }

    public function testPasswordResetWithAttemptsRemainingReached()
    {
        $this->questionHelper->expects($this->at(0))
            ->method('ask')
            ->will($this->returnValue('test@test.com'));

        $this->sdk->method('resetPassword')
            ->will(
                $this->throwException(
                    new PasswordResetAttemptsRemainingIsReachedException('Limit of password reset attempts is already reached')
                )
            );

        $this->applicationTester->run([$this->command->getName()]);

        $output = $this->applicationTester->getDisplay();
        $this->assertContains('Limit of password reset attempts is already reached', $output);
    }
}
