<?php

namespace Fungio\TwoFactorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use TwoFAS\Api\TotpSecretGenerator;

/**
 * Validates TOTP Secret
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Validator\Constraints
 */
class TotpSecretValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint)
    {
        if (!preg_match('/^[234567QWERTYUIOPASDFGHJKLZXCVBNM]{' . TotpSecretGenerator::LENGTH . '}$/', $value)) {
            /** @var TotpSecret $constraint */
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}