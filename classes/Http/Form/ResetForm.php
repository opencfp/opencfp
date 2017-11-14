<?php

namespace OpenCFP\Http\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;

class ResetForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', RepeatedType::class, [
                'type'            => PasswordType::class,
                'first_options'   => ['label' => 'Password (minimum 5 characters)'],
                'second_options'  => ['label' => 'Password (confirm)'],
                'invalid_message' => 'Passwords did not match', ])
            ->add('user_id', HiddenType::class)
            ->add('reset_code', HiddenType::class)
            ->getForm();
    }

    public function getName()
    {
        return 'reset';
    }
}
