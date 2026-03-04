<?php

namespace App\Controller;

use App\Entity\Player;
use App\Entity\Reclamation;
use App\Entity\Notification;
use App\Enum\ReclamationStatus;
use App\Enum\ReclamationType;
use App\Form\ReclamationType as ReclamationTypeForm;
use App\Service\EmailService;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reclamation')]
#[IsGranted('ROLE_USER')]
final class ReclamationController extends AbstractController
{
    private EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    private function getAuthenticatedPlayerOrDeny(): Player
    {
        $user = $this->getUser();

        if (!$user instanceof Player) {
            throw $this->createAccessDeniedException('Compte joueur requis.');
        }

        return $user;
    }

    #[Route('/home', name: 'app_reclamation_index', methods: ['GET'])]
    public function index(ReclamationRepository $reclamationRepository): Response
    {
        $reclamation = new Reclamation();
        $reclamation->setType(ReclamationType::JOUEUR);
        $reclamation->setEtat(ReclamationStatus::EN_COURS);

        $form = $this->createForm(ReclamationTypeForm::class, $reclamation, [
            'action' => $this->generateUrl('app_reclamation_new_player'),
            'method' => 'POST'
        ]);

        $reclamations = $this->isGranted('ROLE_ADMIN')
            ? $reclamationRepository->findBy([], ['createdAt' => 'DESC'])
            : $reclamationRepository->findBy(['player' => $this->getAuthenticatedPlayerOrDeny()], ['createdAt' => 'DESC']);

        return $this->render('reclamation/index.html.twig', [
            'reclamations' => $reclamations,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/new/simple', name: 'app_reclamation_new_simple', methods: ['POST'])]
    public function newSimple(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {

        try {

            $reclamation = new Reclamation();

            $reclamation->setTitre($request->request->get('titre'));
            $reclamation->setDescription($request->request->get('description'));
            $reclamation->setEtat(ReclamationStatus::EN_COURS);

            $simpleType = $request->request->get('type_simple');

            if ($simpleType === 'TECHNIQUE') {
                $reclamation->setType(ReclamationType::TECHNIQUE);
            } elseif ($simpleType === 'ORGANISATIONNELLE') {
                $reclamation->setType(ReclamationType::ORGANISATIONNELLE);
            }

            if ($this->isGranted('ROLE_MANAGER') || $this->isGranted('ROLE_USER')) {
                $reclamation->setPlayer($this->getAuthenticatedPlayerOrDeny());
            }

            if (method_exists($reclamation, 'setCreatedAt')) {
                $reclamation->setCreatedAt(new \DateTime());
            }

            $errors = $validator->validate($reclamation);
            if (count($errors) > 0) {
                $errs = [];
                foreach ($errors as $error) {
                    $prop = $error->getPropertyPath();
                    $field = $prop === 'type' ? 'type_simple' : $prop;
                    $errs[$field][] = $error->getMessage();
                }

                return $this->json([
                    'success' => false,
                    'errors' => $errs
                ], 400);
            }

            $entityManager->persist($reclamation);
            $entityManager->flush();

            $notif = new Notification();
            $notif->setTitle('Nouvelle réclamation #' . $reclamation->getId());
            $notif->setMessage($reclamation->getTitre());
            $notif->setReclamation($reclamation);
            $entityManager->persist($notif);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Réclamation ajoutée',
                'id' => $reclamation->getId()
            ]);

        } catch (\Throwable $e) {

            return $this->json([
                'success' => false,
                'debug' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/new/player', name: 'app_reclamation_new_player', methods: ['GET','POST'])]
    public function newPlayer(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reclamation = new Reclamation();
        $reclamation->setType(ReclamationType::JOUEUR);
        $reclamation->setEtat(ReclamationStatus::EN_COURS);
        $reclamation->setPlayer($this->getAuthenticatedPlayerOrDeny());

        $form = $this->createForm(ReclamationTypeForm::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (method_exists($reclamation, 'setCreatedAt')) {
                $reclamation->setCreatedAt(new \DateTime());
            }

            $uploadedFile = $form->get('attachment')->getData();
            if ($uploadedFile) {
                $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/reclamations';
                if (!is_dir($uploadsDir)) {
                    mkdir($uploadsDir, 0775, true);
                }
                $originalExt = $uploadedFile->guessExtension() ?: $uploadedFile->getClientOriginalExtension();
                $newFilename = uniqid('rec_', true) . '.' . $originalExt;
                $uploadedFile->move($uploadsDir, $newFilename);
                $reclamation->setAttachmentFilename($newFilename);
            }

            $entityManager->persist($reclamation);
            $entityManager->flush();

            $notif = new Notification();
            $notif->setTitle('Nouvelle réclamation #' . $reclamation->getId());
            $notif->setMessage($reclamation->getTitre());
            $notif->setReclamation($reclamation);
            $entityManager->persist($notif);
            $entityManager->flush();

            $this->addFlash('success', 'Réclamation ajoutée avec succès');

            return $this->redirectToRoute('app_reclamation_index');
        }

        return $this->render('reclamation/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_reclamation_show', methods: ['GET'])]
    public function show(Reclamation $reclamation): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && $reclamation->getPlayer() !== $this->getAuthenticatedPlayerOrDeny()) {
            throw $this->createAccessDeniedException('Accès non autorisé.');
        }

        return $this->render('reclamation/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'app_reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(ReclamationTypeForm::class, $reclamation);
        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {
            if ($form->isSubmitted() && $form->isValid()) {
                if (method_exists($reclamation, 'setUpdatedAt')) {
                    $reclamation->setUpdatedAt(new \DateTime());
                }

                $entityManager->flush();

                return $this->json([
                    'success' => true,
                    'message' => 'Réclamation modifiée avec succès',
                    'id' => $reclamation->getId(),
                    'titre' => $reclamation->getTitre(),
                    'etat'  => $reclamation->getEtat()->value,
                    'adminResponse' => $reclamation->getAdminResponse() ?? '',
                    'updatedAt' => $reclamation->getUpdatedAt()?->format('d M Y H:i'),
                ]);
            }

            if ($form->isSubmitted()) {
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[] = $error->getMessage();
                }
                return $this->json([
                    'success' => false,
                    'errors' => $errors
                ], 400);
            }

            return $this->json([
                'success' => true,
                'data' => [
                    'titre' => $reclamation->getTitre(),
                    'description' => $reclamation->getDescription(),
                    'type' => $reclamation->getType()->value,
                    'etat' => $reclamation->getEtat()->value,
                    'createdAt' => $reclamation->getCreatedAt()?->format('d M Y H:i'),
                    'updatedAt' => $reclamation->getUpdatedAt()?->format('d M Y H:i'),
                    'playerId' => $reclamation->getPlayer()?->getPlayerId() ?? '',
                    'adminResponse' => $reclamation->getAdminResponse() ?? '',
                ]
            ]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            if (method_exists($reclamation, 'setUpdatedAt')) {
                $reclamation->setUpdatedAt(new \DateTime());
            }
            $entityManager->flush();
            $this->addFlash('success', 'Réclamation modifiée');
            return $this->redirectToRoute('app_reclamation_index');
        }

        return $this->render('reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $token = $request->request->get('_token');

        if (!$this->isCsrfTokenValid('delete' . $reclamation->getId(), $token)) {
            $this->addFlash('error', 'Token CSRF invalide');
            return $this->redirectToRoute('app_reclamation_index');
        }

        $entityManager->remove($reclamation);
        $entityManager->flush();

        $this->addFlash('success', 'Réclamation supprimée avec succès');

        return $this->redirectToRoute('app_reclamation_index');
    }

    #[Route('/my-responses', name: 'app_reclamation_my_responses', methods: ['GET'])]
    public function myResponses(ReclamationRepository $reclamationRepository): Response
    {
        $player = $this->getAuthenticatedPlayerOrDeny();

        $reclamations = $reclamationRepository->createQueryBuilder('r')
            ->innerJoin('r.adminResponse', 'ar')
            ->addSelect('ar')
            ->where('r.player = :player')
            ->setParameter('player', $player)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('reclamation/my_responses.html.twig', [
            'reclamations' => $reclamations,
        ]);
    }
}
