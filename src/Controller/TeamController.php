<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\TeamType;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/teams', name: 'team_')]
class TeamController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(TeamRepository $teamRepository, Request $request): Response
    {
        $search = $request->query->get('search', '');
        $game = $request->query->get('game', '');
        $level = $request->query->get('level', '');
        $status = $request->query->get('status', '');
        $country = $request->query->get('country', '');
        $sortBy = $request->query->get('sort', 'name');
        $direction = $request->query->get('direction', 'ASC');

        if ($search || $game || $level || $status || $country) {
            $teams = $teamRepository->searchAdvanced($search, $game, $level, $status, $country, $sortBy, $direction);
        } else {
            $teams = $teamRepository->findAllOrdered($sortBy, $direction);
        }

        $totalTeams = $teamRepository->count([]);
        $pendingCount = $teamRepository->countByStatus('en attente');
        $approvedCount = $teamRepository->countByStatus('approuvé');
        $refusedCount = $teamRepository->countByStatus('refusé');

        $availableGames = ['LoL', 'CS:GO', 'Dota 2', 'FIFA'];
        $availableLevels = ['Débutant', 'Intermédiaire', 'Pro'];

        return $this->render('team/index.html.twig', [
            'teams' => $teams,
            'search' => $search,
            'game' => $game,
            'level' => $level,
            'status' => $status,
            'country' => $country,
            'sortBy' => $sortBy,
            'direction' => $direction,
            'totalTeams' => $totalTeams,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'refusedCount' => $refusedCount,
            'availableGames' => $availableGames,
            'availableLevels' => $availableLevels,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id, TeamRepository $teamRepository): Response
    {
        $team = $teamRepository->find($id);

        if (!$team) {
            throw $this->createNotFoundException('Team not found');
        }

        return $this->render('team/show.html.twig', [
            'team' => $team,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): Response
    {
        $team = new Team();
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // STEP 1: Validate using Symfony's form validation
            if (!$form->isValid()) {
                foreach ($form->getErrors(true) as $error) {
                    $this->addFlash('error', '❌ ' . $error->getMessage());
                }
                return $this->render('team/new.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            // STEP 2: Custom server-side validation
            $validationErrors = [];

            // REQUIRED: Team Name
            $name = $team->getName();
            if ($name === null || $name === '') {
                $validationErrors[] = '❌ Team name is REQUIRED and cannot be empty';
            } else {
                $name = trim($name);
                if ($name === '') {
                    $validationErrors[] = '❌ Team name is REQUIRED - Cannot be only spaces';
                } elseif (strlen($name) < 2) {
                    $validationErrors[] = '❌ Team name must be at least 2 characters long';
                } elseif (strlen($name) > 255) {
                    $validationErrors[] = '❌ Team name must not exceed 255 characters';
                } elseif (!preg_match('/^[a-zA-Z0-9éèêëàâäæîïôõöœùûüçñ\s\-\.]+$/i', $name)) {
                    $validationErrors[] = '❌ Team name contains invalid characters (only letters, numbers, spaces, hyphens allowed)';
                }
            }

            // OPTIONAL: Country validation
            if (!empty($team->getCountry())) {
                $country = trim($team->getCountry());
                if (strlen($country) > 255) {
                    $validationErrors[] = '❌ Country must not exceed 255 characters';
                }
            }

            // OPTIONAL: Description validation
            if (!empty($team->getDescription())) {
                $desc = trim($team->getDescription());
                if (strlen($desc) > 1000) {
                    $validationErrors[] = '❌ Description must not exceed 1000 characters';
                }
            }

            // OPTIONAL: Detailed Description validation
            if (!empty($team->getDetailedDescription())) {
                $detailedDesc = trim($team->getDetailedDescription());
                if (strlen($detailedDesc) > 2000) {
                    $validationErrors[] = '❌ Detailed description must not exceed 2000 characters';
                }
            }

            // OPTIONAL: Color validation
            if (!empty($team->getCouleurEquipe())) {
                $color = trim($team->getCouleurEquipe());
                if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/i', $color)) {
                    $validationErrors[] = '❌ Invalid color format. Use hex format like #FF0000 or #FFF';
                }
            }

            // OPTIONAL: Game validation
            if (!empty($team->getJeu())) {
                $validGames = ['LoL', 'CS:GO', 'Dota 2', 'FIFA'];
                if (!in_array($team->getJeu(), $validGames)) {
                    $validationErrors[] = '❌ Invalid game. Choose from: LoL, CS:GO, Dota 2, FIFA';
                }
            }

            // OPTIONAL: Level validation
            if (!empty($team->getNiveau())) {
                $validLevels = ['Débutant', 'Intermédiaire', 'Pro'];
                if (!in_array($team->getNiveau(), $validLevels)) {
                    $validationErrors[] = '❌ Invalid level. Choose from: Débutant, Intermédiaire, Pro';
                }
            }

            // OPTIONAL: Captain ID validation
            if ($team->getCaptainId() !== null) {
                if (!is_int($team->getCaptainId()) || $team->getCaptainId() <= 0) {
                    $validationErrors[] = '❌ Captain ID must be a positive number';
                }
            }

            // If validation errors, stop and display them
            if (count($validationErrors) > 0) {
                foreach ($validationErrors as $error) {
                    $this->addFlash('error', $error);
                }
                return $this->render('team/new.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            try {
                // STEP 3: Handle logo upload
                $file = $form->get('logo')->getData();
                if ($file) {
                    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
                    if (!in_array($file->getMimeType(), $allowedMimes)) {
                        $this->addFlash('error', '❌ Invalid image type. Only JPG, PNG, GIF are allowed.');
                        return $this->render('team/new.html.twig', [
                            'form' => $form->createView(),
                        ]);
                    }

                    if ($file->getSize() > 1048576) {
                        $this->addFlash('error', '❌ File size must not exceed 1MB.');
                        return $this->render('team/new.html.twig', [
                            'form' => $form->createView(),
                        ]);
                    }

                    $filename = uniqid('logo_') . '.' . $file->guessExtension();
                    $file->move($this->getParameter('logos_directory'), $filename);
                    $team->setLogo($filename);
                }

                // STEP 4: Handle members
                $membersString = $form->get('membres')->getData();
                if ($membersString !== null && $membersString !== '') {
                    $membersString = trim($membersString);
                    if (!empty($membersString)) {
                        $membersArray = array_filter(array_map('trim', explode(',', $membersString)));
                        
                        if (count($membersArray) > 50) {
                            $this->addFlash('error', '❌ Team cannot have more than 50 members.');
                            return $this->render('team/new.html.twig', [
                                'form' => $form->createView(),
                            ]);
                        }

                        foreach ($membersArray as $member) {
                            if (strlen($member) < 2 || strlen($member) > 100) {
                                $this->addFlash('error', '❌ Member name must be between 2 and 100 characters.');
                                return $this->render('team/new.html.twig', [
                                    'form' => $form->createView(),
                                ]);
                            }
                        }
                        $team->setMembres($membersArray);
                    } else {
                        $team->setMembres([]);
                    }
                } else {
                    $team->setMembres([]);
                }

                // STEP 5: Final validation with Symfony Validator
                $errors = $validator->validate($team);
                if (count($errors) > 0) {
                    foreach ($errors as $error) {
                        $this->addFlash('error', '❌ ' . $error->getPropertyPath() . ': ' . $error->getMessage());
                    }
                    return $this->render('team/new.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }

                // STEP 6: Save to database
                $em->persist($team);
                $em->flush();

                $this->addFlash('success', '✅ Team created successfully!');
                return $this->redirectToRoute('team_index');
                
            } catch (\Exception $e) {
                $this->addFlash('error', '❌ An error occurred while creating the team: ' . $e->getMessage());
                return $this->render('team/new.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
        }

        return $this->render('team/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Team $team, EntityManagerInterface $em, ValidatorInterface $validator): Response
    {
        $form = $this->createForm(TeamType::class, $team);

        if ($team->getMembres()) {
            $form->get('membres')->setData(implode(', ', $team->getMembres()));
        }

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // STEP 1: Validate using Symfony's form validation
            if (!$form->isValid()) {
                foreach ($form->getErrors(true) as $error) {
                    $this->addFlash('error', '❌ ' . $error->getMessage());
                }
                return $this->render('team/edit.html.twig', [
                    'form' => $form->createView(),
                    'team' => $team
                ]);
            }

            // STEP 2: Custom server-side validation
            $validationErrors = [];

            // REQUIRED: Team Name
            $name = $team->getName();
            if ($name === null || $name === '') {
                $validationErrors[] = '❌ Team name is REQUIRED and cannot be empty';
            } else {
                $name = trim($name);
                if ($name === '') {
                    $validationErrors[] = '❌ Team name is REQUIRED - Cannot be only spaces';
                } elseif (strlen($name) < 2) {
                    $validationErrors[] = '❌ Team name must be at least 2 characters long';
                } elseif (strlen($name) > 255) {
                    $validationErrors[] = '❌ Team name must not exceed 255 characters';
                } elseif (!preg_match('/^[a-zA-Z0-9éèêëàâäæîïôõöœùûüçñ\s\-\.]+$/i', $name)) {
                    $validationErrors[] = '❌ Team name contains invalid characters';
                }
            }

            // OPTIONAL: Country validation
            if (!empty($team->getCountry())) {
                $country = trim($team->getCountry());
                if (strlen($country) > 255) {
                    $validationErrors[] = '❌ Country must not exceed 255 characters';
                }
            }

            // OPTIONAL: Description validation
            if (!empty($team->getDescription())) {
                $desc = trim($team->getDescription());
                if (strlen($desc) > 1000) {
                    $validationErrors[] = '❌ Description must not exceed 1000 characters';
                }
            }

            // OPTIONAL: Detailed Description validation
            if (!empty($team->getDetailedDescription())) {
                $detailedDesc = trim($team->getDetailedDescription());
                if (strlen($detailedDesc) > 2000) {
                    $validationErrors[] = '❌ Detailed description must not exceed 2000 characters';
                }
            }

            // OPTIONAL: Color validation
            if (!empty($team->getCouleurEquipe())) {
                $color = trim($team->getCouleurEquipe());
                if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/i', $color)) {
                    $validationErrors[] = '❌ Invalid color format. Use hex format like #FF0000 or #FFF';
                }
            }

            // OPTIONAL: Game validation
            if (!empty($team->getJeu())) {
                $validGames = ['LoL', 'CS:GO', 'Dota 2', 'FIFA'];
                if (!in_array($team->getJeu(), $validGames)) {
                    $validationErrors[] = '❌ Invalid game. Choose from: LoL, CS:GO, Dota 2, FIFA';
                }
            }

            // OPTIONAL: Level validation
            if (!empty($team->getNiveau())) {
                $validLevels = ['Débutant', 'Intermédiaire', 'Pro'];
                if (!in_array($team->getNiveau(), $validLevels)) {
                    $validationErrors[] = '❌ Invalid level. Choose from: Débutant, Intermédiaire, Pro';
                }
            }

            // OPTIONAL: Captain ID validation
            if ($team->getCaptainId() !== null) {
                if (!is_int($team->getCaptainId()) || $team->getCaptainId() <= 0) {
                    $validationErrors[] = '❌ Captain ID must be a positive number';
                }
            }

            if (count($validationErrors) > 0) {
                foreach ($validationErrors as $error) {
                    $this->addFlash('error', $error);
                }
                return $this->render('team/edit.html.twig', [
                    'form' => $form->createView(),
                    'team' => $team
                ]);
            }

            try {
                // STEP 3: Handle logo upload
                $file = $form->get('logo')->getData();
                if ($file) {
                    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
                    if (!in_array($file->getMimeType(), $allowedMimes)) {
                        $this->addFlash('error', '❌ Invalid image type. Only JPG, PNG, GIF are allowed.');
                        return $this->render('team/edit.html.twig', [
                            'form' => $form->createView(),
                            'team' => $team
                        ]);
                    }

                    if ($file->getSize() > 1048576) {
                        $this->addFlash('error', '❌ File size must not exceed 1MB.');
                        return $this->render('team/edit.html.twig', [
                            'form' => $form->createView(),
                            'team' => $team
                        ]);
                    }

                    $filename = uniqid('logo_') . '.' . $file->guessExtension();
                    $file->move($this->getParameter('logos_directory'), $filename);
                    $team->setLogo($filename);
                }

                // STEP 4: Handle members
                $membersString = $form->get('membres')->getData();
                if ($membersString !== null && $membersString !== '') {
                    $membersString = trim($membersString);
                    if (!empty($membersString)) {
                        $membersArray = array_filter(array_map('trim', explode(',', $membersString)));
                        
                        if (count($membersArray) > 50) {
                            $this->addFlash('error', '❌ Team cannot have more than 50 members.');
                            return $this->render('team/edit.html.twig', [
                                'form' => $form->createView(),
                                'team' => $team
                            ]);
                        }

                        foreach ($membersArray as $member) {
                            if (strlen($member) < 2 || strlen($member) > 100) {
                                $this->addFlash('error', '❌ Member name must be between 2 and 100 characters.');
                                return $this->render('team/edit.html.twig', [
                                    'form' => $form->createView(),
                                    'team' => $team
                                ]);
                            }
                        }
                        $team->setMembres($membresArray);
                    } else {
                        $team->setMembres([]);
                    }
                } else {
                    $team->setMembres([]);
                }

                // STEP 5: Final validation with Symfony Validator
                $errors = $validator->validate($team);
                if (count($errors) > 0) {
                    foreach ($errors as $error) {
                        $this->addFlash('error', '❌ ' . $error->getPropertyPath() . ': ' . $error->getMessage());
                    }
                    return $this->render('team/edit.html.twig', [
                        'form' => $form->createView(),
                        'team' => $team
                    ]);
                }

                // STEP 6: Save to database
                $team->setUpdatedAt(new \DateTime());
                $em->flush();

                $this->addFlash('success', '✅ Team updated successfully!');
                return $this->redirectToRoute('team_index');
                
            } catch (\Exception $e) {
                $this->addFlash('error', '❌ An error occurred while updating the team: ' . $e->getMessage());
                return $this->render('team/edit.html.twig', [
                    'form' => $form->createView(),
                    'team' => $team
                ]);
            }
        }

        return $this->render('team/edit.html.twig', [
            'form' => $form->createView(),
            'team' => $team
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Team $team, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $team->getId(), $request->request->get('_token'))) {
            try {
                $em->remove($team);
                $em->flush();
                $this->addFlash('success', '✅ Team deleted successfully!');
            } catch (\Exception $e) {
                $this->addFlash('error', '❌ An error occurred while deleting the team: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', '❌ Invalid CSRF token');
        }
        return $this->redirectToRoute('team_index');
    }

    #[Route('/{id}/approve', name: 'approve', methods: ['POST'])]
    public function approve(Request $request, Team $team, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('approve' . $team->getId(), $request->request->get('_token'))) {
            try {
                $team->setStatut('approuvé');
                $team->setDateValidation(new \DateTime());
                $em->flush();
                $this->addFlash('success', '✅ Team approved!');
            } catch (\Exception $e) {
                $this->addFlash('error', '❌ An error occurred: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', '❌ Invalid CSRF token');
        }
        return $this->redirectToRoute('team_index');
    }

    #[Route('/{id}/refuse', name: 'refuse', methods: ['POST'])]
    public function refuse(Request $request, Team $team, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('refuse' . $team->getId(), $request->request->get('_token'))) {
            try {
                $team->setStatut('refusé');
                $team->setDateValidation(new \DateTime());
                $em->flush();
                $this->addFlash('success', '✅ Team refused!');
            } catch (\Exception $e) {
                $this->addFlash('error', '❌ An error occurred: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', '❌ Invalid CSRF token');
        }
        return $this->redirectToRoute('team_index');
    }
}
