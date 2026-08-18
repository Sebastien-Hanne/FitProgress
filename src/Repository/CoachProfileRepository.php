<?php

namespace App\Repository;

use App\Entity\CoachProfile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CoachProfile>
 */
class CoachProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoachProfile::class);
    }

    /** @return CoachProfile[] */
    public function findAvailable(?string $search = null, ?string $specialty = null): array
    {
        $qb = $this->createQueryBuilder('coach')
            ->innerJoin('coach.user', 'user')
            ->addSelect('user')
            ->andWhere('coach.isAvailable = true')
            ->andWhere('user.isDeleted = false')
            ->orderBy('user.name', 'ASC');

        if ($search !== null && $search !== '') {
            $qb->andWhere('LOWER(user.name) LIKE :search OR LOWER(coach.specialties) LIKE :search')
                ->setParameter('search', '%'.mb_strtolower($search).'%');
        }

        if ($specialty !== null && $specialty !== '') {
            $qb->andWhere('LOWER(coach.specialties) LIKE :specialty')
                ->setParameter('specialty', '%'.mb_strtolower($specialty).'%');
        }

        return $qb->getQuery()->getResult();
    }

    /** @return string[] */
    public function findAvailableSpecialties(): array
    {
        $rows = $this->createQueryBuilder('coach')
            ->select('coach.specialties')
            ->andWhere('coach.isAvailable = true')
            ->andWhere('coach.specialties IS NOT NULL')
            ->getQuery()->getSingleColumnResult();

        $specialties = [];
        foreach ($rows as $row) {
            foreach (preg_split('/[,;]+/', $row) ?: [] as $item) {
                if (($item = trim($item)) !== '') {
                    $specialties[mb_strtolower($item)] = $item;
                }
            }
        }
        natcasesort($specialties);

        return array_values($specialties);
    }

//    /**
//     * @return CoachProfile[] Returns an array of CoachProfile objects
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

//    public function findOneBySomeField($value): ?CoachProfile
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
