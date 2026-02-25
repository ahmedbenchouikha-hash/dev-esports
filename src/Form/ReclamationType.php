<?php

namespace App\Form;

use App\Entity\Reclamation;
use App\Entity\Player;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description détaillée',
                'attr' => ['class' => 'form-control', 'rows' => 5],
            ])
            ->add('attachment', FileType::class, [
                'label' => 'Fichier (optionnel)',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('player', EntityType::class, [
                'class' => Player::class,
                'choice_label' => 'nickname',
                'label' => 'Joueur',
                'required' => false,
                'placeholder' => 'Sélectionner un joueur',
                'attr' => ['class' => 'form-control'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
        ]);
    }
}
