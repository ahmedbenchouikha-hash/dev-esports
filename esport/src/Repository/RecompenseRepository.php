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
     * Rechercher par nom et/ou type avec tri
     */
    public function searchByNameAndType(?string $searchName = null, ?string $searchType = null, ?string $sort = null): array
    {
        $qb = $this->createQueryBuilder('r');

        // Recherche par nom
        if ($searchName) {
            $qb->andWhere('LOWER(r.recompense) LIKE LOWER(:searchName)')
                ->setParameter('searchName', '%' . $searchName . '%');
        }

        // Recherche par type
        if ($searchType) {
            $qb->andWhere('LOWER(r.type) LIKE LOWER(:searchType)')
                ->setParameter('searchType', '%' . $searchType . '%');
        }

        // Tri
        $sortOrder = ($sort === 'desc') ? 'DESC' : 'ASC';
        $qb->orderBy('r.recompense', $sortOrder);

        return $qb->getQuery()->getResult();
    }

    /**
     * Rechercher par nom avec tri (obsolète, garder pour compatibilité)
     */
    public function searchAndSort(?string $search = null, ?string $sort = null): array
    {
        return $this->searchByNameAndType($search, null, $sort);
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