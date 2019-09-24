<?php

namespace Fungio\TwoFactorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * 2FAS Code Constraint
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Validator\Constraints
 *
 * @Annotation
 */
class Code extends Constraint
{
    public $message = 'This value should be valid 2FAS code.';
}