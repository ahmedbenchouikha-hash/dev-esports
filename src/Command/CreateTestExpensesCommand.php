<?php

namespace App\Command;

use App\Entity\Depense;
use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateTestExpensesCommand extends Command
{
    protected static $defaultName = 'app:create-test-expenses';

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this->setDescription('Creates test expense records for development');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $teams = $this->entityManager->getRepository(Team::class)->findAll();

        if (count($teams) < 3) {
            $output->writeln('<error>Not enough teams found in database. Please load fixtures first.</error>');
            return Command::FAILURE;
        }

        // Create test expenses
        $expenses = [
            [
                'team' => $teams[0],
                'titre' => 'Equipment Purchase',
                'montant' => 5000.00,
                'description' => 'Gaming peripherals and tournament equipment',
                'categorie' => 'equipment'
            ],
            [
                'team' => $teams[1],
                'titre' => 'Coaching Fees',
                'montant' => 8000.00,
                'description' => 'Monthly coaching and strategy sessions',
                'categorie' => 'personnel'
            ],
            [
                'team' => $teams[2],
                'titre' => 'Streaming Setup',
                'montant' => 3500.00,
                'description' => 'Professional streaming equipment and software licenses',
                'categorie' => 'equipment'
            ]
        ];

        foreach ($expenses as $expenseData) {
            $depense = new Depense();
            $depense->setTeam($expenseData['team']);
            $depense->setTitre($expenseData['titre']);
            $depense->setMontant($expenseData['montant']);
            $depense->setDescription($expenseData['description']);
            $depense->setDateCreation(new \DateTime());
            $depense->setStatut('en_attente');
            $depense->setCategorie($expenseData['categorie']);

            $this->entityManager->persist($depense);
        }

        $this->entityManager->flush();

        $output->writeln('<info>Successfully created 3 test expenses!</info>');

        return Command::SUCCESS;
    }
}