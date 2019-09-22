<?php

namespace Fungio\TwoFactorBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Fungio\TwoFactorBundle\Form\Type\CodeType;
use Fungio\TwoFactorBundle\Validator\Constraints\Code;
use Fungio\TwoFactorBundle\Validator\Constraints\TotpSecret;

/**
 * Form to provide Two Factor Authentication Code.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Form
 */
class CodeForm extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', CodeType::class, [
                'constraints'        => [
                    new Code([
                        'message' => 'code.valid',
                        'groups'  => ['configure', 'check_code']
                    ]),
                ],
                'attr' => [
                    'minlength' => 6,
                    'maxlength' => 6
                ],
                'label_attr'         => ['class' => 'fungio-code-label'],
                'label_format'       => 'form.code.label',
                'translation_domain' => 'FungioTwoFactorBundle'
            ])
            ->add('remember_two_factor', CheckboxType::class, [
                'required'           => false,
                'label_attr'         => ['class' => 'fungio-remember-me-label'],
                'label_format'       => 'form.code.remember_me',
                'translation_domain' => 'FungioTwoFactorBundle'
            ])
            ->add('auth_id', CollectionType::class, [
                'entry_type'  => HiddenType::class,
                'label'       => false,
                'constraints' => [
                    new NotBlank([
                        'groups' => ['check_code']
                    ])
                ]
            ])
            ->add('totp_secret', HiddenType::class, [
                'constraints' => [
                    new NotBlank([
                        'groups' => ['configure']
                    ]),
                    new TotpSecret([
                        'message' => 'totp_secret.valid',
                        'groups'  => ['configure']
                    ])
                ],
                'error_bubbling'  => false
            ])
            ->add('submit', SubmitType::class, [
                'translation_domain' => false // Translate in twig
            ]);
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'method'          => 'POST',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'fungio_csrf_token'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return '';
    }
}