<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\AtLeastOneOf;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter an email',
                    ]),
                    new Email([
                        'message' => 'The email "{{ value }}" is not a valid email.',
                    ]),
                ],
                'attr' => ['autocomplete' => 'email'],
            ])
            ->add('username', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a username',
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Your username should be at least {{ limit }} characters',
                        'max' => 180,
                    ]),
                ],
                'label' => 'Username/Nickname',
            ])
            ->add('firstName', TextType::class, [
                'required' => false,
                'label' => 'First Name',
            ])
            ->add('lastName', TextType::class, [
                'required' => false,
                'label' => 'Last Name',
            ])
            ->add('birthDate', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'label' => 'Birth Date',
            ])
            ->add('userRole', ChoiceType::class, [
                'mapped' => false,
                'choices' => [
                    'Player' => 'ROLE_USER',
                    'Admin' => 'ROLE_ADMIN',
                ],
                'label' => 'Select Your Role',
                'expanded' => true,
                'multiple' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please select a role',
                    ]),
                ],
            ])
            ->add('verificationFile', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Verification Document (Player Only)',
                'help' => 'Upload ID, passport, or gaming license to verify you are a player',
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'application/pdf',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PDF, JPG, or PNG file',
                    ]),
                ],
                'attr' => ['accept' => '.pdf,.jpg,.jpeg,.png'],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Please enter a password',
                        ]),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'Your password should be at least {{ limit }} characters',
                            'max' => 4096,
                        ]),
                        new Regex([
                            'pattern' => '/[A-Z]/',
                            'message' => 'Password must contain at least one uppercase letter',
                        ]),
                        new Regex([
                            'pattern' => '/[0-9]/',
                            'message' => 'Password must contain at least one number',
                        ]),
                    ],
                    'attr' => ['autocomplete' => 'new-password'],
                    'label' => 'Password',
                ],
                'second_options' => [
                    'attr' => ['autocomplete' => 'new-password'],
                    'label' => 'Repeat Password',
                ],
                'invalid_message' => 'The password fields must match.',
            ])
            ->add('passwordConfirm', PasswordType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Confirm Password',
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to the terms.',
                    ]),
                ],
                'label' => 'I agree to the Terms of Service',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
