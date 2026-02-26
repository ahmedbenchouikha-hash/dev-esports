<?php

namespace App\Command;

use App\Entity\DemandeRecompense;
use App\Entity\Recompense;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LoadTestDataCommand extends Command
{
    protected static $defaultName = 'app:load-test-data';
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Loading test data...');

        try {
            // Create or get recompense
            $recompRepo = $this->em->getRepository(Recompense::class);
            $recompense = $recompRepo->findOneBy(['id' => 1]);
            
            if (!$recompense) {
                $recompense = new Recompense();
                $recompense->setRecompense('OR - Excellence Sportive');
                $recompense->setType('Premium');
                $recompense->setClassement(1);
                $this->em->persist($recompense);
            }

            // Clear existing test data
            $this->em->getRepository(DemandeRecompense::class)->createQueryBuilder('d')
                ->delete()
                ->where('d.id BETWEEN 1 AND 6')
                ->getQuery()
                ->execute();

            // Create 6 test cases
            $demandes = [
                ['Jules Dupont', 'jules@ex.com', 'Championnat régional', 'soumise', 85],
                ['Marie Smith', 'marie@ex.com', 'Bien joué hier', 'pre_analysee', 42],
                ['Tom Spam', 'tom@ex.com', 'give reward', 'rejetee', 15],
                ['Alex Champion', 'alex@ex.com', 'MVP 25/2', 'approuvee', 90],
                ['Lisa Suspicious', 'lisa@ex.com', 'Joué bien', 'rejetee', 25],
                ['Kevin Legend', 'kevin@ex.com', 'Triple penta', 'soumise', 78],
            ];

            foreach ($demandes as $i => $data) {
                $demande = new DemandeRecompense();
                $demande->setNomDemandeur($data[0]);
                $demande->setEmail($data[1]);
                $demande->setMotif($data[2]);
                $demande->setStatut($data[3]);
                $demande->setAiLegitimacyScore($data[4]);
                $demande->setRecompense($recompense);
                $demande->setDateDemande(new \DateTime());
                $this->em->persist($demande);
                $output->writeln("  ✓ {$data[0]} ({$data[3]})");
            }

            $this->em->flush();
            $output->writeln("\n<info>✅ 6 test cases loaded successfully!</info>");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $output->writeln("<error>❌ Error: " . $e->getMessage() . "</error>");
            return Command::FAILURE;
        }
    }
}
