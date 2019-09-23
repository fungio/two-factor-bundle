<?php

namespace Fungio\TwoFactorBundle\Tests\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Fungio\TwoFactorBundle\EventListener\ConsoleExceptionListener;
use TwoFAS\Account\Exception\AuthorizationException;
use TwoFAS\Account\Exception\Exception;
use TwoFAS\Account\Exception\ValidationException;
use TwoFAS\ValidationRules\ValidationRules;

class ConsoleExceptionListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConsoleExceptionListener
     */
    private $listener;

    public function setUp()
    {
        parent::setUp();

        $this->listener = new ConsoleExceptionListener($this->getMockForAbstractClass(LoggerInterface::class));
    }

    public function testNotAccountException()
    {
        $event = $this->getEvent();
        $this->listener->onConsoleException($event);

        $this->assertEquals(new \Exception(), $event->getException());
    }

    public function testValidationException()
    {
        $event     = $this->getEvent();
        $exception = new ValidationException(['email' => [ValidationRules::EMAIL]]);
        $event->setException($exception);

        $this->listener->onConsoleException($event);

        $actualException = $event->getException();
        $this->assertInstanceOf(Exception::class, $actualException);
        $this->assertEquals('E-mail is invalid', $actualException->getMessage());
    }

    public function testAuthorizationException()
    {
        $event     = $this->getEvent();
        $exception = new AuthorizationException();
        $event->setException($exception);

        $this->listener->onConsoleException($event);

        $actualException = $event->getException();
        $this->assertInstanceOf(Exception::class, $actualException);
        $this->assertEquals('Invalid credentials entered', $actualException->getMessage());
    }

    public function testServerErrorException()
    {
        $event     = $this->getEvent();
        $exception = new Exception('Server error message');
        $event->setException($exception);

        $this->listener->onConsoleException($event);

        $actualException = $event->getException();
        $this->assertInstanceOf(Exception::class, $actualException);
        $this->assertEquals('Server error message', $actualException->getMessage());
    }

    /**
     * @return ConsoleExceptionEvent
     */
    private function getEvent()
    {
        $command   = $this->getMockBuilder(Command::class)->disableOriginalConstructor()->getMock();
        $input     = $this->getMockForAbstractClass(InputInterface::class);
        $output    = $this->getMockForAbstractClass(OutputInterface::class);
        $exception = new \Exception();
        $code      = 0;

        return new ConsoleExceptionEvent($command, $input, $output, $exception, $code);
    }
}
