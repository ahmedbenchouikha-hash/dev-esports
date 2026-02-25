<?php

namespace App\Form;

use App\Entity\Tournament;
use Symfony\Component\Form\AbstractType;
<<<<<<< HEAD
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
=======
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType as FormTextareaType;
use Symfony\Component\Form\CallbackTransformer;
>>>>>>> 1c04895fd40ddf3e3d0493c052d9fac6b47ed96e

class TournamentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Tournament Name',
<<<<<<< HEAD
                'required' => true,
                'attr' => [
                    'placeholder' => 'Enter tournament name',
                    'class' => 'form-control'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Tournament description',
                    'class' => 'form-control',
                    'rows' => 5
                ]
            ])
            ->add('startDate', DateTimeType::class, [
                'label' => 'Start Date & Time',
                'required' => true,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                    'type' => 'datetime-local'
                ]
            ])
            ->add('endDate', DateTimeType::class, [
                'label' => 'End Date & Time',
                'required' => true,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                    'type' => 'datetime-local'
                ]
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'required' => true,
=======
                'attr' => ['class' => 'form-control'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control', 'rows' => 4],
                'required' => false,
            ])
            ->add('startDate', DateTimeType::class, [
                'label' => 'Start Date',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('endDate', DateTimeType::class, [
                'label' => 'End Date',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
>>>>>>> 1c04895fd40ddf3e3d0493c052d9fac6b47ed96e
                'choices' => [
                    'Pending' => 'pending',
                    'Ongoing' => 'ongoing',
                    'Completed' => 'completed',
<<<<<<< HEAD
                    'Cancelled' => 'cancelled'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('location', TextType::class, [
                'label' => 'Location',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Tournament location',
                    'class' => 'form-control'
                ]
            ])
            ->add('prizePool', MoneyType::class, [
                'label' => 'Prize Pool',
                'required' => false,
                'currency' => 'USD',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
        ;
=======
                    'Cancelled' => 'cancelled',
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('location', TextType::class, [
                'label' => 'Location',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'City, Country'],
            ])
            ->add('prizePool', NumberType::class, [
                'label' => 'Prize Pool (USD)',
                'required' => false,
                'scale' => 2,
                'attr' => ['class' => 'form-control', 'min' => 0, 'step' => '0.01'],
            ])
            ->add('rules', FormTextareaType::class, [
                'label' => 'Rules',
                'required' => false,
                'mapped' => true,
                'attr' => ['class' => 'form-control', 'rows' => 6, 'placeholder' => "One rule per line"]
            ]);

        // transform between array (entity) and newline-separated string (form)
        $builder->get('rules')->addModelTransformer(new CallbackTransformer(
            function ($rulesArray) {
                if (is_array($rulesArray)) {
                    return implode("\n", $rulesArray);
                }
                return '';
            },
            function ($rulesString) {
                if ($rulesString === null || $rulesString === '') {
                    return null;
                }
                // split on CRLF/CR/LF and remove empty lines
                $lines = preg_split('/\r\n|\r|\n/', $rulesString);
                $clean = array_values(array_filter(array_map('trim', $lines), fn($v) => $v !== ''));
                return $clean === [] ? null : $clean;
            }
        ));
        
>>>>>>> 1c04895fd40ddf3e3d0493c052d9fac6b47ed96e
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tournament::class,
        ]);
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> 1c04895fd40ddf3e3d0493c052d9fac6b47ed96e
