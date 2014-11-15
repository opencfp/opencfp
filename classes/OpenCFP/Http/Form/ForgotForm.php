<?php
namespace OpenCFP\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ForgotForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', 'text', array(
            'constraints' => array(
                new Assert\NotBlank(),
                new Assert\Email()
            )
        ));
    }

    public function getName()
    {
        return 'forgot';
    }
}
