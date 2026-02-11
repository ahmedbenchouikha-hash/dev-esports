<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\UserEditType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/users')]
class UserController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/', name: 'user_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(Request $request): Response
    {
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'username');

        $qb = $this->em->getRepository(User::class)->createQueryBuilder('u');

        if ($search) {
            $qb->andWhere('u.username LIKE :search OR u.email LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Add sorting
        if ($sort === 'email') {
            $qb->orderBy('u.email', 'ASC');
        } elseif ($sort === 'id') {
            $qb->orderBy('u.id', 'ASC');
        } else {
            $qb->orderBy('u.username', 'ASC');
        }

        $users = $qb->getQuery()->getResult();

        return $this->render('user/index.html.twig', [
            'users' => $users,
            'search' => $search,
            'sort' => $sort,
        ]);
    }

    #[Route('/new', name: 'user_new', methods: ['GET','POST'])]
    public function new(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        // allow ADMIN to create users; normal users can sign-up via register action
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // hash password (plainPassword mapped in form)
            $plain = $form->get('plainPassword')->getData();
            if ($plain) {
                $user->setPassword($passwordHasher->hashPassword($user, $plain));
            }

            // handle profile upload
            $profile = $form->get('profileFile')->getData();
            if ($profile) {
                $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/profiles';
                if (!is_dir($uploadsDir)) {
                    mkdir($uploadsDir, 0755, true);
                }
                $newName = uniqid() . '-' . preg_replace('/[^a-z0-9.\-_]/i', '', $profile->getClientOriginalName());
                $profile->move($uploadsDir, $newName);
                // optional: set filename to the entity if you add a field
            }

            $this->em->persist($user);
            $this->em->flush();

            $this->addFlash('success', 'User created.');

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/{id}', name: 'user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        // allow users to view their own profile or admins
        if ($this->getUser() !== $user) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        }

        return $this->render('user/show.html.twig', ['user' => $user]);
    }

    #[Route('/{id}/edit', name: 'user_edit', methods: ['GET','POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, User $user, UserPasswordHasherInterface $passwordHasher): Response
    {
        // allow owner or admin
        if ($this->getUser() !== $user) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        }

        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // handle password change if provided
            $plain = $form->get('plainPassword')->getData();
            if ($plain) {
                $user->setPassword($passwordHasher->hashPassword($user, $plain));
            }

            $profile = $form->get('profileFile')->getData();
            if ($profile) {
                $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/profiles';
                if (!is_dir($uploadsDir)) {
                    mkdir($uploadsDir, 0755, true);
                }
                $newName = uniqid() . '-' . preg_replace('/[^a-z0-9.\-_]/i', '', $profile->getClientOriginalName());
                $profile->move($uploadsDir, $newName);
                // Store the filename in the user entity
                $user->setConfirmationFile($newName);
            }

            $this->em->flush();
            $this->addFlash('success', 'Profile updated.');

            return $this->redirectToRoute('user_show', ['id' => $user->getId()]);
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }

    #[Route('/{id}/delete', name: 'user_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, User $user): Response
    {
        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete-user-' . $user->getId(), $token)) {
            $this->em->remove($user);
            $this->em->flush();
            $this->addFlash('success', 'User deleted.');
        } else {
            $this->addFlash('error', 'Invalid CSRF token.');
        }

        return $this->redirectToRoute('user_index');
    }
}
