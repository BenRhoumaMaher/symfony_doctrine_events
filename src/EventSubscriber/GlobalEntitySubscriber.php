<?php

namespace App\EventSubscriber;

use App\Interface\BlameableInterface;
use App\Interface\SoftDeletableInterface;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Bundle\SecurityBundle\Security;
use Psr\Log\LoggerInterface;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preRemove)]
#[AsDoctrineListener(event: Events::postUpdate)]
class GlobalEntitySubscriber
{
    public function __construct(
        private Security $security,
        private LoggerInterface $logger
    ) {
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof BlameableInterface) {
            $user = $this->security->getUser();
            if ($user instanceof User) {
                $entity->setCreatedBy($user);
            }
        }
    }

    public function preRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof SoftDeletableInterface) {

            $em = $args->getObjectManager();

            $entity->setDeletedAt(new \DateTimeImmutable());

            $em->persist($entity);
            $em->flush();

        }
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        $className = (new \ReflectionClass($entity))->getShortName();
        $user = $this->security->getUser()?->getUserIdentifier() ?? 'Anonymous';

        $uow = $args->getObjectManager()->getUnitOfWork();
        $changes = $uow->getEntityChangeSet($entity);

        if (empty($changes)) {
            return;
        }

        $this->logger->info(
            "AUDIT: $className updated by $user",
            [
            'entity_id' => method_exists(
                $entity,
                'getId'
            ) ? $entity->getId() : 'N/A',
            'changes' => $changes
            ]
        );
    }
}
