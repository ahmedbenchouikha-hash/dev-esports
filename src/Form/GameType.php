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
use Symfony\Component\OptionsResolver\OptionsResolver;

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
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('team2', EntityType::class, [
                'class' => Team::class,
                'choice_label' => 'name',
                'label' => 'Team 2',
                'required' => true,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('score1', IntegerType::class, [
                'label' => 'Score Team 1',
                'required' => true,
                'attr' => [
                    'min' => '0',
                    'class' => 'form-control'
                ]
            ])
            ->add('score2', IntegerType::class, [
                'label' => 'Score Team 2',
                'required' => true,
                'attr' => [
                    'min' => '0',
                    'class' => 'form-control'
                ]
            ])
            ->add('matchdate', DateTimeType::class, [
                'label' => 'Match Date',
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
                    'Finished' => 'finished',
                    'Cancelled' => 'cancelled'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('tournament', EntityType::class, [
                'class' => Tournament::class,
                'choice_label' => 'name',
                'label' => 'Tournament',
                'required' => true,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Game::class,
        ]);
    }
}
