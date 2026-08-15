<?php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function countUnreadFor(User $recipient): int
    {
        return (int) $this->createQueryBuilder('message')
            ->select('COUNT(message.id)')
            ->innerJoin('message.conversation', 'conversation')
            ->innerJoin('conversation.coachProfile', 'coach')
            ->andWhere('(conversation.user = :recipient OR coach.user = :recipient)')
            ->andWhere('message.sender != :recipient')
            ->andWhere('message.isRead = false')
            ->setParameter('recipient', $recipient)
            ->getQuery()->getSingleScalarResult();
    }

    public function markIncomingAsRead(Conversation $conversation, User $reader): int
    {
        return $this->createQueryBuilder('message')->update()
            ->set('message.isRead', ':read')
            ->andWhere('message.conversation = :conversation')
            ->andWhere('message.sender != :reader')
            ->andWhere('message.isRead = false')
            ->setParameter('read', true)
            ->setParameter('conversation', $conversation)
            ->setParameter('reader', $reader)
            ->getQuery()->execute();
    }

    //    /**
    //     * @return Message[] Returns an array of Message objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Message
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
