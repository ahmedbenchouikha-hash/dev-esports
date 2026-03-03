<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\User;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please enter a username.']),
                    new Assert\Length(['min' => 3, 'max' => 50, 'minMessage' => 'Username must be at least {{ limit }} characters.', 'maxMessage' => 'Username cannot be longer than {{ limit }} characters.']),
                ],
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please enter an email address.']),
                    new Assert\Email(['message' => 'Please enter a valid email address.']),
                    new Assert\Length(['max' => 180]),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => ['label' => 'Password'],
                'second_options' => ['label' => 'Confirm Password'],
                'invalid_message' => 'The password fields must match.',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please enter a password.']),
                    new Assert\Length(['min' => 8, 'minMessage' => 'Password must be at least {{ limit }} characters.']),
                ],
            ])
            ->add('confirmationFile', FileType::class, [
                'label' => 'Player Confirmation (Send document to admin for verification)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Assert\File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'application/pdf'],
                        'mimeTypesMessage' => 'Please upload a valid JPEG, PNG image or PDF.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => true,
        ]);
    }
}
