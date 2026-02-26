<?php

namespace App\Form;

use App\Entity\Game;
use App\Entity\Ticket;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class TicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('game', EntityType::class, [
                'class' => Game::class,
                'choice_label' => fn(Game $game) => sprintf(
                    '%s vs %s (ID: %d)',
                    $game->getTeam1()?->getName(),
                    $game->getTeam2()?->getName(),
                    $game->getId()
                ),
                'label' => 'Match',
                'placeholder' => 'Select a match',
                'attr' => [
                    'class' => 'form-select form-select-dark',
                    'aria-label' => 'Select a match'
                ],
                'constraints' => [
                    new Assert\NotNull(message: 'Please select a match'),
                ]
            ])
            ->add('ticketNumber', TextType::class, [
                'label' => 'Ticket Number',
                'attr' => [
                    'class' => 'form-control form-control-dark',
                    'placeholder' => 'e.g., TKT-001, VIP-2026',
                    'maxlength' => 100,
                    'aria-label' => 'Unique ticket number'
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Ticket number is required'),
                    new Assert\Length(max: 100, maxMessage: 'Ticket number cannot exceed 100 characters'),
                ]
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Ticket Type',
                'choices' => [
                    'Regular' => 'regular',
                    'VIP' => 'vip',
                    'Student' => 'student'
                ],
                'placeholder' => 'Select ticket type',
                'attr' => [
                    'class' => 'form-select form-select-dark',
                    'aria-label' => 'Ticket type'
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Please select a ticket type'),
                ]
            ])
            ->add('price', NumberType::class, [
                'label' => 'Price (€)',
                'attr' => [
                    'min' => 0,
                    'step' => 0.01,
                    'class' => 'form-control form-control-dark',
                    'placeholder' => 'Enter price',
                    'aria-label' => 'Ticket price'
                ],
                'constraints' => [
                    new Assert\NotNull(message: 'Price is required'),
                    new Assert\GreaterThanOrEqual(value: 0, message: 'Price must be 0 or higher'),
                ]
            ])
            ->add('quantity', IntegerType::class, [
                'label' => 'Total Quantity',
                'attr' => [
                    'min' => 1,
                    'max' => 10000,
                    'class' => 'form-control form-control-dark',
                    'placeholder' => 'Enter total number of tickets',
                    'aria-label' => 'Total ticket quantity'
                ],
                'constraints' => [
                    new Assert\NotNull(message: 'Quantity is required'),
                    new Assert\GreaterThan(value: 0, message: 'Quantity must be greater than 0'),
                ]
            ])
            ->add('sold', IntegerType::class, [
                'label' => 'Sold',
                'attr' => [
                    'min' => 0,
                    'max' => 10000,
                    'class' => 'form-control form-control-dark',
                    'placeholder' => 'Enter number of tickets sold',
                    'aria-label' => 'Number of tickets sold'
                ],
                'constraints' => [
                    new Assert\GreaterThanOrEqual(value: 0, message: 'Sold must be 0 or higher'),
                ]
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'Available' => 'available',
                    'Sold Out' => 'sold_out',
                    'Cancelled' => 'cancelled'
                ],
                'attr' => [
                    'class' => 'form-select form-select-dark',
                    'aria-label' => 'Ticket status'
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Please select a status'),
                ]
            ])
        ;

        // Populate form fields when displaying an existing ticket
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();
            
            if ($data && $data->getGame()) {
                $form->get('game')->setData($data->getGame());
            }
        });

        // Manually set game after form validation and submission
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();
            
            if ($form->has('game') && $form->get('game')->getData()) {
                $data->setGame($form->get('game')->getData());
            }
            
            $event->setData($data);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
        ]);
    }
}
