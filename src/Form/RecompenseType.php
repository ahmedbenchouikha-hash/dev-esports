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
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le nom de la récompense',
                    'maxlength' => 30,
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => [
                    'Accessoires informatique' => 'Accessoires informatique',
                    'Médailles' => 'Médailles',
                    'Trophées' => 'Trophées',
                    'Argent' => 'Argent',
                ],
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('classement', IntegerType::class, [
                'label' => 'Classement (1-30)',
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
                    'placeholder' => 'Description de la récompense',
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

