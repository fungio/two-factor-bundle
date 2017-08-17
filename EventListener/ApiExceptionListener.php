<?php

namespace TwoFAS\TwoFactorBundle\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Translation\TranslatorInterface;
use TwoFAS\Api\Exception\AuthorizationException;
use TwoFAS\Api\Exception\ValidationException;
use TwoFAS\Api\ValidationRules;

/**
 * Listen for TwoFAS Api Exceptions, add flash, etc.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\EventListener
 */
class ApiExceptionListener
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var array
     */
    private $messages = [
        'code'                       => [
            ValidationRules::REQUIRED => 'authentication.code.required',
            ValidationRules::DIGITS   => 'authentication.code.digits',
        ],
        'totp_secret'                => [
            ValidationRules::REQUIRED => 'authentication.totp_secret.required'
        ],
        ValidationRules::UNSUPPORTED => 'general.unknown_error'
    ];

    public function __construct(Session $session, TranslatorInterface $translator)
    {
        $this->session    = $session;
        $this->translator = $translator;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $uri       = $event->getRequest()->getRequestUri();

        if ($exception instanceof ValidationException) {
            $message = $this->getValidationMessage($exception);
            $this->notifyException($message);
            $event->setResponse(new RedirectResponse($uri));
        } elseif ($exception instanceof AuthorizationException) {
            $event->setException(new AccessDeniedHttpException($exception->getMessage()));
        }
    }

    /**
     * @param ValidationException $exception
     *
     * @return string
     */
    protected function getValidationMessage(ValidationException $exception)
    {
        foreach ($this->messages as $key => $messages) {
            if ($exception->hasKey($key)) {
                $error = $exception->getError($key)[0];

                if (array_key_exists($error, $messages)) {
                    return $messages[$error];
                }
            }
        }

        return $this->messages[ValidationRules::UNSUPPORTED];
    }

    /**
     * @param string $message
     */
    protected function notifyException($message)
    {
        $this->session->getFlashBag()->add('danger', $this->translator->trans($message));
    }
}