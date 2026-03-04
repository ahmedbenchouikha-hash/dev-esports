<?php

namespace App\Form;

use App\Entity\Punition;
use App\Entity\Reclamation;
use App\Enum\StatutPunition;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
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
            ->add('reclamation', EntityType::class, [
                'class' => Reclamation::class,
                'choice_label' => 'titre',
                'label' => 'Réclamation',
                'required' => true,
                'placeholder' => 'Sélectionnez une réclamation',
            ]);

        if ($options['edit_mode']) {
            $builder->add('playerStatus', ChoiceType::class, [
                'label' => 'Mettre à jour le statut',
                'choices' => StatutPunition::choices(),
                'required' => true,
            ]);

            return;
        }

        $builder->add('playerStatus', ChoiceType::class, [
            'label' => 'Statut de punition',
            'choices' => StatutPunition::choices(),
            'required' => true,
            'data' => StatutPunition::ACTIF->value,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Punition::class,
            'edit_mode' => false,
        ]);

        $resolver->setAllowedTypes('edit_mode', 'bool');
    }
}
