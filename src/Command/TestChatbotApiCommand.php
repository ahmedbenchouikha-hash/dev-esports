<?php

namespace App\Command;

use App\Entity\Player;
use App\Repository\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsCommand(
    name: 'app:test-chatbot-api',
    description: 'Test chatbot API endpoint'
)]
final class TestChatbotApiCommand extends Command
{
    public function __construct(
        private PlayerRepository $playerRepository,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('🧪 Testing Chatbot API...');
        $output->writeln('');

        // Get a manager or admin user
        $users = $this->playerRepository->findAll();
        $output->writeln('📊 Total users in database: ' . count($users));

        foreach ($users as $user) {
            if (\in_array('ROLE_MANAGER', $user->getRoles()) || \in_array('ROLE_ADMIN', $user->getRoles())) {
                $output->writeln('✅ Found user with manager/admin role:');
                $output->writeln('   - Username: ' . $user->getUsername());
                $output->writeln('   - Email: ' . $user->getEmail());
                $output->writeln('   - Roles: ' . implode(', ', $user->getRoles()));
                $output->writeln('');

                // List teams
                $teams = $user->getTeams();
                $output->writeln('👥 Teams assigned: ' . count($teams));
                foreach ($teams as $team) {
                    $output->writeln('   - ' . $team->getName());
                }
                $output->writeln('');
                return Command::SUCCESS;
            }
        }

        $output->writeln('❌ No manager or admin users found!');
        $output->writeln('');
        $output->writeln('📋 Available users:');
        foreach ($users as $user) {
            $output->writeln('   - ' . $user->getUsername() . ' (Roles: ' . implode(', ', $user->getRoles()) . ')');
        }

        return Command::FAILURE;
    }
}
