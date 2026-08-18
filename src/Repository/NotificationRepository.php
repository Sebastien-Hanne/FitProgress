<?php

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\User;
use App\Enum\NotificationType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function countUnreadFor(User $user): int
    {
        return $this->count(['user' => $user, 'isRead' => false]);
    }

    /** @return Notification[] */
    public function findWithoutCoachingHistory(User $user): array
    {
        return $this->createQueryBuilder('notification')
            ->andWhere('notification.user = :user')
            ->andWhere('notification.type NOT IN (:coachTypes)')
            ->setParameter('user', $user)
            ->setParameter('coachTypes', $this->coachingTypes())
            ->orderBy('notification.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countUnreadWithoutCoachingHistory(User $user): int
    {
        return (int) $this->createQueryBuilder('notification')
            ->select('COUNT(notification.id)')
            ->andWhere('notification.user = :user')
            ->andWhere('notification.isRead = false')
            ->andWhere('notification.type NOT IN (:coachTypes)')
            ->setParameter('user', $user)
            ->setParameter('coachTypes', $this->coachingTypes())
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return NotificationType[] */
    private function coachingTypes(): array
    {
        return [
            NotificationType::new_message,
            NotificationType::new_feedback,
            NotificationType::request_accepted,
            NotificationType::request_rejected,
            NotificationType::session_scheduled,
            NotificationType::session_modified,
            NotificationType::session_cancelled,
        ];
    }

    //    /**
    //     * @return Notification[] Returns an array of Notification objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('n.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Notification
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
