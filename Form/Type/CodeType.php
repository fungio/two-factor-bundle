<?php

namespace TwoFAS\TwoFactorBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Text field for 2FAS Code - always empty (after submit if any errors occurred)
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Form\Type
 */
class CodeType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['value'] = '';
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return TextType::class;
    }
}