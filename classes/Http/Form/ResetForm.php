<?php
namespace OpenCFP\Http\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ResetForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('password', 'repeated', [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 5])],
                'type' => 'password',
                'first_options' => ['label' => 'Password (minimum 5 characters)'],
                'second_options' => ['label' => 'Password (confirm)'],
                'first_name' => 'password',
                'second_name' => 'password2',
                'invalid_message' => 'Passwords did not match'])
            ->add('user_id', 'hidden')
            ->add('reset_code', 'hidden')
            ->getForm();
    }

    public function getName()
    {
        return 'reset';
    }
}
