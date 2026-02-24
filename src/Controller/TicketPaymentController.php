<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\Payment;
use App\Repository\PaymentRepository;
use App\Service\StripeService;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ticket/payment')]
class TicketPaymentController extends AbstractController
{
    public function __construct(
        private StripeService $stripeService,
        private EntityManagerInterface $entityManager,
        private PaymentRepository $paymentRepository,
        private EmailService $emailService
    ) {}

    /**
     * Show payment form for a ticket
     */
    #[Route('/{id}/checkout', name: 'ticket_checkout', methods: ['GET'])]
    public function checkout(Ticket $ticket): Response
    {
        return $this->render('ticket_payment/checkout.html.twig', [
            'ticket' => $ticket,
            'stripePublicKey' => $this->stripeService->getPublishableKey(),
        ]);
    }

    /**
     * API: Create payment intent
     */
    #[Route('/{id}/create-payment', name: 'ticket_create_payment', methods: ['POST'])]
    public function createPayment(Ticket $ticket, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['email'])) {
                return $this->json(['error' => 'Email is required'], 400);
            }

            $email = $data['email'];

            // Create payment intent with Stripe
            $paymentIntent = $this->stripeService->createPaymentIntent(
                $ticket->getPrice(),
                'usd', // Change to your currency
                $ticket,
                $email
            );

            // Don't create Payment record yet - wait for confirmation
            // Return payment intent for client-side confirmation
            return $this->json([
                'success' => true,
                'clientSecret' => $paymentIntent->client_secret,
                'paymentIntentId' => $paymentIntent->id,
                'amount' => $ticket->getPrice(),
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * API: Confirm payment after client-side processing
     */
    #[Route('/{id}/confirm-payment', name: 'ticket_confirm_payment', methods: ['POST'])]
    public function confirmPayment(Ticket $ticket, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['paymentIntentId'])) {
                return $this->json(['error' => 'Payment intent ID is required'], 400);
            }

            $paymentIntentId = $data['paymentIntentId'];

            $paymentIntentId = $data['paymentIntentId'];
            $email = $data['email'] ?? null;

            // Check if payment succeeded with Stripe
            $isSucceeded = $this->stripeService->isPaymentSucceeded($paymentIntentId);

            if ($isSucceeded) {
                // Check if Payment record exists
                $payment = $this->paymentRepository->findByPaymentIntentId($paymentIntentId);
                
                if (!$payment) {
                    // Create Payment record only on successful confirmation
                    $payment = new Payment();
                    $payment->setTicket($ticket);
                    $payment->setPaymentIntentId($paymentIntentId);
                    $payment->setCustomerEmail($email);
                    $payment->setAmount($ticket->getPrice());
                    $payment->setQuantityPurchased(1);
                    $payment->setStatus('succeeded');
                } else {
                    // Update existing record
                    $payment->setStatus('succeeded');
                }

                // Update ticket as sold
                $ticket->setSold($ticket->getSold() + $payment->getQuantityPurchased());

                // Update ticket status if all sold
                if ($ticket->getSold() >= $ticket->getQuantity()) {
                    $ticket->setStatus('sold_out');
                }

                $this->entityManager->persist($payment);
                $this->entityManager->flush();

                // Send confirmation email
                $this->emailService->sendPaymentConfirmation($payment);

                return $this->json([
                    'success' => true,
                    'message' => 'Ticket purchased successfully! Check your email for confirmation.',
                    'ticketNumber' => $ticket->getTicketNumber(),
                    'paymentId' => $payment->getId(),
                ]);
            } else {
                return $this->json([
                    'success' => false,
                    'message' => 'Payment failed. Please try again.',
                ], 400);
            }
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * API: Get payment status
     */
    #[Route('/{id}/payment-status', name: 'ticket_payment_status', methods: ['GET'])]
    public function getPaymentStatus(Ticket $ticket): JsonResponse
    {
        try {
            // Get latest payment for this ticket
            $payments = $this->paymentRepository->findByTicket($ticket->getId());
            
            if (empty($payments)) {
                return $this->json([
                    'status' => 'not_started',
                    'message' => 'No payments initiated yet',
                    'ticketSold' => $ticket->getSold(),
                    'ticketQuantity' => $ticket->getQuantity(),
                ]);
            }

            $latestPayment = $payments[0];
            $intent = $this->stripeService->getPaymentIntent($latestPayment->getPaymentIntentId());

            return $this->json([
                'status' => $intent->status,
                'paymentStatus' => $latestPayment->getStatus(),
                'amount' => $intent->amount / 100, // Convert from cents
                'paymentId' => $latestPayment->getId(),
                'ticketSold' => $ticket->getSold(),
                'ticketQuantity' => $ticket->getQuantity(),
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * API: Refund a payment
     */
    #[Route('/payment/{paymentId}/refund', name: 'payment_refund', methods: ['POST'])]
    public function refundPayment(Payment $payment): JsonResponse
    {
        try {
            // Check authorization (admin only)
            $this->denyAccessUnlessGranted('ROLE_ADMIN');

            if ($payment->getStatus() !== 'succeeded') {
                return $this->json(['error' => 'Only succeeded payments can be refunded'], 400);
            }

            // Process refund with Stripe
            $refund = $this->stripeService->refundPayment($payment->getPaymentIntentId(), $payment->getAmount());

            // Update payment record
            $payment->setStatus('refunded');
            $payment->setRefundedAt(new \DateTime());
            $payment->setRefundAmount($payment->getAmount());

            // Update ticket
            $ticket = $payment->getTicket();
            $ticket->setSold(max(0, $ticket->getSold() - $payment->getQuantityPurchased()));
            $ticket->setStatus('available');

            $this->entityManager->flush();

            // Send refund confirmation email
            $this->emailService->sendRefundConfirmation($payment, $payment->getAmount());

            return $this->json([
                'success' => true,
                'message' => 'Refund processed successfully',
                'refundId' => $refund->id,
                'amount' => $refund->amount / 100,
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Success page after payment
     */
    #[Route('/success', name: 'payment_success', methods: ['GET'])]
    public function success(): Response
    {
        return $this->render('ticket_payment/success.html.twig');
    }

    /**
     * Cancel page
     */
    #[Route('/cancel', name: 'payment_cancel', methods: ['GET'])]
    public function cancel(): Response
    {
        return $this->render('ticket_payment/cancel.html.twig');
    }
}

