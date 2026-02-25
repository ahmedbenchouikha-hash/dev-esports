<?php

namespace App\Form;

use App\Entity\Player;
use App\Entity\Team;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlayerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nickname', TextType::class, [
                'label' => 'Nickname',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Player nickname',
                    'class' => 'form-control'
                ]
            ])
            ->add('firstName', TextType::class, [
                'label' => 'First Name',
                'required' => true,
                'attr' => [
                    'placeholder' => 'First name',
                    'class' => 'form-control'
                ]
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Last Name',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Last name',
                    'class' => 'form-control'
                ]
            ])
            ->add('birthDate', DateType::class, [
                'label' => 'Birth Date',
                'required' => false,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                    'type' => 'date'
                ]
            ])
            ->add('role', TextType::class, [
                'label' => 'Role',
                'required' => false,
                'attr' => [
                    'placeholder' => 'e.g., ADC, Support, Mid',
                    'class' => 'form-control'
                ]
            ])
            ->add('team', EntityType::class, [
                'class' => Team::class,
                'choice_label' => 'name',
                'label' => 'Team',
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
            'data_class' => Player::class,
        ]);
    }
}
