<?php

namespace App\Form;

use App\Entity\TournamentRegistration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class TournamentRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('teamName', TextType::class, [
                'label' => 'Team Name',
                'attr' => [
                    'class' => 'form-control',
                    'readonly' => true,
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Team name is required.',
                    ]),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Team name cannot exceed 255 characters.',
                    ]),
                ],
            ])
            ->add('contactEmail', EmailType::class, [
                'label' => 'Contact Email',
                'attr' => [
                    'class' => 'form-control',
                    'readonly' => true,
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Email is required.',
                    ]),
                    new Email([
                        'message' => 'Please enter a valid email address.',
                    ]),
                ],
            ])
            ->add('additionalInfo', TextareaType::class, [
                'label' => 'Additional Notes (Optional)',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Add any additional information about your team registration...',
                ],
                'required' => false,
                'data' => '',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TournamentRegistration::class,
        ]);
    }
}
