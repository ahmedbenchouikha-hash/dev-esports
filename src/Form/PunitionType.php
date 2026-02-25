<?php

namespace App\Form;

use App\Entity\Punition;
use App\Entity\Reclamation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PunitionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startAt', DateTimeType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
            ])
            ->add('endAt', DateTimeType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
            ])
            ->add('playerStatus', TextType::class, [
                'label' => 'Statut du joueur',
                'data' => 'BANNED',
            ])
            ->add('reclamation', EntityType::class, [
                'class' => Reclamation::class,
                'choice_label' => 'titre',
                'label' => 'Réclamation',
                'required' => true,
                'placeholder' => 'Sélectionnez une réclamation',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Punition::class,
        ]);
    }
}
