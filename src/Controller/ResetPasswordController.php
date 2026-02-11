<?php

namespace App\Controller;

use App\Entity\PasswordResetToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ResetPasswordController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em, private readonly UserPasswordHasherInterface $hasher)
    {
    }

    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function reset(Request $request, string $token): Response
    {
        $repo = $this->em->getRepository(PasswordResetToken::class);
        $tokenEntity = $repo->findOneBy(['token' => $token]);

        if (! $tokenEntity) {
            $this->addFlash('error', 'Invalid or expired token.');
            return $this->redirectToRoute('app_login');
        }

        if ($tokenEntity->getExpiresAt() < new \DateTimeImmutable()) {
            $this->addFlash('error', 'Token expired.');
            return $this->redirectToRoute('app_forgot_password_request');
        }

        $user = $tokenEntity->getUser();

        $form = $this->createFormBuilder()
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'New password'],
                'second_options' => ['label' => 'Repeat password'],
                'constraints' => [new NotBlank(), new Length(['min' => 8])],
                'mapped' => false,
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plain = $form->get('plainPassword')->getData();
            $hashed = $this->hasher->hashPassword($user, $plain);
            $user->setPassword($hashed);

            // remove all tokens for this user
            $tokens = $this->em->getRepository(PasswordResetToken::class)->findBy(['user' => $user]);
            foreach ($tokens as $t) {
                $this->em->remove($t);
            }

            $this->em->persist($user);
            $this->em->flush();

            $this->addFlash('success', 'Password updated. You may now sign in.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', ['resetForm' => $form->createView()]);
    }
}
