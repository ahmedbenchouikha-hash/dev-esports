<?php

namespace App\Form;

use App\Entity\Depense;
use App\Entity\Team;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class DepenseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $managerTeams = $options['manager_teams'] ?? [];

        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de la dépense',
                'required' => true,
                'attr' => ['class' => 'w-full px-4 py-2 rounded bg-gray-700 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-rankup-primary']
            ])
            ->add('montant', MoneyType::class, [
                'label' => 'Montant',
                'currency' => 'EUR',
                'required' => true,
                'attr' => ['class' => 'w-full px-4 py-2 rounded bg-gray-700 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-rankup-primary']
            ])
            ->add('categorie', ChoiceType::class, [
                'label' => 'Catégorie',
                'required' => true,
                'choices' => [
                    'Matériel' => 'materiel',
                    'Transport' => 'transport',
                    'Logistique' => 'logistique',
                    'Nourriture' => 'nourriture',
                    'Autre' => 'autre',
                ],
                'attr' => ['class' => 'w-full px-4 py-2 rounded bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-rankup-primary']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['class' => 'w-full px-4 py-2 rounded bg-gray-700 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-rankup-primary', 'rows' => 4]
            ])
            ->add('team', EntityType::class, [
                'label' => 'Équipe',
                'class' => Team::class,
                'choice_label' => 'name',
                'choices' => $managerTeams,
                'required' => true,
                'attr' => ['class' => 'w-full px-4 py-2 rounded bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-rankup-primary']
            ])
            ->add('factureFile', FileType::class, [
                'label' => 'Invoice/Receipt (PDF, Image)',
                'required' => false,
                'mapped' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'],
                        'mimeTypesMessage' => 'Please upload a valid PDF or image file',
                    ])
                ],
                'attr' => ['class' => 'form-control', 'accept' => '.pdf,.jpg,.jpeg,.png,.gif']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Depense::class,
            'manager_teams' => [],
        ]);
    }
}
