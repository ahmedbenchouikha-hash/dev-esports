<?php

namespace App\Form;

use App\Entity\DemandeRecompense;
use App\Entity\Recompense;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DemandeRecompenseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('recompense', EntityType::class, [
                'class' => Recompense::class,
                'choice_label' => 'recompense',
                'label' => 'Récompense demandée',
            ])
            ->add('nomDemandeur', TextType::class, [
                'label' => 'Votre nom',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Votre email',
            ])
            ->add('motif', TextareaType::class, [
                'label' => 'Motif de la demande',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DemandeRecompense::class,
        ]);
    }
}