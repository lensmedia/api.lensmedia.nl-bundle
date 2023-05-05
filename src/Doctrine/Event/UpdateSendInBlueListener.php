<?php

namespace Lens\Bundle\LensApiBundle\Doctrine\Event;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\PersistentCollection;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Lens\Bundle\LensApiBundle\Entity\Company\Company;
use Lens\Bundle\LensApiBundle\Entity\ContactMethod;
use Lens\Bundle\LensApiBundle\Entity\Personal\Personal;
use Lens\Bundle\LensApiBundle\SendInBlue\SendInBlue;
use Psr\Log\LoggerInterface;
use SendinBlue\Client\ApiException;

/**
 * Synchronize personal with SendInBlue when doctrine updates entities/collections.
 *
 * Works when:
 * Personal object is added, updated or deleted
 * Personal email contact method is added, updated or deleted
 * Email advertise option is added or removed
 * Company where personal works at start or stops becoming a dealer
 */
#[AsDoctrineListener(event: Events::onFlush, connection: 'lens_api')]
class UpdateSendInBlueListener
{
    private array $isHandled = [];
    private static ObjectManager $manager;

    private const CREATE = 'create';
    private const UPDATE = 'update';
    private const DELETE = 'delete';

    public function __construct(
        private readonly SendInBlue $sendInBlue,
        private readonly LoggerInterface $logger,
        private readonly bool $isDebug,
    ) {
    }

    public function onFlush(OnFlushEventArgs $event): void
    {
        self::$manager = $event->getObjectManager();

        $this->listRequiredSendInBlueSynchronizationChanges();
        $this->synchronizeHandledChangesWithSendInBlue();
    }

    private function listRequiredSendInBlueSynchronizationChanges(): void
    {
        $uow = self::$manager->getUnitOfWork();

        // These 3 take care of Personal/ContactMethod add/update/deletion.
        $this->handleEntityInsertions($uow->getScheduledEntityInsertions());
        $this->handleEntityUpdates($uow->getScheduledEntityUpdates());
        $this->handleEntityDeletions($uow->getScheduledEntityDeletions());

        // Handle add/remove email option, or company becoming a dealer or not.
        $this->handleCollectionUpdates($uow->getScheduledCollectionUpdates());
        $this->handleCollectionDeletions($uow->getScheduledCollectionDeletions());
    }

    private function handleEntityInsertions(array $entities): void
    {
        foreach ($entities as $entity) {
            $this->handleEntity($entity, self::CREATE);
        }
    }

    private function handleEntityUpdates(array $entities): void
    {
        foreach ($entities as $entity) {
            $this->handleEntity($entity, self::UPDATE);
        }
    }

    private function handleEntityDeletions(array $entities): void
    {
        foreach ($entities as $entity) {
            $this->handleEntity($entity, self::DELETE);
        }
    }

    private function handleEntity(object $entity, string $operation): void
    {
        if ($entity instanceof Personal) {
            $this->markAsHandled($entity, $operation);
        } elseif ($entity instanceof ContactMethod) {
            if ($entity->isEmail() && $entity->personal) {
                $this->markAsHandled($entity->personal, $operation);
            }
        }
    }

    private function handleCollectionUpdates(array $collections): void
    {
        foreach ($collections as $collection) {
            $this->handleCollection($collection);
        }
    }

    private function handleCollectionDeletions(array $collections): void
    {
        foreach ($collections as $collection) {
            $this->handleCollection($collection);
        }
    }

    private function handleCollection(PersistentCollection $collection): void
    {
        $owner = $collection->getOwner();
        if ($owner instanceof Personal) {
            $this->markAsHandled($owner, self::UPDATE);
        } elseif ($owner instanceof Company) {
            foreach ($owner->employees as $employee) {
                $this->markAsHandled($employee->personal, self::UPDATE);
            }
        }
    }

    private function markAsHandled(Personal $personal, string $operation): void
    {
        if ($this->isHandled[(string)$personal->id]['operation'] ?? null === self::DELETE) {
            return;
        }

        $this->isHandled[(string)$personal->id] = [
            'personal' => $personal,
            'operation' => $operation,
        ];
    }

    private function synchronizeHandledChangesWithSendInBlue(): void
    {
        foreach ($this->isHandled as ['personal' => $personal, 'operation' => $operation]) {
            switch ($operation) {
                case self::CREATE:
                    $this->synchronizeCreate($personal);
                    break;

                case self::UPDATE:
                    $this->synchronizeUpdate($personal);
                    break;

                case self::DELETE:
                    $this->synchronizeDelete($personal);
                    break;
            }
        }
    }

    private function synchronizeCreate(Personal $personal): void
    {
        try {
            $this->sendInBlue->createContact($personal);
        } catch (ApiException $exception) {
            $this->handleException($exception);
        }
    }

    private function synchronizeUpdate(Personal $personal): void
    {
        $uow = self::$manager->getUnitOfWork();

        // Update requires old email to be passed to method.
        $oldEmail = null;
        if (null !== $personal->emailContactMethod()) {
            $changes = $uow->getEntityChangeSet($personal->emailContactMethod());
            if (!empty($changes)) {
                $oldEmail = $changes['value'][0];
            }
        }

        try {
            $this->sendInBlue->updateContact($personal, $oldEmail);
        } catch (ApiException $exception) {
            $this->handleException($exception);
        }
    }

    private function synchronizeDelete(Personal $personal): void
    {
        try {
            $this->sendInBlue->deleteContact($personal);
        } catch (ApiException $exception) {
            $this->handleException($exception);
        }
    }

    private function handleException(Exception $exception): void
    {
        $this->logger->error(__CLASS__.' returned an error: '.$exception->getMessage(), [
            'exception' => $exception,
        ]);
    }
}
