<?php

namespace App\Repository;

use App\Entity\CoachProfile;
use App\Entity\Session;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Session>
 */
class SessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Session::class);
    }

    /** @return Session[] */
    public function findForCoach(CoachProfile $coach): array
    {
        return $this->createQueryBuilder('session')
            ->addSelect('client')
            ->innerJoin('session.user', 'client')
            ->andWhere('session.coachProfile = :coach')
            ->setParameter('coach', $coach)
            ->orderBy('session.startAt', 'ASC')
            ->getQuery()->getResult();
    }

    /** @return Session[] */
    public function findForUser(User $user): array
    {
        return $this->createQueryBuilder('session')
            ->addSelect('coach', 'coachUser')
            ->innerJoin('session.coachProfile', 'coach')
            ->innerJoin('coach.user', 'coachUser')
            ->andWhere('session.user = :user')
            ->setParameter('user', $user)
            ->orderBy('session.startAt', 'DESC')
            ->getQuery()->getResult();
    }

    //    /**
    //     * @return Session[] Returns an array of Session objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Session
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
