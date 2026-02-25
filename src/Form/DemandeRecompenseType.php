<?php

namespace App\Form;

use App\Entity\DemandeRecompense;
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
            ->add('nomDemandeur', TextType::class, [
                'label' => 'Nom du demandeur',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez votre nom',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez votre email',
                ],
            ])
            ->add('motif', TextareaType::class, [
                'label' => 'Motif de la demande',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Expliquez le motif de votre demande',
                ],
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
