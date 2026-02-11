<?php

namespace App\Repository;

use App\Entity\DemandeRecompense;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DemandeRecompenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DemandeRecompense::class);
    }

    public function findPending(): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.statut = :statut')
            ->setParameter('statut', 'en_attente')
            ->orderBy('d.dateDemande', 'DESC')
            ->getQuery()
            ->getResult();
    }
}