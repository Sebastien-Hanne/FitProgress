<?php

namespace App\Repository;

use App\Entity\CoachRequest;
use App\Entity\User;
use App\Entity\CoachProfile;
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

    /** @return CoachRequest[] */
    public function findApprovedForCoach(CoachProfile $coach, ?string $search = null): array
    {
        $qb = $this->createQueryBuilder('request')->addSelect('client', 'goal')
            ->innerJoin('request.user', 'client')->leftJoin('client.goal', 'goal')
            ->andWhere('request.coachProfile = :coach')->andWhere('request.status = :approved')
            ->setParameter('coach', $coach)->setParameter('approved', RequestStatus::Approved)
            ->orderBy('client.name', 'ASC');
        if ($search) $qb->andWhere('LOWER(client.name) LIKE :search')->setParameter('search', '%'.mb_strtolower($search).'%');
        return $qb->getQuery()->getResult();
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
