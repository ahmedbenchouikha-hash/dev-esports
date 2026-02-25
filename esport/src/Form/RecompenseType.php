<?php

namespace App\Form;

use App\Entity\Recompense;
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
            ->add('recompense', TextType::class, [
                'label' => 'Nom de la récompense',
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => [
                    'Accessoire informatique' => 'Accessoire informatique',
                    'Médaille' => 'Médaille',
                    'Argent' => 'Argent',
                    'Trophée' => 'Trophée',
                ],
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
                'label' => 'Classement',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
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