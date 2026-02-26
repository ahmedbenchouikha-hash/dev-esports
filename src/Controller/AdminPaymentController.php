<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Repository\PaymentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/payments')]
class AdminPaymentController extends AbstractController
{
    #[Route('', name: 'admin_payment_index', methods: ['GET'])]
    public function index(PaymentRepository $repository, Request $request): Response
    {
        $search = $request->query->get('search', '');
        $page = (int)$request->query->get('page', 1);
        $limit = 12;
        $offset = ($page - 1) * $limit;

        // Build query
        $queryBuilder = $repository->createQueryBuilder('p')
            ->leftJoin('p.ticket', 't')
            ->addSelect('t');

        if ($search) {
            $queryBuilder
                ->andWhere('p.customerEmail LIKE :search OR p.customerName LIKE :search OR p.paymentIntentId LIKE :search OR t.ticketNumber LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $queryBuilder->orderBy('p.id', 'DESC');
        $query = $queryBuilder->getQuery();
        $allPayments = $query->getResult();
        $total = count($allPayments);
        $pages = (int)ceil($total / $limit);

        $payments = array_slice($allPayments, $offset, $limit);

        // Calculate statistics
        $stats = $this->calculateStats($allPayments);

        return $this->render('admin/payment/index.html.twig', [
            'payments' => $payments,
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
            'search' => $search,
            'stats' => $stats,
        ]);
    }

    #[Route('/{id}', name: 'admin_payment_show', methods: ['GET'])]
    public function show(Payment $payment): Response
    {
        return $this->render('admin/payment/show.html.twig', [
            'payment' => $payment,
        ]);
    }

    private function calculateStats(array $payments): array
    {
        $totalRevenue = 0;
        $totalRefunded = 0;
        $countSucceeded = 0;
        $countPending = 0;
        $countFailed = 0;
        $countRefunded = 0;
        $revenueByType = [];
        $revenueByStatus = ['succeeded' => 0, 'failed' => 0, 'pending' => 0, 'refunded' => 0];
        $topCustomers = [];
        $monthlyRevenue = [];

        foreach ($payments as $payment) {
            $amount = $payment->getAmount() * $payment->getQuantityPurchased();
            
            // Overall stats
            $totalRevenue += $amount;
            
            // Count by status
            match ($payment->getStatus()) {
                'succeeded' => $countSucceeded++,
                'pending' => $countPending++,
                'failed' => $countFailed++,
                'refunded' => $countRefunded++,
                default => null,
            };

            // Revenue by status
            $revenueByStatus[$payment->getStatus()] += $amount;
            
            // Refund tracking
            if ($payment->getStatus() === 'refunded' && $payment->getRefundAmount()) {
                $totalRefunded += $payment->getRefundAmount();
            }

            // Revenue by ticket type
            $ticketType = $payment->getTicket()->getType();
            if (!isset($revenueByType[$ticketType])) {
                $revenueByType[$ticketType] = 0;
            }
            $revenueByType[$ticketType] += $amount;

            // Top customers
            $email = $payment->getCustomerEmail();
            if (!isset($topCustomers[$email])) {
                $topCustomers[$email] = [
                    'name' => $payment->getCustomerName() ?: $email,
                    'email' => $email,
                    'spent' => 0,
                    'purchases' => 0,
                ];
            }
            $topCustomers[$email]['spent'] += $amount;
            $topCustomers[$email]['purchases']++;

            // Monthly revenue (if payment entity has createdAt)
            if (method_exists($payment, 'getCreatedAt') && $payment->getCreatedAt()) {
                $month = $payment->getCreatedAt()->format('Y-m');
                if (!isset($monthlyRevenue[$month])) {
                    $monthlyRevenue[$month] = 0;
                }
                $monthlyRevenue[$month] += $amount;
            }
        }

        // Sort top customers by spent
        uasort($topCustomers, fn($a, $b) => $b['spent'] <=> $a['spent']);
        $topCustomers = array_slice($topCustomers, 0, 5);

        // Sort monthly revenue
        ksort($monthlyRevenue);

        $avgTransaction = count($payments) > 0 ? $totalRevenue / count($payments) : 0;

        return [
            'totalRevenue' => $totalRevenue,
            'totalRefunded' => $totalRefunded,
            'netRevenue' => $totalRevenue - $totalRefunded,
            'countSucceeded' => $countSucceeded,
            'countPending' => $countPending,
            'countFailed' => $countFailed,
            'countRefunded' => $countRefunded,
            'avgTransaction' => $avgTransaction,
            'revenueByType' => $revenueByType,
            'revenueByStatus' => $revenueByStatus,
            'topCustomers' => $topCustomers,
            'monthlyRevenue' => $monthlyRevenue,
        ];
    }
}
