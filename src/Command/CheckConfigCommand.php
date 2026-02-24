<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;

#[AsCommand(
    name: 'app:check-config',
    description: 'Check current Symfony configuration'
)]
class CheckConfigCommand extends Command
{
    public function __construct(private MailerInterface $mailer)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Checking configuration...</info>');
        
        $output->writeln('<info>Mailer class: ' . get_class($this->mailer) . '</info>');
        
        // Try to get the transport
        if (method_exists($this->mailer, 'getTransport')) {
            $transport = $this->mailer->getTransport();
            $output->writeln('<info>Transport class: ' . get_class($transport) . '</info>');
        }
        
        // Check environment variables
        $output->writeln('<info>MAILER_DSN env var: ' . ($_ENV['MAILER_DSN'] ?? 'NOT SET') . '</info>');
        
        return Command::SUCCESS;
    }
}
