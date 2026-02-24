<?php

namespace App\Repository;

use App\Entity\ManagerRequest;
use App\Entity\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ManagerRequest>
 *
 * @method ManagerRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method ManagerRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method ManagerRequest[]    findAll()
 * @method ManagerRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ManagerRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ManagerRequest::class);
    }

    public function findPendingRequests()
    {
        return $this->createQueryBuilder('mr')
            ->where('mr.status = :status')
            ->setParameter('status', 'pending')
            ->orderBy('mr.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByPlayer(Player $player)
    {
        return $this->createQueryBuilder('mr')
            ->where('mr.player = :player')
            ->setParameter('player', $player)
            ->orderBy('mr.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPendingByPlayer(Player $player)
    {
        return $this->createQueryBuilder('mr')
            ->where('mr.player = :player')
            ->andWhere('mr.status = :status')
            ->setParameter('player', $player)
            ->setParameter('status', 'pending')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
