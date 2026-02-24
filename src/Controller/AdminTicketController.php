<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Form\TicketType;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/tickets')]
#[IsGranted('ROLE_ADMIN')]
class AdminTicketController extends AbstractController
{
    #[Route('', name: 'admin_ticket_index', methods: ['GET'])]
    public function index(TicketRepository $repository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 15;

        // Get filter parameters and convert empty strings to null
        $search = $request->query->get('search') ?: null;
        $gameId = $request->query->get('gameId') ?: null;
        $status = $request->query->get('status') ?: null;

        // Build query with filters
        $query = $repository->searchAndFilter($search, $gameId, $status)->getQuery();
        $allTickets = $query->getResult();
        $total = count($allTickets);
        $maxPages = ceil($total / $limit);

        if ($page > $maxPages && $maxPages > 0) {
            $page = $maxPages;
        }

        $offset = ($page - 1) * $limit;
        $tickets = array_slice($allTickets, $offset, $limit);

        return $this->render('admin/ticket/index.html.twig', [
            'tickets' => $tickets,
            'current_page' => $page,
            'max_pages' => $maxPages,
            'total' => $total,
            'search' => $search,
            'gameId' => $gameId,
            'status' => $status,
        ]);
    }

    #[Route('/create', name: 'admin_ticket_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $ticket = new Ticket();
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ticket->setUpdatedAt(new \DateTime());
            $em->persist($ticket);
            $em->flush();

            $this->addFlash('success', 'Ticket created successfully!');

            return $this->redirectToRoute('admin_ticket_index');
        }

        return $this->render('admin/ticket/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_ticket_show', methods: ['GET'])]
    public function show(Ticket $ticket): Response
    {
        return $this->render('admin/ticket/show.html.twig', [
            'ticket' => $ticket,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_ticket_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Ticket $ticket, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ticket->setUpdatedAt(new \DateTime());
            $em->flush();

            $this->addFlash('success', 'Ticket updated successfully!');

            return $this->redirectToRoute('admin_ticket_index');
        }

        return $this->render('admin/ticket/edit.html.twig', [
            'form' => $form->createView(),
            'ticket' => $ticket,
        ]);
    }

    #[Route('/{id}', name: 'admin_ticket_delete', methods: ['POST'])]
    public function delete(Request $request, Ticket $ticket, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $ticket->getId(), $request->request->get('_token'))) {
            $em->remove($ticket);
            $em->flush();

            $this->addFlash('success', 'Ticket deleted successfully!');
        }

        return $this->redirectToRoute('admin_ticket_index');
    }
}
