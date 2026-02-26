<?php

namespace App\Form;

use App\Entity\TournamentRegistration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;

class TournamentRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('teamName', TextType::class, [
                'label' => 'Team Name',
                'attr' => [
                    'placeholder' => 'Enter your team name',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter your team name'])
                ]
            ])
            ->add('contactEmail', EmailType::class, [
                'label' => 'Contact Email',
                'attr' => [
                    'placeholder' => 'Enter contact email',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter contact email']),
                    new Email(['message' => 'Please enter a valid email address'])
                ]
            ])
            ->add('contactPhone', TextType::class, [
                'label' => 'Contact Phone',
                'attr' => [
                    'placeholder' => 'Enter contact phone number',
                    'class' => 'form-control'
                ],
                'required' => false,
            ])
            ->add('additionalInfo', TextareaType::class, [
                'label' => 'Additional Information',
                'attr' => [
                    'placeholder' => 'Any additional information about your team...',
                    'class' => 'form-control',
                    'rows' => 4
                ],
                'required' => false,
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'I agree to the tournament rules and terms',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You must agree to the tournament rules.',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-check-input'
                ]
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