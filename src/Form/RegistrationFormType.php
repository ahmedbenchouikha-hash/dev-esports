<?php

namespace App\Form;

<<<<<<< HEAD
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
=======
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\User;
>>>>>>> module-user

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
<<<<<<< HEAD
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
=======
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
            ->add('typeUser', ChoiceType::class, [
                'choices' => [
                    'ADMIN' => 'ADMIN',
                    'USER' => 'USER',
                ],
                'constraints' => [new Assert\NotBlank(['message' => 'Please choose a user type.']), new Assert\Choice(['choices' => ['ADMIN', 'USER'], 'message' => 'Choose a valid user type.'])],
            ])
            ->add('profileFile', FileType::class, [
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
>>>>>>> module-user
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
<<<<<<< HEAD
=======
            'csrf_protection' => true,
>>>>>>> module-user
        ]);
    }
}
