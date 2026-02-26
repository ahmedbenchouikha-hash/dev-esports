<?php

namespace App\Form;

use App\Entity\Team;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class TeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Team Name',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Team name is required']),
                    new Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'Team name must be at least 2 characters',
                        'maxMessage' => 'Team name must not exceed 255 characters'
                    ])
                ],
                'attr' => [
                    'placeholder' => 'Enter team name (min 2 characters)',
                    'class' => 'form-control',
                    'minlength' => 2,
                    'maxlength' => 255
                ]
            ])
            ->add('country', TextType::class, [
                'label' => 'Country',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Country is required']),
                    new Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'Country must be at least 2 characters',
                        'maxMessage' => 'Country must not exceed 255 characters'
                    ])
                ],
                'attr' => [
                    'placeholder' => 'e.g., France (required)',
                    'class' => 'form-control',
                    'minlength' => 2,
                    'maxlength' => 255
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Description is required']),
                    new Length([
                        'min' => 10,
                        'max' => 1000,
                        'minMessage' => 'Description must be at least 10 characters',
                        'maxMessage' => 'Description must not exceed 1000 characters'
                    ])
                ],
                'attr' => [
                    'placeholder' => 'Team description (min 10 characters)',
                    'class' => 'form-control',
                    'rows' => 5,
                    'minlength' => 10,
                    'maxlength' => 1000
                ]
            ])
            ->add('detailedDescription', TextareaType::class, [
                'label' => 'Detailed Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Detailed team information',
                    'class' => 'form-control',
                    'rows' => 5
                ]
            ])
            ->add('jeu', ChoiceType::class, [
                'label' => 'Game',
                'required' => false,
                'choices' => [
                    'LoL' => 'LoL',
                    'CS:GO' => 'CS:GO',
                    'Dota 2' => 'Dota 2',
                    'FIFA' => 'FIFA'
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('niveau', ChoiceType::class, [
                'label' => 'Level',
                'required' => false,
                'choices' => [
                    'Beginner' => 'Débutant',
                    'Intermediate' => 'Intermédiaire',
                    'Pro' => 'Pro'
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('couleurEquipe', TextType::class, [
                'label' => 'Team Color',
                'required' => false,
                'attr' => [
                    'placeholder' => 'e.g., #FF0000',
                    'class' => 'form-control'
                ]
            ])
            ->add('logo', FileType::class, [
                'label' => 'Logo (Image)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                        'mimeTypesMessage' => 'Please upload a valid image'
                    ])
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('membres', TextareaType::class, [
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Separate members by commas',
                    'class' => 'form-control',
                    'rows' => 3
                ]
            ])
            ->add('captainId', TextType::class, [
                'label' => 'Captain ID',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Captain ID',
                    'class' => 'form-control'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Team::class,
        ]);
    }
}
