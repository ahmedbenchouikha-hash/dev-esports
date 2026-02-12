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

class BudgetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Budget::class,
        ]);
    }
}
