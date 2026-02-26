<?php

namespace App\Form;

use App\Entity\Recompense;
<<<<<<< HEAD
=======
use App\Entity\Tournament;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
>>>>>>> module-rewards
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecompenseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
<<<<<<< HEAD
            ->add('recompense', TextType::class, [
                'label' => 'Nom de la récompense',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le nom de la récompense',
=======
            ->add('tournament', EntityType::class, [
                'class' => Tournament::class,
                'choice_label' => 'name',
                'label' => 'Tournament',
                'placeholder' => 'Select a tournament',
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('recompense', TextType::class, [
                'label' => 'Reward name',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter the reward name',
>>>>>>> module-rewards
                    'maxlength' => 30,
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => [
<<<<<<< HEAD
                    'Accessoires informatique' => 'Accessoires informatique',
                    'Médailles' => 'Médailles',
                    'Trophées' => 'Trophées',
                    'Argent' => 'Argent',
=======
                    'Computer accessory' => 'Accessoire informatique',
                    'Medal' => 'Médaille',
                    'Cash' => 'Argent',
                    'Trophy' => 'Trophée',
>>>>>>> module-rewards
                ],
                'attr' => [
                    'class' => 'form-select',
                ],
<<<<<<< HEAD
            ])
            ->add('classement', IntegerType::class, [
                'label' => 'Classement (1-30)',
=======
                'choice_attr' => function($choice, $key, $value) {
                    $colors = [
                        'Accessoire informatique' => '#00f2fe',
                        'Médaille' => '#fd7e14',
                        'Argent' => '#95E1D3',
                        'Trophée' => '#FF6B6B',
                    ];
                    $color = $colors[$choice] ?? '#ffffff';
                    return ['data-color' => $color];
                },
            ])
            ->add('classement', IntegerType::class, [
                'label' => 'Rank (1-30)',
>>>>>>> module-rewards
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                    'max' => 30,
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
<<<<<<< HEAD
                    'placeholder' => 'Description de la récompense',
=======
                    'placeholder' => 'Enter a description (optional)',
>>>>>>> module-rewards
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recompense::class,
        ]);
    }
}

