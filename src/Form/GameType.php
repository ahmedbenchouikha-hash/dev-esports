<?php

namespace App\Form;

use App\Entity\Game;
use App\Entity\Team;
use App\Entity\Tournament;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class GameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('team1', EntityType::class, [
                'class' => Team::class,
                'choice_label' => 'name',
                'label' => 'Team 1',
                'required' => true,
                'placeholder' => 'Select Team 1',
                'attr' => [
                    'class' => 'form-select form-select-dark',
                    'aria-label' => 'Team 1'
                ],
                'constraints' => [
                    new Assert\NotNull(message: 'Please select Team 1'),
                ]
            ])
            ->add('team2', EntityType::class, [
                'class' => Team::class,
                'choice_label' => 'name',
                'label' => 'Team 2',
                'required' => true,
                'placeholder' => 'Select Team 2',
                'attr' => [
                    'class' => 'form-select form-select-dark',
                    'aria-label' => 'Team 2'
                ],
                'constraints' => [
                    new Assert\NotNull(message: 'Please select Team 2'),
                ]
            ])
            ->add('score1', IntegerType::class, [
                'label' => 'Score Team 1',
                'required' => true,
                'attr' => [
                    'min' => 0,
                    'max' => 999,
                    'class' => 'form-control form-control-dark',
                    'placeholder' => 'Enter score (0-999)',
                    'aria-label' => 'Score for Team 1'
                ],
                'constraints' => [
                    new Assert\NotNull(message: 'Score is required'),
                    new Assert\GreaterThanOrEqual(value: 0, message: 'Score must be 0 or higher'),
                    new Assert\LessThanOrEqual(value: 999, message: 'Score must not exceed 999'),
                ]
            ])
            ->add('score2', IntegerType::class, [
                'label' => 'Score Team 2',
                'required' => true,
                'attr' => [
                    'min' => 0,
                    'max' => 999,
                    'class' => 'form-control form-control-dark',
                    'placeholder' => 'Enter score (0-999)',
                    'aria-label' => 'Score for Team 2'
                ],
                'constraints' => [
                    new Assert\NotNull(message: 'Score is required'),
                    new Assert\GreaterThanOrEqual(value: 0, message: 'Score must be 0 or higher'),
                    new Assert\LessThanOrEqual(value: 999, message: 'Score must not exceed 999'),
                ]
            ])
            ->add('matchdate', DateTimeType::class, [
                'label' => 'Match Date',
                'required' => true,
                'widget' => 'single_text',
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control form-control-dark',
                    'aria-label' => 'Match date and time'
                ],
                'constraints' => [
                    new Assert\NotNull(message: 'Match date is required'),
                ]
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Match Status',
                'required' => true,
                'choices' => [
                    'Pending' => 'pending',
                    'Ongoing' => 'ongoing',
                    'Finished' => 'finished',
                    'Cancelled' => 'cancelled'
                ],
                'placeholder' => 'Select a status',
                'attr' => [
                    'class' => 'form-select form-select-dark',
                    'aria-label' => 'Match status'
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Please select a status'),
                    new Assert\Choice(choices: ['pending', 'ongoing', 'finished', 'cancelled'], message: 'Invalid status selected'),
                ]
            ])
            ->add('tournament', EntityType::class, [
                'class' => Tournament::class,
                'choice_label' => 'name',
                'label' => 'Tournament',
                'required' => true,
                'placeholder' => 'Select Tournament',
                'attr' => [
                    'class' => 'form-select form-select-dark',
                    'aria-label' => 'Tournament'
                ],
                'constraints' => [
                    new Assert\NotNull(message: 'Please select a tournament'),
                ]
            ])
        ;

        // Populate matchdate field with entity value when displaying form
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();
            
            if ($data && $data->getMatchdate()) {
                $form->get('matchdate')->setData($data->getMatchdate());
            }
        });

        // Manually set matchdate after form validation and submission
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();
            
            if ($form->has('matchdate') && $form->get('matchdate')->getData()) {
                $data->setMatchdate($form->get('matchdate')->getData());
            }
            
            $event->setData($data);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Game::class,
        ]);
    }
}
