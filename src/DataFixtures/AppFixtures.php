<?php

namespace App\DataFixtures;

use App\Entity\Depense;
use App\Entity\Game;
use App\Entity\Player;
use App\Entity\Team;
use App\Entity\Tournament;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Create Tournaments
        $tournament1 = new Tournament();
        $tournament1->setName('World Championship 2025');
        $tournament1->setDescription('The biggest esports tournament of the year with teams from around the world competing for $1 million prize pool.');
        $tournament1->setStartDate(new \DateTime('2025-02-15'));
        $tournament1->setEndDate(new \DateTime('2025-02-28'));
        $tournament1->setLocation('Dubai, UAE');
        $tournament1->setPrizePool(1000000);
        $tournament1->setStatus('pending');
        $manager->persist($tournament1);

        $tournament2 = new Tournament();
        $tournament2->setName('Regional Finals 2025');
        $tournament2->setDescription('Regional qualifiers for the world championship with 16 teams battling for spots.');
        $tournament2->setStartDate(new \DateTime('2025-02-01'));
        $tournament2->setEndDate(new \DateTime('2025-02-10'));
        $tournament2->setLocation('Berlin, Germany');
        $tournament2->setPrizePool(250000);
        $tournament2->setStatus('ongoing');
        $manager->persist($tournament2);

        $tournament3 = new Tournament();
        $tournament3->setName('Season 5 League');
        $tournament3->setDescription('Regular season matches where teams earn points towards playoff qualification.');
        $tournament3->setStartDate(new \DateTime('2025-01-01'));
        $tournament3->setEndDate(new \DateTime('2025-04-30'));
        $tournament3->setLocation('Online');
        $tournament3->setPrizePool(500000);
        $tournament3->setStatus('ongoing');
        $manager->persist($tournament3);

        // Create Teams
        $teams = [];

        $team1 = new Team();
        $team1->setName('Phoenix Legends');
        $team1->setCountry('United States');
        $team1->setDescription('One of the most dominant teams in esports history. Phoenix Legends has won multiple international tournaments and consistently ranks in the top 5 globally.');
        $manager->persist($team1);
        $teams[] = $team1;

        $team2 = new Team();
        $team2->setName('Dragon Warriors');
        $team2->setCountry('South Korea');
        $team2->setDescription('Korean esports powerhouse known for exceptional mechanical skill and strategic gameplay. Multiple world champions among their roster.');
        $manager->persist($team2);
        $teams[] = $team2;

        $team3 = new Team();
        $team3->setName('Shadow Assassins');
        $team3->setCountry('United Kingdom');
        $team3->setDescription('European team known for aggressive playstyle and creative strategies. Underdog story that made it to multiple finals.');
        $manager->persist($team3);
        $teams[] = $team3;

        $team4 = new Team();
        $team4->setName('Nordic Vikings');
        $team4->setCountry('Sweden');
        $team4->setDescription('Scandinavian representatives with a talented young roster. Rising stars in the competitive scene.');
        $manager->persist($team4);
        $teams[] = $team4;

        $team5 = new Team();
        $team5->setName('Rising Stars');
        $team5->setCountry('France');
        $team5->setDescription('Emerging team with promising young talent. Known for high-speed gameplay and innovative tactics.');
        $manager->persist($team5);
        $teams[] = $team5;

        $team6 = new Team();
        $team6->setName('Tokyo Ninjas');
        $team6->setCountry('Japan');
        $team6->setDescription('Japanese esports organization with excellent support staff and training facilities.');
        $manager->persist($team6);
        $teams[] = $team6;

        // Create Players
        $playerNames = [
            ['Alex Chen', 'Mechanical', 'Mid'],
            ['Marcus Johnson', 'Strategic', 'Support'],
            ['Emma Rodriguez', 'Aggressive', 'Carry'],
            ['Kai Tanaka', 'Defensive', 'Top'],
            ['Sarah Williams', 'Creative', 'Jungler'],
            ['Lin Zhang', 'Analytical', 'Mid'],
            ['James Murphy', 'Leader', 'Support'],
            ['Sophie Lambert', 'Explosive', 'Carry'],
            ['Yuki Yamamoto', 'Flexible', 'Top'],
            ['Maria Santos', 'Consistent', 'Jungler'],
            ['David Kim', 'Clutch', 'Mid'],
            ['Anna Petrov', 'Supportive', 'Support'],
        ];

        $players = [];
        $playerIndex = 0;

        foreach ($teams as $team) {
            for ($i = 0; $i < 5; $i++) {
                $player = new Player();
                [$name, $playstyle, $role] = $playerNames[$playerIndex % count($playerNames)];
                
                $player->setNickname($name . '#' . rand(100, 999));
                $player->setFirstName($name);
                $player->setLastName('Player');
                $player->setBirthDate(new \DateTime('-' . rand(18, 35) . ' years'));
                $player->setRole($role);
                $player->setTeam($team);
                $manager->persist($player);
                $players[] = $player;
                $playerIndex++;
            }
        }

        // Create Games/Matches
        $statuses = ['pending', 'ongoing', 'finished'];
        $matchCount = 0;

        // Matches for Tournament 1
        for ($i = 0; $i < 8; $i++) {
            $game = new Game();
            $game->setTeam1($teams[rand(0, count($teams) - 1)]);
            $game->setTeam2($teams[rand(0, count($teams) - 1)]);
            
            // Ensure teams are different
            while ($game->getTeam2()->getId() === $game->getTeam1()->getId()) {
                $game->setTeam2($teams[rand(0, count($teams) - 1)]);
            }

            $game->setTournament($tournament1);
            $game->setMatchDate(new \DateTime('+' . rand(1, 21) . ' days'));
            $game->setScore1(rand(0, 2));
            $game->setScore2(rand(0, 2));
            $game->setStatus($statuses[rand(0, 2)]);
            $manager->persist($game);
            $matchCount++;
        }

        // Matches for Tournament 2
        for ($i = 0; $i < 12; $i++) {
            $game = new Game();
            $game->setTeam1($teams[rand(0, count($teams) - 1)]);
            $game->setTeam2($teams[rand(0, count($teams) - 1)]);
            
            while ($game->getTeam2()->getId() === $game->getTeam1()->getId()) {
                $game->setTeam2($teams[rand(0, count($teams) - 1)]);
            }

            $game->setTournament($tournament2);
            $game->setMatchDate(new \DateTime('+' . rand(1, 9) . ' days'));
            $game->setScore1(rand(0, 2));
            $game->setScore2(rand(0, 2));
            $game->setStatus($statuses[rand(0, 2)]);
            $manager->persist($game);
            $matchCount++;
        }

        // Matches for Tournament 3
        for ($i = 0; $i < 20; $i++) {
            $game = new Game();
            $game->setTeam1($teams[rand(0, count($teams) - 1)]);
            $game->setTeam2($teams[rand(0, count($teams) - 1)]);
            
            while ($game->getTeam2()->getId() === $game->getTeam1()->getId()) {
                $game->setTeam2($teams[rand(0, count($teams) - 1)]);
            }

            $game->setTournament($tournament3);
            $game->setMatchDate(new \DateTime(rand(-89, 89) . ' days'));
            $game->setScore1(rand(0, 3));
            $game->setScore2(rand(0, 3));
            $game->setStatus($statuses[rand(0, 2)]);
            $manager->persist($game);
            $matchCount++;
        }

        // Create Expenses
        $expenseCount = 0;

        $depense1 = new Depense();
        $depense1->setTeam($teams[0]); // Phoenix Legends
        $depense1->setTitre('Equipment Purchase');
        $depense1->setMontant(5000.00);
        $depense1->setDescription('Gaming peripherals and tournament equipment');
        $depense1->setDateCreation(new \DateTime());
        $depense1->setStatut('en_attente');
        $depense1->setCategorie('equipment');
        $manager->persist($depense1);
        $expenseCount++;

        $depense2 = new Depense();
        $depense2->setTeam($teams[0]); // Phoenix Legends
        $depense2->setTitre('Team Travel Expenses');
        $depense2->setMontant(15000.00);
        $depense2->setDescription('Flights and accommodation for world championship');
        $depense2->setDateCreation(new \DateTime('-20 days'));
        $depense2->setStatut('en_attente');
        $depense2->setCategorie('travel');
        $manager->persist($depense2);
        $expenseCount++;

        $depense3 = new Depense();
        $depense3->setTeam($teams[1]); // Dragon Warriors
        $depense3->setTitre('Coaching Fees');
        $depense3->setMontant(8000.00);
        $depense3->setDescription('Monthly coaching and strategy sessions');
        $depense3->setDateCreation(new \DateTime('-5 days'));
        $depense3->setStatut('en_attente');
        $depense3->setCategorie('personnel');
        $manager->persist($depense3);
        $expenseCount++;

        $depense4 = new Depense();
        $depense4->setTeam($teams[2]); // Shadow Assassins
        $depense4->setTitre('Streaming Setup');
        $depense4->setMontant(3500.00);
        $depense4->setDescription('Professional streaming equipment and software licenses');
        $depense4->setDateCreation(new \DateTime());
        $depense4->setStatut('en_attente');
        $depense4->setCategorie('equipment');
        $manager->persist($depense4);
        $expenseCount++;

        $manager->flush();

        echo "\n✅ Database populated successfully!\n";
        echo "   📊 Created: 3 Tournaments, 6 Teams, 30 Players, $matchCount Matches, $expenseCount Expenses\n\n";
    }
}
