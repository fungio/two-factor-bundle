<?php

namespace TwoFAS\TwoFactorBundle\Tests\Form;

use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use TwoFAS\TwoFactorBundle\Form\CodeForm;

class CodeFormTest extends TypeTestCase
{
    /**
     * @var ValidatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validator;

    public function testSubmitValidData()
    {
        $formData = [
            'code'                => 123456,
            'totp_secret'         => 'JBSWY3DPEHPK3PXP',
            'auth_id'             => ['a54dg327'],
            'remember_two_factor' => false
        ];

        $form = $this->factory->create(CodeForm::class, ['auth_id' => ['a54dg327']]);

        $expectedData = [
            'code'                => 123456,
            'totp_secret'         => 'JBSWY3DPEHPK3PXP',
            'auth_id'             => ['a54dg327'],
            'remember_two_factor' => false
        ];

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedData, $form->getData());

        $view     = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }

    protected function getExtensions()
    {
        $this->validator = $this
            ->getMockBuilder(ValidatorInterface::class)
            ->setMethods(['getMetadataFor'])
            ->getMockForAbstractClass();

        $this->validator->method('getMetadataFor')->willReturn(new ClassMetadata(Form::class));

        $this->validator
            ->method('validate')
            ->will($this->returnValue(new ConstraintViolationList()));

        return [
            new ValidatorExtension($this->validator)
        ];
    }


}
