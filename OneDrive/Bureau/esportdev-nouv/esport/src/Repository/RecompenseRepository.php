<?php

namespace App\Repository;

use App\Entity\Recompense;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RecompenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recompense::class);
    }

    public function findAllOrderedByClassement(): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.classement', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Rechercher par nom avec tri
     */
    public function searchAndSort(?string $search = null, ?string $sort = null): array
    {
        $qb = $this->createQueryBuilder('r');

        // Recherche
        if ($search) {
            $qb->where('LOWER(r.recompense) LIKE LOWER(:search)')
                ->setParameter('search', '%' . $search . '%');
        }

        // Tri
        $sortOrder = ($sort === 'desc') ? 'DESC' : 'ASC';
        $qb->orderBy('r.recompense', $sortOrder);

        return $qb->getQuery()->getResult();
    }

    /**
     * Compter par type pour les statistiques
     */
    public function countByType(): array
    {
        return $this->createQueryBuilder('r')
            ->select('r.type, COUNT(r.id) as count')
            ->groupBy('r.type')
            ->getQuery()
            ->getResult();
    }
}