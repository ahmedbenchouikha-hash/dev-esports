<?php

namespace App\Form;

use App\Entity\Budget;
use App\Entity\Team;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class BudgetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $teams = $options['teams'] ?? [];
        
        $builder
            ->add('montantAlloue', MoneyType::class, [
                'label' => 'Allocated Amount (€) *',
                'currency' => 'EUR',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '0.00 €',
                    'min' => '0.01',
                    'step' => '0.01'
                ],
            ])
            ->add('team', EntityType::class, [
                'class' => Team::class,
                'choices' => $teams,
                'choice_label' => 'name',
                'placeholder' => 'Select a team',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes (optional)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Add notes about this budget...'
                ],
            ])
            ->add('justificatifFile', FileType::class, [
                'label' => 'Justification Document (PDF, Image)',
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
            'data_class' => Budget::class,
            'teams' => [],
        ]);
    }
}
