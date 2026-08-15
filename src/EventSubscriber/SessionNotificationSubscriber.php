<?php

namespace App\EventSubscriber;

use App\Entity\Notification;
use App\Entity\Session;
use App\Enum\NotificationType;
use App\Enum\SessionStatus;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::onFlush)]
final class SessionNotificationSubscriber
{
    public function onFlush(OnFlushEventArgs $args): void
    {
        $entityManager = $args->getObjectManager();
        if (!$entityManager instanceof EntityManagerInterface) return;
        $unitOfWork = $entityManager->getUnitOfWork();

        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof Session) {
                $this->schedule($entity, NotificationType::session_scheduled, 'Nouvelle séance', $entityManager);
            }
        }

        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            if (!$entity instanceof Session) continue;
            $changes = $unitOfWork->getEntityChangeSet($entity);
            $cancelled = isset($changes['status']) && $entity->getStatus() === SessionStatus::Cancelled;
            $this->schedule($entity, $cancelled ? NotificationType::session_cancelled : NotificationType::session_modified, $cancelled ? 'Séance annulée' : 'Séance modifiée', $entityManager);
        }
    }

    private function schedule(Session $session, NotificationType $type, string $title, EntityManagerInterface $entityManager): void
    {
        if (!$session->getUser() || !$session->getStartAt()) return;
        $notification = (new Notification())->setUser($session->getUser())->setType($type)->setTitle($title)
            ->setContent(sprintf('%s — le %s à %s.', $session->getTitle(), $session->getStartAt()->format('d/m/Y'), $session->getStartAt()->format('H:i')));
        $entityManager->persist($notification);
        $entityManager->getUnitOfWork()->computeChangeSet($entityManager->getClassMetadata(Notification::class), $notification);
    }
}
