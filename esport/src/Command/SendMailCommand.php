<?php

namespace App\Command;

use App\Service\EmailService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:send-mail',
    description: 'Teste l\'envoi d\'email via Mailtrap',
)]
class SendMailCommand extends Command
{
    public function __construct(private EmailService $emailService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->emailService->sendTestEmail('ramzi.benhmida@esprit.tn')) {
            $io->success('✓ Email envoyé avec succès via Mailtrap!');
            $io->info('Vérifiez votre boîte de réception Mailtrap : https://mailtrap.io/');
            return Command::SUCCESS;
        } else {
            $io->error('Erreur lors de l\'envoi de l\'email.');
            return Command::FAILURE;
        }
    }
}
