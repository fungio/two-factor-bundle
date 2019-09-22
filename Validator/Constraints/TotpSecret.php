<?php

namespace Fungio\TwoFactorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Validator\Constraints
 *
 * @Annotation
 */
class TotpSecret extends Constraint
{
    public $message = 'This value should be valid TOTP Secret.';
}