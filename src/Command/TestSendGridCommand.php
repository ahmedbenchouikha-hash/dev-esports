<?php

namespace App\Command;

use App\Service\EmailService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:test-sendgrid',
    description: 'Test SendGrid email sending',
)]
class TestSendGridCommand extends Command
{
    public function __construct(private EmailService $emailService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Testing SendGrid email sending...');

        try {
            $this->emailService->send(
                'ahmedbenchouikha@gmail.com',
                '🎫 SendGrid Test Email - Dev Esports',
                '<h1>SendGrid Integration Test</h1><p>If you received this, SendGrid is working!</p>'
            );

            $output->writeln('<info>✅ Email sent successfully!</info>');
            $output->writeln('Check your inbox or SendGrid Activity Feed');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>❌ Error sending email:</error>');
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }
    }
}
