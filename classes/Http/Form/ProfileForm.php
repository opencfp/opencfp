<?php
namespace OpenCFP\Http\Form;

use OpenCFP\Http\Form\Entity\Profile;
use OpenCFP\Http\Form\Validator\Constraints\TwitterAccount;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Form object for our signup & profile pages, handles validation of form data
 */
class ProfileForm extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Profile::class]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', HiddenType::class, [
                'constraints' => [new Assert\NotBlank()],
                'required' => true,
                'error_bubbling' => true,
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'attr' => ['placeholder' => 'you@domain.org', 'class' => 'form-control']
            ])
            ->add('first_name', TextType::class, [
                'error_bubbling' => true,
                'required' => true,
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
                'attr' => ['placeholder' => '@twitter', 'class' => 'form-control'],
                'required' => false,
                'constraints' => [new TwitterAccount()]
            ])
            ->add('bio', TextareaType::class, [
                'error_bubbling' => true,
                'attr' => ['placeholder' => 'Information About You', 'rows' => 5, 'class' => 'form-control'],
                'required' => false,
            ])
            ->add('airport', TextType::class, [
                'error_bubbling' => true,
                'attr' => ['placeholder' => '3 Characters', 'class' => 'form-control'],
                'required' => false,
                'constraints' => [new Assert\Length([
                    'min' => 3,
                    'max' => 3,
                    'exactMessage' => 'Airport codes must be 3 alphabetical characters'
                ])]])
            ->add('info', TextareaType::class, [
                'error_bubbling' => true,
                'label' => 'Additional Notes',
                'attr' => ['placeholder' => 'Other information you feel the organizers should be aware of', 'class' => 'form-control'],
                'required' => false,
            ])
            ->add('transportation', ChoiceType::class, [
                'choices' => [
                    'Yes' => true,
                    'No' => false,
                ],
                'error_bubbling' => true,
                'required' => false,
                'attr' => ['class' => 'form-control', 'length' => 5],
            ])
            ->add('hotel', ChoiceType::class, [
                'choices' => [
                    'Yes' => true,
                    'No' => false,
                ],
                'error_bubbling' => true,
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('photo_path', FileType::class, [
                'error_bubbling' => true,
                'constraints' => [new Assert\Image([
                    'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png'],
                    'maxSize' => 5 * 1048576,
                    'mimeTypesMessage' => 'You can only upload JPEG or PNG files'
                ])],
                'required' => false,
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function getName()
    {
        return 'profile';
    }


}
