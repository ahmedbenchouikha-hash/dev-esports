<?php

namespace App\Form;

use App\Entity\Equipe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class EquipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom')
            ->add('description', TextareaType::class, [
                'required' => false
            ])
            ->add('jeu', ChoiceType::class, [
                'choices' => [
                    'LoL' => 'LoL',
                    'CS:GO' => 'CS:GO',
                    'Dota 2' => 'Dota 2',
                    'FIFA' => 'FIFA'
                ]
            ])
            ->add('niveau', ChoiceType::class, [
                'choices' => [
                    'Débutant' => 'Débutant',
                    'Intermédiaire' => 'Intermédiaire',
                    'Pro' => 'Pro'
                ]
            ])
            ->add('couleurEquipe')
            ->add('logo', FileType::class, [
                'label' => 'Logo (Image)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide'
                    ])
                ]
            ])
            ->add('membres', TextareaType::class, [
                'mapped' => false,
                'required' => false,
                'attr' => ['placeholder' => 'Séparer les membres par des virgules']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Equipe::class,
        ]);
    }
}
