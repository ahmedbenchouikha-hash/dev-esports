<?php

namespace App\Form;

use App\Entity\Punition;
use App\Entity\Reclamation;
<<<<<<< HEAD
use App\Enum\StatutPunition;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
=======
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
>>>>>>> module-rewards
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
<<<<<<< HEAD
=======
            ->add('playerStatus', TextType::class, [
                'label' => 'Statut du joueur',
                'data' => 'BANNED',
            ])
>>>>>>> module-rewards
            ->add('reclamation', EntityType::class, [
                'class' => Reclamation::class,
                'choice_label' => 'titre',
                'label' => 'Réclamation',
                'required' => true,
                'placeholder' => 'Sélectionnez une réclamation',
<<<<<<< HEAD
            ]);

        if ($options['edit_mode']) {
            $builder->add('newBan', ChoiceType::class, [
                'mapped' => false,
                'label' => 'Ajouter un ban',
                'required' => false,
                'placeholder' => 'Choisir un nouveau ban',
                'choices' => StatutPunition::choices(),
            ]);

            return;
        }

        $builder->add('playerStatus', ChoiceType::class, [
            'label' => 'Ban',
            'placeholder' => 'Sélectionnez un ban',
            'choices' => StatutPunition::choices(),
        ]);
=======
            ])
        ;
>>>>>>> module-rewards
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Punition::class,
<<<<<<< HEAD
            'edit_mode' => false,
        ]);

        $resolver->setAllowedTypes('edit_mode', 'bool');
=======
        ]);
>>>>>>> module-rewards
    }
}
