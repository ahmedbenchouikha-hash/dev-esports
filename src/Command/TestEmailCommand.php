<?php

namespace App\Command;

use App\Service\EmailService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:test-email',
    description: 'Test sending an email'
)]
class TestEmailCommand extends Command
{
    private EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        parent::__construct();
        $this->emailService = $emailService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('🧪 Testing email sending...');

        $to = 'melkimalek888@gmail.com';
        $subject = '🧪 Test Email - RankUp Budget Alert System';
        $message = <<<HTML
        <html>
        <body style="font-family: Arial; background-color: #f5f5f5; padding: 20px;">
            <div style="max-width: 600px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 8px; border-left: 5px solid #4CAF50;">
                <h2 style="color: #4CAF50;">✅ Test Email - System Working!</h2>
                <p>This is a test email to verify that the budget alert system is working correctly.</p>
                <p><strong>Time:</strong> {$this->getTime()}</p>
                <hr />
                <p style="color: #666; font-size: 12px;">This is an automated test email from RankUp.</p>
            </div>
        </body>
        </html>
        HTML;

        try {
            $output->writeln("📧 Sending email to: $to");
            $this->emailService->send($to, $subject, $message);
            $output->writeln('✅ Email sent successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('❌ Error sending email: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function getTime(): string
    {
        return (new \DateTime())->format('Y-m-d H:i:s');
    }
}
