<?php
namespace OpenCFP\Http\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class ForgotForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', EmailType::class, ['attr' => ['placeholder' => 'you@domain.org']])
            ->add('send', SubmitType::class, ['label' => 'Reset my password']);
    }

    public function getName()
    {
        return 'forgot';
    }
}
