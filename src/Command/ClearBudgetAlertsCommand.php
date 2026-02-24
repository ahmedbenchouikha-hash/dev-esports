<?php

namespace App\Command;

use App\Repository\BudgetAlertRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:clear-budget-alerts',
    description: 'Delete all budget alerts from the database'
)]
class ClearBudgetAlertsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BudgetAlertRepository $budgetAlertRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->entityManager->createQuery('DELETE FROM App\Entity\BudgetAlert')->execute();
        $output->writeln('<info>All budget alerts deleted successfully!</info>');
        return Command::SUCCESS;
    }
}
