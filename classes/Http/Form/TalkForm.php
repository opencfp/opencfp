<?php
namespace OpenCFP\Http\Form;

use OpenCFP\Http\Form\Entity\Talk;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class representing the form that speakers fill out when they want
 * to submit a talk
 */
class TalkForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', HiddenType::class, ['error_bubbling' => true])
            ->add('user_id', HiddenType::class, ['error_bubbling' => true])
            ->add('title', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'max' => 100,
                        'maxMessage' => "Talk title can't be more than 100 characters",
                    ]),
                ],
                'required' => true,
                'error_bubbling' => true,
                'attr' => ['placeholder' => 'Talk Title', 'class' => 'form-control'],
            ])
            ->add('description', TextareaType::class, [
                'constraints' => [new Assert\NotBlank()],
                'required' => true,
                'error_bubbling' => true,
                'attr' => ['placeholder' => 'Description', 'class' => 'form-control'],
            ])
            ->add('slides', TextType::class, [
                'constraints' => [new Assert\Length([
                    'max' => 255,
                    'maxMessage' => "Slides URL can't be more than 255 characters", ])],
                'required' => false,
                'error_bubbling' => true,
                'attr' => ['placeholder' => 'URL for slides if online', 'class' => 'form-control'],
            ])
            ->add('other', TextareaType::class, [
                'attr' => [
                    'placeholder' => 'Other Considerations, such as Joind.In, Lanyrd, local user group, etc.',
                    'rows' => 5,
                    'class' => 'form-control',
                ],
                'required' => false,
            ])
            ->add('sponsor', ChoiceType::class, [
                'choices' => ['Yes' => true, 'No' => false],
                'required' => false,
                'error_bubbling' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('desired', ChoiceType::class, [
                'choices' => ['Yes' => true, 'No' => false],
                'required' => false,
                'error_bubbling' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('type', ChoiceType::class, [
                'choices' => $options['types'],
                'required' => true,
                'error_bubbling' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('category', ChoiceType::class, [
                'choices' => $options['categories'],
                'required' => true,
                'error_bubbling' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('level', ChoiceType::class, [
                'choices' => $options['levels'],
                'required' => true,
                'error_bubbling' => true,
                'attr' => ['class' => 'form-control'],
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Talk::class]);
        $resolver->setRequired(['categories', 'types', 'levels']);
    }
}
