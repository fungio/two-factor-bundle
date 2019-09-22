<?php

namespace Fungio\TwoFactorBundle\Tests\Command;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Fungio\TwoFactorBundle\Command\CreateAccountCommand;
use Fungio\TwoFactorBundle\Model\Entity\OptionInterface;
use Fungio\Account\Exception\AuthorizationException;
use Fungio\Account\Exception\Exception as AccountException;
use Fungio\Account\Exception\ValidationException;
use Fungio\Account\Fungio;
use Fungio\ValidationRules\ValidationRules;

class CreateAccountCommandTest extends CommandTestCase
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
        $this->command = new CreateAccountCommand();

        parent::setUp();

        $command = $this->application->find('fungio:account:create');

        $this->questionHelper = $this->getMockBuilder(QuestionHelper::class)->setMethods(['ask'])->getMock();
        $this->sdk            = $this
            ->getMockBuilder(Fungio::class)
            ->disableOriginalConstructor()
            ->setMethods(['createClient', 'createIntegration', 'createKey', 'generateOAuthSetupToken', 'generateIntegrationSpecificToken'])
            ->getMock();
        $this->sdk->setBaseUrl('http://localhost');
        $this->container->set('two_fas_two_factor.sdk.account', $this->sdk);
        $command->getHelperSet()->set($this->questionHelper, 'question');
    }

    public function testExecuteWhenAccountNotExist()
    {
        $this->questionHelper->expects($this->at(0))
            ->method('ask')
            ->will($this->returnValue('no'));

        $this->questionHelper->expects($this->at(1))
            ->method('ask')
            ->will($this->returnValue('test@test.com'));

        $this->sdk->method('createIntegration')->willReturn($this->getIntegration());
        $this->sdk->method('createKey')->willReturn($this->getIntegrationKey());

        $this->applicationTester->run([$this->command->getName()]);

        $output = $this->applicationTester->getDisplay();
        $this->assertContains('Your Two FAS Account Created Successfully!', $output);
    }

    public function testExecuteWhenAccountExists()
    {
        $this->questionHelper->expects($this->at(0))
            ->method('ask')
            ->will($this->returnValue('yes'));

        $this->questionHelper->expects($this->at(1))
            ->method('ask')
            ->will($this->returnValue('test@test.com'));

        $this->questionHelper->expects($this->at(2))
            ->method('ask')
            ->will($this->returnValue('secret-password'));

        $this->sdk->method('createIntegration')->willReturn($this->getIntegration());
        $this->sdk->method('createKey')->willReturn($this->getIntegrationKey());

        $this->applicationTester->run([$this->command->getName()]);

        $output = $this->applicationTester->getDisplay();
        $this->assertContains('Your Two FAS Account Created Successfully!', $output);
    }

    public function testExecuteIfConfigured()
    {
        $this->addOption($this->getOption(OptionInterface::LOGIN, uniqid()));
        $this->addOption($this->getOption(OptionInterface::TOKEN, uniqid()));

        $this->applicationTester->run([$this->command->getName()]);

        $output = $this->applicationTester->getDisplay();
        $this->assertContains('Previous configuration has detected!', $output);
    }

    public function testInvalidCredentials()
    {
        $this->questionHelper->expects($this->at(0))
            ->method('ask')
            ->will($this->returnValue('yes'));

        $this->questionHelper->expects($this->at(1))
            ->method('ask')
            ->will($this->returnValue('test@test.com'));

        $this->questionHelper->expects($this->at(2))
            ->method('ask')
            ->will($this->returnValue('invalid-password'));

        $this->sdk->method('createIntegration')->will($this->throwException(new AuthorizationException('invalid_credentials')));

        $this->applicationTester->run([$this->command->getName()]);

        $output = $this->applicationTester->getDisplay();
        $this->assertContains('Invalid credentials entered', $output);
    }

    public function testEmptyEmailValidation()
    {
        $this->questionHelper->expects($this->at(0))
            ->method('ask')
            ->will($this->returnValue('yes'));

        $this->questionHelper->expects($this->at(1))
            ->method('ask')
            ->will($this->throwException(new \Exception('The email can not be empty')));

        $this->applicationTester->run([$this->command->getName()]);

        $output = $this->applicationTester->getDisplay();
        $this->assertContains('The email can not be empty', $output);

    }

    public function testInvalidEmailValidation()
    {
        $this->questionHelper->expects($this->at(0))
            ->method('ask')
            ->will($this->returnValue('yes'));

        $this->questionHelper->expects($this->at(1))
            ->method('ask')
            ->will($this->throwException(new \Exception('This value is not a valid email address.')));

        $this->applicationTester->run([$this->command->getName()]);

        $output = $this->applicationTester->getDisplay();
        $this->assertContains('This value is not a valid email address.', $output);
    }

    public function testFungioEmailValidation()
    {
        $this->questionHelper->expects($this->at(0))
            ->method('ask')
            ->will($this->returnValue('yes'));

        $this->questionHelper->expects($this->at(1))
            ->method('ask')
            ->will($this->returnValue('test@test.com'));

        $this->questionHelper->expects($this->at(2))
            ->method('ask')
            ->will($this->returnValue('secret-password'));

        $this->sdk->method('createIntegration')
            ->will(
                $this->throwException(
                    new ValidationException(
                        ['email' => [ValidationRules::UNIQUE]]
                    )
                )
            );

        $this->applicationTester->run([$this->command->getName()]);

        $output = $this->applicationTester->getDisplay();
        $this->assertContains('E-mail already exists', $output);
    }

    public function testEmptyPasswordValidation()
    {
        $this->questionHelper->expects($this->at(0))
            ->method('ask')
            ->will($this->returnValue('yes'));

        $this->questionHelper->expects($this->at(1))
            ->method('ask')
            ->will($this->returnValue('test@test.com'));

        $this->questionHelper->expects($this->at(2))
            ->method('ask')
            ->will($this->throwException(new \Exception('The password can not be empty')));

        $this->applicationTester->run([$this->command->getName()]);

        $output = $this->applicationTester->getDisplay();
        $this->assertContains('The password can not be empty', $output);
    }

    public function testFungioPasswordValidation()
    {
        $this->questionHelper->expects($this->at(0))
            ->method('ask')
            ->will($this->returnValue('yes'));

        $this->questionHelper->expects($this->at(1))
            ->method('ask')
            ->will($this->returnValue('test@test.com'));

        $this->questionHelper->expects($this->at(2))
            ->method('ask')
            ->will($this->returnValue('short'));

        $this->sdk->method('createIntegration')
            ->will(
                $this->throwException(
                    new ValidationException(
                        ['password' => [ValidationRules::MIN]]
                    )
                )
            );

        $this->applicationTester->run([$this->command->getName()]);

        $output = $this->applicationTester->getDisplay();
        $this->assertContains('Password should have at least 6 characters', $output);
    }

    public function testUnsupportedValidation()
    {
        $this->questionHelper->expects($this->at(0))
            ->method('ask')
            ->will($this->returnValue('no'));

        $this->questionHelper->expects($this->at(1))
            ->method('ask')
            ->will($this->returnValue('very-long-email-address@test.com'));

        $this->sdk->method('createIntegration')
            ->will(
                $this->throwException(
                    new ValidationException(
                        ['email' => [ValidationRules::MAX]]
                    )
                )
            );

        $this->applicationTester->run([$this->command->getName()]);

        $output = $this->applicationTester->getDisplay();
        $this->assertContains('Unknown Fungio Exception', $output);
    }

    public function testExecuteOnFungioError()
    {
        $this->questionHelper->expects($this->at(0))
            ->method('ask')
            ->will($this->returnValue('yes'));

        $this->questionHelper->expects($this->at(1))
            ->method('ask')
            ->will($this->returnValue('test@test.com'));

        $this->questionHelper->expects($this->at(2))
            ->method('ask')
            ->will($this->returnValue('secret-password'));

        $this->sdk->method('createIntegration')->will($this->throwException(new AccountException('Unsupported response')));

        $this->applicationTester->run([$this->command->getName()]);

        $output = $this->applicationTester->getDisplay();
        $this->assertContains('Unsupported response', $output);
    }

    public function testCannotExecuteIfEncryptionKeyIsNotSet()
    {
        $this->setExpectedException(\Exception::class, 'Two FAS Encryption Key is not set! Run "fungio:encryption-key:create first."');
        $container = $this->getMockBuilder(ContainerInterface::class)->getMockForAbstractClass();
        $container->method('get')->willReturn(null);
        $container->method('getParameter')->willReturn(null);
        $command = new CreateAccountCommand();
        $command->setContainer($container);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
    }

    public function testCannotExecuteIfPreviousConfigurationWasDetected()
    {
        $this->setExpectedException(\Exception::class, 'Previous configuration has detected! Run "fungio:account:delete first if you want create new account or use another credentials."');
        $container = $this->getMockBuilder(ContainerInterface::class)->getMockForAbstractClass();
        $container->method('get')->willReturn($this->container->get('two_fas_two_factor.option_persister'));
        $container->method('getParameter')->willReturn('not_null');
        $this->addOption($this->getOption(OptionInterface::STATUS, 1));
        $command = new CreateAccountCommand();
        $command->setContainer($container);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
    }
}
