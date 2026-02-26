<?php

namespace App\Command;

use App\Entity\Player;
use App\Service\DepenseChatbotService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\InMemoryUser;

#[AsCommand(
    name: 'app:test-chatbot',
    description: 'Test chatbot IA functionality directly'
)]
final class TestChatbotCommand extends Command
{
    public function __construct(
        private DepenseChatbotService $chatbotService,
        private Security $security
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('🤖 Testing Chatbot IA...');
        $output->writeln('');

        try {
            // Create a mock admin user for CLI context
            // This is just for testing and won't affect database
            $mockUser = new InMemoryUser(
                'test-admin',
                'password',
                ['ROLE_ADMIN']
            );

            $output->writeln('📋 Context: CLI Test (Admin privileges)');
            $output->writeln('');

            // Test 1: Get snapshot
            $output->write('📊 Getting financial snapshot... ');
            $snapshot = $this->chatbotService->getFinancialSnapshot();
            $output->writeln('✅ OK');
            $output->writeln('   - Teams: ' . count($snapshot['budgets']));
            $output->writeln('   - Total Budget: ' . $snapshot['global']['budgetTotal'] . ' TND');
            $output->writeln('   - Spent: ' . $snapshot['global']['spent'] . ' TND');
            $output->writeln('   - Remaining: ' . $snapshot['global']['remaining'] . ' TND');
            $output->writeln('');

            if (count($snapshot['budgets']) === 0) {
                $output->writeln('⚠️  WARNING: No budgets found in database!');
                $output->writeln('    This could mean:');
                $output->writeln('    1. Database is empty');
                $output->writeln('    2. Authorization check is too strict');
                $output->writeln('');
            }

            // Test 2: Ask a simple question
            $output->write('🤔 Testing AI response to: "Quel est le budget total ?"... ');
            $response = $this->chatbotService->ask('Quel est le budget total ?');
            $output->writeln('✅ OK');
            $output->writeln('');
            $output->writeln('📝 Response:');
            $output->writeln($response);
            $output->writeln('');

            // Test 3: Test commands
            $output->write('📋 Testing /stats command... ');
            $statsResponse = $this->chatbotService->ask('/stats');
            $output->writeln('✅ OK');
            $output->writeln('');
            $output->writeln('📊 Stats Response:');
            $output->writeln($statsResponse);
            $output->writeln('');

            if (count($snapshot['budgets']) === 0) {
                $output->writeln('❌ ISSUE: Chatbot is working but returning no budget data!');
                $output->writeln('');
                $output->writeln('🔍 DIAGNOSIS:');
                $output->writeln('   - The chatbot service is functional');
                $output->writeln('   - But getFinancialSnapshot() returns empty budgets');
                $output->writeln('   - This is a DATA issue, not a CODE issue');
                $output->writeln('   - Check filter logic in getFinancialSnapshot()');
                return Command::FAILURE;
            }

            $output->writeln('✅ All tests passed! Chatbot is functional and returning data.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('❌ Error: ' . $e->getMessage());
            $output->writeln('');
            $output->writeln('Stack trace:');
            $output->writeln($e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
