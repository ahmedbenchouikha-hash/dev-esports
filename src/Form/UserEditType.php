<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\User;

class UserEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please enter a username.']),
                    new Assert\Length(['min' => 3, 'max' => 50]),
                ],
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please enter an email address.']),
                    new Assert\Email(['message' => 'Please enter a valid email address.']),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => false,
                'first_options' => ['label' => 'New password'],
                'second_options' => ['label' => 'Confirm new password'],
                'invalid_message' => 'The password fields must match.',
                'constraints' => [
                    new Assert\Length(['min' => 8]),
                ],
            ])
            ->add('profileFile', FileType::class, [
                'label' => 'Profile Document (Player Confirmation)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Assert\File(['maxSize' => '5M', 'mimeTypes' => ['image/jpeg', 'image/png', 'application/pdf']])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => User::class, 'csrf_protection' => true]);
    }
}