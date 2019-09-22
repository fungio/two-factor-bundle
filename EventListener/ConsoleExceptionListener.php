<?php

namespace Fungio\TwoFactorBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Fungio\Account\Exception\AuthorizationException;
use Fungio\Account\Exception\Exception as AccountException;
use Fungio\Account\Exception\ValidationException;
use Fungio\ValidationRules\ValidationRules;

/**
 * Class for handle exceptions from Fungio Console Commands.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\EventListener
 */
class ConsoleExceptionListener
{
    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * @var array
     */
    private $messages = [
        'email'                      => [
            ValidationRules::EMAIL  => 'E-mail is invalid',
            ValidationRules::UNIQUE => 'E-mail already exists'
        ],
        'password'                   => [
            ValidationRules::REQUIRED => 'Password is required',
            ValidationRules::MIN      => 'Password should have at least 6 characters'
        ],
        ValidationRules::UNSUPPORTED => 'Unknown Fungio Exception'
    ];

    /**
     * @param LoggerInterface|null $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * @param ConsoleExceptionEvent $event
     */
    public function onConsoleException(ConsoleExceptionEvent $event)
    {
        $exception = $event->getException();

        if (!$exception instanceof AccountException) {
            return;
        }

        $event->setException(new AccountException($this->getMessage($exception)));
    }

    /**
     * @param AccountException $exception
     *
     * @return string
     */
    private function getMessage(AccountException $exception)
    {
        if ($exception instanceof ValidationException) {
            return $this->getValidationMessage($exception);
        }

        if ($exception instanceof AuthorizationException) {
            return 'Invalid credentials entered';
        }

        return $exception->getMessage();
    }

    /**
     * @param ValidationException $exception
     *
     * @return string
     */
    private function getValidationMessage(ValidationException $exception)
    {
        foreach ($this->messages as $key => $messages) {
            if ($exception->hasKey($key)) {
                $error = $exception->getError($key);

                if (array_key_exists($error[0], $messages)) {
                    return $messages[$error[0]];
                }
            }
        }

        if (!is_null($this->logger)) {
            $this->logger->info('Validation error: ' . json_encode($exception->getErrors()));
        }

        return $this->messages[ValidationRules::UNSUPPORTED];
    }
}
