<?php

namespace App\Repository;

use App\Entity\Recompense;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recompense>
 */
class RecompenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recompense::class);
    }

    /**
     * Find all recompenses ordered by classement
     */
    public function findAllOrderedByClassement(): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.classement', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search and sort recompenses
     */
    public function searchAndSort(?string $search = null, ?string $sort = null): array
    {
        $qb = $this->createQueryBuilder('r');

        if ($search) {
            $qb->where('r.recompense LIKE :search')
                ->orWhere('r.type LIKE :search')
                ->orWhere('r.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($sort === 'classement_asc') {
            $qb->orderBy('r.classement', 'ASC');
        } elseif ($sort === 'classement_desc') {
            $qb->orderBy('r.classement', 'DESC');
        } elseif ($sort === 'name_asc') {
            $qb->orderBy('r.recompense', 'ASC');
        } elseif ($sort === 'name_desc') {
            $qb->orderBy('r.recompense', 'DESC');
        } else {
            $qb->orderBy('r.classement', 'ASC');
        }

        return $qb->getQuery()->getResult();
    }
}
