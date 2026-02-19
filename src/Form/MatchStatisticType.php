<?php

namespace App\Form;

use App\Entity\Game;
use App\Entity\MatchStatistic;
use App\Entity\Player;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints as Assert;

class MatchStatisticType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('player', EntityType::class, [
                'class' => Player::class,
                'choice_label' => 'nickname',
                'label' => 'Player',
                'placeholder' => 'Select a player',
                'attr' => [
                    'class' => 'form-select form-select-dark',
                    'aria-label' => 'Select a player'
                ],
                'constraints' => [
                    new Assert\NotNull(message: 'Please select a player'),
                ]
            ])
            ->add('game', EntityType::class, [
                'class' => Game::class,
                'choice_label' => fn(Game $game) => sprintf(
                    '%s vs %s (ID: %d)',
                    $game->getTeam1()?->getName(),
                    $game->getTeam2()?->getName(),
                    $game->getId()
                ),
                'label' => 'Match',
                'placeholder' => 'Select a match',
                'attr' => [
                    'class' => 'form-select form-select-dark',
                    'aria-label' => 'Select a match'
                ],
                'constraints' => [
                    new Assert\NotNull(message: 'Please select a match'),
                ]
            ])
            ->add('kills', IntegerType::class, [
                'label' => 'Kills',
                'attr' => [
                    'min' => 0,
                    'max' => 999,
                    'class' => 'form-control form-control-dark',
                    'placeholder' => 'Enter number of kills',
                    'aria-label' => 'Number of kills'
                ],
                'constraints' => [
                    new Assert\NotNull(message: 'Kills is required'),
                    new Assert\GreaterThanOrEqual(value: 0, message: 'Kills must be 0 or higher'),
                    new Assert\LessThanOrEqual(value: 999, message: 'Kills cannot exceed 999'),
                ]
            ])
            ->add('deaths', IntegerType::class, [
                'label' => 'Deaths',
                'attr' => [
                    'min' => 0,
                    'max' => 999,
                    'class' => 'form-control form-control-dark',
                    'placeholder' => 'Enter number of deaths',
                    'aria-label' => 'Number of deaths'
                ],
                'constraints' => [
                    new Assert\NotNull(message: 'Deaths is required'),
                    new Assert\GreaterThanOrEqual(value: 0, message: 'Deaths must be 0 or higher'),
                    new Assert\LessThanOrEqual(value: 999, message: 'Deaths cannot exceed 999'),
                ]
            ])
            ->add('assists', IntegerType::class, [
                'label' => 'Assists',
                'attr' => [
                    'min' => 0,
                    'max' => 999,
                    'class' => 'form-control form-control-dark',
                    'placeholder' => 'Enter number of assists',
                    'aria-label' => 'Number of assists'
                ],
                'constraints' => [
                    new Assert\NotNull(message: 'Assists is required'),
                    new Assert\GreaterThanOrEqual(value: 0, message: 'Assists must be 0 or higher'),
                    new Assert\LessThanOrEqual(value: 999, message: 'Assists cannot exceed 999'),
                ]
            ])
            ->add('damageDealt', NumberType::class, [
                'label' => 'Damage Dealt',
                'attr' => [
                    'min' => 0,
                    'step' => 0.1,
                    'max' => 999999,
                    'class' => 'form-control form-control-dark',
                    'placeholder' => 'Enter damage dealt',
                    'aria-label' => 'Total damage dealt'
                ],
                'constraints' => [
                    new Assert\NotNull(message: 'Damage dealt is required'),
                    new Assert\GreaterThanOrEqual(value: 0, message: 'Damage must be 0 or higher'),
                ]
            ])
            ->add('damageTaken', NumberType::class, [
                'label' => 'Damage Taken',
                'attr' => [
                    'min' => 0,
                    'step' => 0.1,
                    'max' => 999999,
                    'class' => 'form-control form-control-dark',
                    'placeholder' => 'Enter damage taken',
                    'aria-label' => 'Total damage taken'
                ],
                'constraints' => [
                    new Assert\NotNull(message: 'Damage taken is required'),
                    new Assert\GreaterThanOrEqual(value: 0, message: 'Damage must be 0 or higher'),
                ]
            ])
            ->add('objectivesDestroyed', IntegerType::class, [
                'label' => 'Objectives Destroyed',
                'attr' => [
                    'min' => 0,
                    'max' => 999,
                    'class' => 'form-control form-control-dark',
                    'placeholder' => 'Enter objectives destroyed',
                    'aria-label' => 'Number of objectives destroyed'
                ],
                'constraints' => [
                    new Assert\NotNull(message: 'Objectives destroyed is required'),
                    new Assert\GreaterThanOrEqual(value: 0, message: 'Objectives must be 0 or higher'),
                ]
            ])
            ->add('goldEarned', NumberType::class, [
                'label' => 'Gold Earned',
                'attr' => [
                    'min' => 0,
                    'step' => 0.1,
                    'max' => 999999,
                    'class' => 'form-control form-control-dark',
                    'placeholder' => 'Enter gold earned',
                    'aria-label' => 'Total gold earned'
                ],
                'constraints' => [
                    new Assert\NotNull(message: 'Gold earned is required'),
                    new Assert\GreaterThanOrEqual(value: 0, message: 'Gold must be 0 or higher'),
                ]
            ])
            ->add('role', TextType::class, [
                'label' => 'Role',
                'required' => false,
                'attr' => [
                    'class' => 'form-control form-control-dark',
                    'placeholder' => 'e.g., ADC, Support, Mid, Top, Jungle',
                    'maxlength' => 255,
                    'aria-label' => 'Player role in the team'
                ],
                'constraints' => [
                    new Assert\Length(max: 255, maxMessage: 'Role cannot exceed 255 characters'),
                ]
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
                'attr' => [
                    'class' => 'form-control form-control-dark',
                    'rows' => 3,
                    'placeholder' => 'Additional notes about the player\'s performance',
                    'maxlength' => 5000,
                    'aria-label' => 'Additional performance notes'
                ],
                'constraints' => [
                    new Assert\Length(max: 5000, maxMessage: 'Notes cannot exceed 5000 characters'),
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MatchStatistic::class,
        ]);
    }
}
