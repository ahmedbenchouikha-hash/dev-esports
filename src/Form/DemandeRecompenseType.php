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
                'choice_label' => function(Recompense $recompense) {
                    return sprintf('%s - %s (%s)', $recompense->getRecompense(), $recompense->getType(), $recompense->getTournament()?->getName());
                },
                'label' => 'Requested reward',
                'placeholder' => 'Select a reward',
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('nomDemandeur', TextType::class, [
                'label' => 'Full name',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Full name',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'votre.email@domain.com',
                ],
            ])
            ->add('motif', TextareaType::class, [
                'label' => 'Request reason',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5,
                    'placeholder' => 'Explain why you are requesting this reward (minimum 50 characters)',
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
