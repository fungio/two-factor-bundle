<?php

namespace TwoFAS\TwoFactorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates 2FAS Code
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Validator\Constraints
 */
class CodeValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint)
    {
        if (!preg_match('/^[0-9]{6}$/', $value)) {
            /** @var Code $constraint */
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}