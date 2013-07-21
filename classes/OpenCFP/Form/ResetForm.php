<?php
namespace OpenCFP\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ResetForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('password', 'repeated', array(
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array('min' => 5))),
                'type' => 'password',
                'first_options' => array('label' => 'Password (minimum 5 characters)'),
                'second_options' => array('label' => 'Password (confirm)'),
                'first_name' => 'passwd',
                'second_name' => 'passwd2',
                'invalid_message' => 'Passwords did not match'))
            ->add('user_id', 'hidden')
            ->add('reset_code', 'hidden')
            ->getForm();

    }

    public function getName()
    {
        return 'reset';
    }
}
