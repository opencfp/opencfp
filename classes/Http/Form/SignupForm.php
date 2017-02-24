<?php
namespace OpenCFP\Http\Form;

use OpenCFP\Http\Form\Validator\Constraints\AccountExists;
use OpenCFP\Http\Form\Validator\Constraints\TwitterAccount;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Form object for our signup & profile pages, handles validation of form data
 */
class SignupForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', EmailType::class, [
            'label' => 'Email',
            'constraints' => [
                new Assert\NotBlank(),
                new AccountExists(),
            ],
            'attr' => ['placeholder' => 'you@domain.org', 'class' => 'form-control']])
            ->add('password', RepeatedType::class, [
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
            ->add('first_name', TextType::class, [
                'error_bubbling' => true,
                'required' => true,
                'label' => 'First Name',
                'attr' => ['placeholder' => 'First Name', 'class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => 1,
                        'max' => 255,
                        'maxMessage' => 'First name must be between 1 and 255 characters',
                    ])]
                ])
            ->add('last_name', TextType::class, [
                'error_bubbling' => true,
                'label' => 'Last Name',
                'attr' => ['placeholder' => 'Last Name', 'class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => 1,
                        'max' => 255,
                        'minMessage' => 'Last name must be between 1 and 255 characters',
                        'maxMessage' => 'Last name must be between 1 and 255 characters',
                    ])]
                ])
            ->add('company', TextType::class, [
                'error_bubbling' => true,
                'label' => 'Company',
                'attr' => ['placeholder' => 'Company', 'class' => 'form-control'],
                'required' => false,
                'constraints' => [new Assert\Length([
                    'min' => 1,
                    'max' => 255,
                    'minMessage' => 'Company name must be between 1 and 255 characters',
                    'maxMessage' => 'Company name must be between 1 and 255 characters',
                ])]])
            ->add('twitter', TextType::class, [
                'error_bubbling' => true,
                'label' => 'Twitter',
                'attr' => ['placeholder' => '@twitter', 'class' => 'form-control'],
                'required' => false,
                'constraints' => [new TwitterAccount()]
            ])
            ->add('speaker_bio', TextareaType::class, [
                'error_bubbling' => true,
                'label' => 'Speaker Bio',
                'attr' => ['placeholder' => 'Information About You', 'rows' => 5, 'class' => 'form-control'],
                'required' => false,
            ])
            ->add('airport', TextType::class, [
                'error_bubbling' => true,
                'label' => 'Departing Airport Code',
                'attr' => ['placeholder' => '3 Characters', 'class' => 'form-control'],
                'required' => false,
                'constraints' => [new Assert\Length([
                    'min' => 3,
                    'max' => 3,
                    'exactMessage' => 'Airport codes must be 3 alphabetical characters'
            ])]])
            ->add('speaker_info', TextareaType::class, [
                'error_bubbling' => true,
                'label' => 'Additional Notes',
                'attr' => ['placeholder' => 'Other infomration you feel the organizers should be aware of', 'class' => 'form-control'],
                'required' => false,
            ])
            ->add('transportation', CheckboxType::class, [
                'error_bubbling' => true,
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('hotel', CheckboxType::class, [
                'error_bubbling' => true,
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('speaker_photo', FileType::class, [
                'error_bubbling' => true,
                'constraints' => [new Assert\Image([
                    'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png'],
                    'maxSize' => 5 * 1048576,
                    'mimeTypesMessage' => 'You can only upload JPEG or PNG files'
                ])],
                'label' => 'Photo',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ]);

        // Now, we have to add some fields in if there are some optional values
        if (isset($options['id'])) {
            $builder->add('id', HiddenType::class, [
                'error_bubbling' => true,
                'required' => false,
            ]);
        }

        if (isset($options['coc_link'])) {
            $builder->add('agree_coc', CheckboxType::class, [
                'error_bubbling' => true,
                'label' => "I agree to abide by the <a href='{$options['coc_link']}' target='_blank'>Code of conduct</a>",
                'required' => false,
            ]);
        }
    }

    public function getName()
    {
        return 'signup';
    }
}
