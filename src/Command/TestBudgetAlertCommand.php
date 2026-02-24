<?php

namespace App\Command;

use App\Service\BudgetAlertService;
use App\Repository\TeamRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestBudgetAlertCommand extends Command
{
    protected static $defaultName = 'app:test-budget-alert';
    protected static $defaultDescription = 'Test budget alert system';

    private BudgetAlertService $budgetAlertService;
    private TeamRepository $teamRepository;

    public function __construct(BudgetAlertService $budgetAlertService, TeamRepository $teamRepository)
    {
        parent::__construct();
        $this->budgetAlertService = $budgetAlertService;
        $this->teamRepository = $teamRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Testing budget alert system...');

        // Get first team with budget
        $teams = $this->teamRepository->findAll();
        
        foreach ($teams as $team) {
            $output->writeln("Testing team: " . $team->getName());
            $this->budgetAlertService->checkBudgetAndAlert($team);
            $output->writeln("✓ Alert check completed for " . $team->getName());
        }

        $output->writeln("\n✅ Test completed!");
        return Command::SUCCESS;
    }
}
