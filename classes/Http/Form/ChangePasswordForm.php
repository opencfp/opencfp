<?php
namespace OpenCFP\Http\Form;

use OpenCFP\Http\Form\Entity\ChangePassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ChangePasswordForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('password', RepeatedType::class, [
            'error_bubbling' => true,
            'type' => PasswordType::class,
            'required' => true,
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Length([
                    'min' => 5,
                    'max' => 255,
                    'minMessage' => 'Passwords must be between 5 and 255 characters',
                    'maxMessage' => 'Passwords must be between 5 and 255 characters',
                ])],
            'first_options' => ['label' => 'Password', 'attr' => ['class' => 'form-control', 'placeholder' => 'Password (minimum 5 characters)']],
            'second_options' => ['label' => 'Password', 'attr' => ['class' => 'form-control', 'placeholder' => 'Password (confirm)']],
            'invalid_message' => 'Passwords did not match', ])
            ->add('user_id', HiddenType::class, [
                'error_bubbling' => true,
                'required' => true,
                'constraints' => [new Assert\NotBlank()]
            ])
            ->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => ChangePassword::class]);
    }


    public function getName()
    {
        return 'changepassword';
    }
}
