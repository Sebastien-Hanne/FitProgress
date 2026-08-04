<?php

namespace App\Repository;

use App\Entity\JournalEntry;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JournalEntry>
 */
class JournalEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JournalEntry::class);
    }

    public function findOneForUserAndDate(User $user, \DateTimeImmutable $date): ?JournalEntry
    {
        return $this->findOneBy(['user' => $user, 'date' => $date]);
    }

    public function findOneForUser(User $user, int $id): ?JournalEntry
    {
        return $this->findOneBy(['id' => $id, 'user' => $user]);
    }

    public function createHistoryQueryBuilder(User $user, ?\DateTimeImmutable $since = null): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('entry')
            ->leftJoin('entry.meals', 'meal')
            ->addSelect('meal')
            ->andWhere('entry.user = :user')
            ->setParameter('user', $user)
            ->orderBy('entry.date', 'DESC');

        if ($since !== null) {
            $queryBuilder
                ->andWhere('entry.date >= :since')
                ->setParameter('since', $since);
        }

        return $queryBuilder;
    }

    /** @return JournalEntry[] */
    public function findAllForUser(User $user): array
    {
        return $this->createHistoryQueryBuilder($user)->getQuery()->getResult();
    }

    //    /**
    //     * @return JournalEntry[] Returns an array of JournalEntry objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('j')
    //            ->andWhere('j.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('j.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?JournalEntry
    //    {
    //        return $this->createQueryBuilder('j')
    //            ->andWhere('j.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
