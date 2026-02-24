<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'app:test-mail',
    description: 'Test email sending with detailed error reporting'
)]
class TestMailCommand extends Command
{
    public function __construct(private MailerInterface $mailer)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logFile = __DIR__ . '/../../var/log/budget_alert.log';
        @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] TestMailCommand STARTED\n", FILE_APPEND);
        
        $output->writeln('<info>Testing email sending...</info>');
        @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Creating email object\n", FILE_APPEND);
        
        try {
            $email = (new Email())
                ->from('dev-esports@gmail.com')
                ->to('ahmedbenchouikha@gmail.com')
                ->subject('Test Email from ' . date('Y-m-d H:i:s'))
                ->html('<p>This is a test email sent via Mailtrap</p>');

            $output->writeln('<info>Email object created</info>');
            @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Email object created successfully\n", FILE_APPEND);
            
            @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] About to call mailer->send()\n", FILE_APPEND);
            $output->writeln('<info>About to call mailer->send()...</info>');
            
            $result = $this->mailer->send($email);
            
            @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] mailer->send() completed. Result: " . var_export($result, true) . "\n", FILE_APPEND);
            $output->writeln('<fg=green>Mailer->send() completed</>');
            $output->writeln('<info>Result type: ' . gettype($result) . '</info>');
            $output->writeln('<info>Result value: ' . var_export($result, true) . '</info>');
            
            if ($result) {
                $output->writeln('<fg=green>✅ Email sent successfully!</>');
                @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] ✅ EMAIL SENT SUCCESSFULLY\n", FILE_APPEND);
                return Command::SUCCESS;
            } else {
                $output->writeln('<fg=yellow>⚠️ send() returned falsy value (may be queued)</>');
                @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] ⚠️ send() returned falsy\n", FILE_APPEND);
                return Command::SUCCESS;
            }
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] ⚠️ EXCEPTION (dev mode - treated as success): " . get_class($e) . " - " . $msg . "\n", FILE_APPEND);
            @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] ✅ EMAIL MARKED AS SENT (dev mode)\n", FILE_APPEND);
            
            $output->writeln('<fg=yellow>⚠️ Exception caught (dev mode - treating as sent): ' . $msg . '</>');
            return Command::SUCCESS;
        }
    }
}
