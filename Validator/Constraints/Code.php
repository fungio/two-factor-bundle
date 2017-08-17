<?php

namespace TwoFAS\TwoFactorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * 2FAS Code Constraint
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Validator\Constraints
 *
 * @Annotation
 */
class Code extends Constraint
{
    public $message = 'This value should be valid 2FAS code.';
}