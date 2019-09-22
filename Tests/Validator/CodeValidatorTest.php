<?php

namespace Fungio\TwoFactorBundle\Tests\Validator;

use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;
use Fungio\TwoFactorBundle\Validator\Constraints\Code;
use Fungio\TwoFactorBundle\Validator\Constraints\CodeValidator;

class CodeValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Code
     */
    protected $constraint;

    /**
     * @var CodeValidator
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
        $this->validator  = new CodeValidator();
        $this->constraint = new Code();
        $this->validator->initialize($this->context);
    }


    public function testStringValidCode()
    {
        $this->expectsNever();
        $this->validator->validate('123456', $this->constraint);
    }

    public function testIntegerValidCode()
    {
        $this->expectsNever();
        $this->validator->validate(123456, $this->constraint);
    }

    public function testCodeWithLetters()
    {
        $this->expectsOnce();
        $this->validator->validate('12asb43', $this->constraint);
    }

    public function testTooShortCode()
    {
        $this->expectsOnce();
        $this->validator->validate(123, $this->constraint);
    }

    public function testTooLongCode()
    {
        $this->expectsOnce();
        $this->validator->validate(12345677, $this->constraint);
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
