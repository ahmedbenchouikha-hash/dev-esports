<?php

namespace App\Command;

use App\Service\DepenseChatbotService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ChatbotDiagnosticCommand extends Command
{
    protected static $defaultName = 'chatbot:diagnostic';
    protected static $defaultDescription = 'Test chatbot functionality and debug issues';

    public function __construct(
        private DepenseChatbotService $chatbotService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('question', InputArgument::OPTIONAL, 'Question to test', 'Propose une stratégie d\'optimisation budgétaire');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $question = $input->getArgument('question');

        $io->title('🤖 Chatbot Diagnostic Tool');

        // Test 1: Check API Key
        $io->section('1️⃣ Configuration Check');
        $apiKey = getenv('GEMINI_API_KEY') ?: ($_ENV['GEMINI_API_KEY'] ?? $_SERVER['GEMINI_API_KEY'] ?? null);
        $model = getenv('GEMINI_MODEL') ?: ($_ENV['GEMINI_MODEL'] ?? $_SERVER['GEMINI_MODEL'] ?? 'gemini-1.5-flash');

        if ($apiKey) {
            $io->success('✅ Gemini API Key configured');
            $io->writeln('   Key: ' . substr($apiKey, 0, 20) . '...');
        } else {
            $io->warning('⚠️  Gemini API Key NOT configured - using fallback only');
        }
        $io->writeln('   Model: ' . $model);

        // Test 2: Get Financial Snapshot
        $io->section('2️⃣ Financial Snapshot');
        $snapshot = $this->chatbotService->getFinancialSnapshot();
        $io->writeln('   Total Budget: ' . number_format($snapshot['global']['budgetTotal'], 2, '.', ' ') . ' TND');
        $io->writeln('   Spent: ' . number_format($snapshot['global']['spent'], 2, '.', ' ') . ' TND');
        $io->writeln('   Remaining: ' . number_format($snapshot['global']['remaining'], 2, '.', ' ') . ' TND');
        $io->writeln('   Teams: ' . count($snapshot['budgets']));
        $io->writeln('   Expenses: ' . count($snapshot['expenses']));

        // Test 3: Test Chatbot Answer
        $io->section('3️⃣ Chatbot Response Test');
        $io->writeln('   Question: ' . $question);
        $io->newLine();

        $answer = $this->chatbotService->ask($question);

        if (!$answer || trim($answer) === '') {
            $io->error('❌ FAILED: Chatbot returned empty answer!');
            return Command::FAILURE;
        }

        $io->success('✅ Chatbot returned answer:');
        $io->writeln('');
        $io->writeln($answer);
        $io->writeln('');
        $io->writeln('   Length: ' . strlen($answer) . ' characters');

        // Test 4: Suggestions
        $io->section('4️⃣ Next Steps');
        if (!$apiKey) {
            $io->note('To enable Gemini AI, configure GEMINI_API_KEY environment variable:');
            $io->writeln('   1. Get key from: https://aistudio.google.com/');
            $io->writeln('   2. Add to .env file: GEMINI_API_KEY=your_key_here');
            $io->writeln('   3. Clear cache: php bin/console cache:clear');
        } else {
            $io->note('Gemini API is configured. If getting null, check:');
            $io->writeln('   1. Run: tail -f var/log/dev.log to see debug logs');
            $io->writeln('   2. Check API rate limits');
            $io->writeln('   3. Verify API key is valid');
        }

        return Command::SUCCESS;
    }
}
