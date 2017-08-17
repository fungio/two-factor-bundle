<?php

namespace TwoFAS\TwoFactorBundle\Tests\Validator;

use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;
use TwoFAS\TwoFactorBundle\Validator\Constraints\TotpSecret;
use TwoFAS\TwoFactorBundle\Validator\Constraints\TotpSecretValidator;

class TotpSecretValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TotpSecret
     */
    protected $constraint;

    /**
     * @var TotpSecretValidator
     */
    protected $validator;

    /**
     * @var ExecutionContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->context    = $this
            ->getMockBuilder(ExecutionContextInterface::class)
            ->setMethods(['buildViolation'])
            ->getMockForAbstractClass();
        $this->validator  = new TotpSecretValidator();
        $this->constraint = new TotpSecret();
        $this->validator->initialize($this->context);
    }


    public function testValidSecret()
    {
        $this->expectsNever();
        $this->validator->validate('FTNA7IY67NN5KOMB', $this->constraint);
    }

    public function testInvalidSecret()
    {
        $this->expectsOnce();
        $this->validator->validate('1FTA7IY67NN5KO89', $this->constraint);
    }

    public function testTooShortCode()
    {
        $this->expectsOnce();
        $this->validator->validate('FKJI', $this->constraint);
    }

    public function testTooLongCode()
    {
        $this->expectsOnce();
        $this->validator->validate('FTNA7IY67NN5KOMBFTNA7IY67NN5KOMB', $this->constraint);
    }

    public function testEmptyCode()
    {
        $this->expectsOnce();
        $this->validator->validate('', $this->constraint);
    }

    public function testNull()
    {
        $this->expectsOnce();
        $this->validator->validate(null, $this->constraint);
    }

    protected function expectsOnce()
    {
        $this->context->method('buildViolation')->willReturn($this->getMockForAbstractClass(ConstraintViolationBuilderInterface::class));
        $this->context->expects($this->once())->method('buildViolation');
    }

    protected function expectsNever()
    {
        $this->context->expects($this->never())->method('buildViolation');
    }
}
