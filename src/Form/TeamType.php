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

class TeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Team Name',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Enter team name',
                    'class' => 'form-control'
                ]
            ])
            ->add('country', TextType::class, [
                'label' => 'Country',
                'required' => false,
                'attr' => [
                    'placeholder' => 'e.g., France',
                    'class' => 'form-control'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Team description',
                    'class' => 'form-control',
                    'rows' => 5
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
