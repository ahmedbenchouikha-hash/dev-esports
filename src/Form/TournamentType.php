<?php

namespace App\Form;

use App\Entity\Tournament;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TournamentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Tournament Name',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Enter tournament name',
                    'class' => 'form-control'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Tournament description',
                    'class' => 'form-control',
                    'rows' => 5
                ]
            ])
            ->add('startDate', DateTimeType::class, [
                'label' => 'Start Date & Time',
                'required' => true,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                    'type' => 'datetime-local'
                ]
            ])
            ->add('endDate', DateTimeType::class, [
                'label' => 'End Date & Time',
                'required' => true,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                    'type' => 'datetime-local'
                ]
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'required' => true,
                'choices' => [
                    'Pending' => 'pending',
                    'Ongoing' => 'ongoing',
                    'Completed' => 'completed',
                    'Cancelled' => 'cancelled'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('location', TextType::class, [
                'label' => 'Location',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Tournament location',
                    'class' => 'form-control'
                ]
            ])
            ->add('prizePool', MoneyType::class, [
                'label' => 'Prize Pool',
                'required' => false,
                'currency' => 'USD',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('rules', CollectionType::class, [
                'label' => 'Tournament Rules',
                'entry_type' => TextType::class,
                'entry_options' => [
                    'attr' => [
                        'placeholder' => 'Enter a rule',
                        'class' => 'form-control',
                        'maxlength' => 500
                    ]
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tournament::class,
        ]);
    }
}
