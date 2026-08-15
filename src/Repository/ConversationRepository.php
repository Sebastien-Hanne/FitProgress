<?php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\User;
use App\Enum\RequestStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Conversation>
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    /** @return Conversation[] */
    public function findForParticipant(User $user): array
    {
        return $this->createQueryBuilder('conversation')
            ->addSelect('participant', 'coach', 'coachUser', 'messages', 'sender')
            ->innerJoin('conversation.user', 'participant')
            ->innerJoin('conversation.coachProfile', 'coach')
            ->innerJoin('coach.user', 'coachUser')
            ->innerJoin('coach.coachRequests', 'coachRequest', 'WITH', 'coachRequest.user = participant')
            ->leftJoin('conversation.messages', 'messages')
            ->leftJoin('messages.sender', 'sender')
            ->andWhere('participant = :user OR coachUser = :user')
            ->andWhere('coachRequest.status = :approved')
            ->setParameter('user', $user)
            ->setParameter('approved', RequestStatus::Approved)
            ->orderBy('conversation.lastMessageAt', 'DESC')
            ->addOrderBy('conversation.createdAt', 'DESC')
            ->getQuery()->getResult();
    }

    public function findOneForPair(User $user, \App\Entity\CoachProfile $coach): ?Conversation
    {
        return $this->findOneBy(['user' => $user, 'coachProfile' => $coach]);
    }

    //    /**
    //     * @return Conversation[] Returns an array of Conversation objects
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

    //    public function findOneBySomeField($value): ?Conversation
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
