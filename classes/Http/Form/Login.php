<?php
namespace OpenCFP\Http\Form;

use OpenCFP\Http\Form\Entity\Login as LoginEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class Login extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => LoginEntity::class]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', EmailType::class, [
            'label' => 'Email',
            'constraints' => [
                new Assert\NotBlank(),
            ],
            'required' => true,
            'attr' => ['placeholder' => 'you@domain.org', 'class' => 'form-control'], ])
            ->add('password', PasswordType::class, [
                'label' => 'Password',
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'attr' => ['class' => 'form-control', 'placeholder' => 'Password'],
                'required' => true,
            ]);
    }

    public function getName()
    {
        return 'login';
    }
}
