<?php

require_once 'vendor/autoload.php';

use App\Kernel;
use App\Entity\Depense;
use App\Entity\Team;

// Créer le kernel Symfony
$kernel = new Kernel('dev', true);
$kernel->boot();

$entityManager = $kernel->getContainer()->get('doctrine')->getManager();

// Créer quelques dépenses de test
$teams = $entityManager->getRepository(Team::class)->findAll();

if (count($teams) >= 3) {
    $depense1 = new Depense();
    $depense1->setTeam($teams[0]);
    $depense1->setTitre('Equipment Purchase');
    $depense1->setMontant(5000.00);
    $depense1->setDescription('Gaming peripherals and tournament equipment');
    $depense1->setDateCreation(new \DateTime());
    $depense1->setStatut('en_attente');
    $depense1->setCategorie('equipment');
    $entityManager->persist($depense1);

    $depense2 = new Depense();
    $depense2->setTeam($teams[1]);
    $depense2->setTitre('Coaching Fees');
    $depense2->setMontant(8000.00);
    $depense2->setDescription('Monthly coaching and strategy sessions');
    $depense2->setDateCreation(new \DateTime('-5 days'));
    $depense2->setStatut('en_attente');
    $depense2->setCategorie('personnel');
    $entityManager->persist($depense2);

    $depense3 = new Depense();
    $depense3->setTeam($teams[2]);
    $depense3->setTitre('Streaming Setup');
    $depense3->setMontant(3500.00);
    $depense3->setDescription('Professional streaming equipment and software licenses');
    $depense3->setDateCreation(new \DateTime());
    $depense3->setStatut('en_attente');
    $depense3->setCategorie('equipment');
    $entityManager->persist($depense3);

    $entityManager->flush();

    echo "✅ Successfully created 3 test expenses!\n";
} else {
    echo "❌ Not enough teams found in database (" . count($teams) . "). Please load fixtures first.\n";
}