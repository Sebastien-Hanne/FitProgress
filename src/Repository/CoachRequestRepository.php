<?php

namespace App\Repository;

use App\Entity\CoachRequest;
use App\Entity\User;
use App\Enum\RequestStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CoachRequest>
 */
class CoachRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoachRequest::class);
    }

    public function findCurrentForUser(User $user): ?CoachRequest
    {
        return $this->createQueryBuilder('request')
            ->addSelect('coach', 'coachUser')
            ->innerJoin('request.coachProfile', 'coach')
            ->innerJoin('coach.user', 'coachUser')
            ->andWhere('request.user = :user')
            ->andWhere('request.status IN (:statuses)')
            ->setParameter('user', $user)
            ->setParameter('statuses', [RequestStatus::Pending, RequestStatus::Approved])
            ->orderBy('request.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }

    //    /**
    //     * @return CoachRequest[] Returns an array of CoachRequest objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?CoachRequest
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
