<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Doctrine\Event;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\PersistentCollection;
use Doctrine\Persistence\ObjectManager;
use Lens\Bundle\LensApiBundle\Entity\Address;
use Lens\Bundle\LensApiBundle\Entity\Company\Company;
use Lens\Bundle\LensApiBundle\Entity\Company\DrivingSchool\DrivingSchool;
use Lens\Bundle\LensApiBundle\Entity\Company\Employee;
use Lens\Bundle\LensApiBundle\Entity\ContactMethod;
use Lens\Bundle\LensApiBundle\Entity\PaymentMethod\PaymentMethod;
use Lens\Bundle\LensApiBundle\Entity\Personal\Personal;
use Lens\Bundle\MeiliSearchBundle\LensMeiliSearch;

/**
 * Synchronize company records with the MeiliSearch index.
 */
#[AsDoctrineListener(event: Events::onFlush, connection: 'lens_api')]
class UpdateMeiliSearchListener
{
    private array $isHandled = [];
    private static ObjectManager $manager;

    private const string CREATE = 'create';
    private const string UPDATE = 'update';
    private const string DELETE = 'delete';

    public function __construct(
        private readonly ?LensMeiliSearch $lensMeiliSearch,
    ) {
    }

    public function onFlush(OnFlushEventArgs $event): void
    {
        if (!$this->lensMeiliSearch) {
            return;
        }

        self::$manager = $event->getObjectManager();

        $this->listRequiredMeiliSearchSynchronizationChanges();
        $this->synchronizeHandledChangesWithMeiliSearch();
    }

    private function listRequiredMeiliSearchSynchronizationChanges(): void
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

    /**
     * Changes to any of these entities: Company, DrivingSchool, Address, PaymentMethod, Personal, Employee
     * Will trigger a MeiliSearch update for all related companies to said entity.
     */
    private function handleEntity(object $entity, string $operation): void
    {
        // Only company deletions should be handled as deleted.
        if ($entity instanceof Company && self::DELETE === $operation) {
            $this->markAsHandled($entity, $operation);
            return;
        }

        // All other entity type deletions should be treated as update for company.
        if (self::DELETE === $operation) {
            $operation = self::UPDATE;
        }

        switch ($entity::class) {
            case Company::class:
                $this->markAsHandled($entity, $operation);
                break;

            case DrivingSchool::class:
                $this->markAsHandled($entity->company, $operation);
                break;

            case ContactMethod::class:
                if ($entity->company) {
                    $this->markAsHandled($entity->company, $operation);
                } elseif ($entity->personal) {
                    foreach ($entity->personal->companies as $employment) {
                        $this->markAsHandled($employment->company, $operation);
                    }
                }
                break;

            case Address::class:
                if ($entity->company) {
                    $this->markAsHandled($entity->company, $operation);
                } elseif ($entity->personal) {
                    foreach ($entity->personal->companies as $employment) {
                        $this->markAsHandled($employment->company, $operation);
                    }
                }
                break;

            case PaymentMethod::class:
                $this->markAsHandled($entity->company, $operation);
                break;

            case Employee::class:
                $this->markAsHandled($entity->company, $operation);
                break;

            case Personal::class:
                foreach ($entity->companies as $employment) {
                    $this->markAsHandled($employment->company, $operation);
                }
                break;
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
        if (!$owner) {
            return;
        }

        switch ($owner::class) {
            case Company::class:
                $this->markAsHandled($owner, self::UPDATE);
                break;

            case DrivingSchool::class:
                $this->markAsHandled($owner->company, self::UPDATE);
                break;

            case Personal::class:
                foreach ($owner->companies as $employment) {
                    $this->markAsHandled($employment->company, self::UPDATE);
                }
                break;

            case Employee::class:
                $this->markAsHandled($owner->company, self::UPDATE);
                break;
        }
    }

    private function markAsHandled(Company $company, string $operation): void
    {
        if ($this->isHandled[(string)$company->id]['operation'] ?? null === self::DELETE) {
            return;
        }

        $this->isHandled[(string)$company->id] = [
            'company' => $company,
            'operation' => $operation,
        ];
    }

    private function synchronizeHandledChangesWithMeiliSearch(): void
    {
        $byOperation = [];
        foreach ($this->isHandled as ['company' => $company, 'operation' => $operation]) {
            $byOperation[$operation][] = $company;
        }

        foreach ($byOperation as $operation => $companies) {
            switch ($operation) {
                case self::CREATE:
                    $this->synchronizeCreate($companies);
                    break;

                case self::UPDATE:
                    $this->synchronizeUpdate($companies);
                    break;

                case self::DELETE:
                    $this->synchronizeDelete($companies);
                    break;
            }
        }
    }

    private function synchronizeCreate(array $companies): void
    {
        $this->lensMeiliSearch->addDocuments('company', $companies);
    }

    private function synchronizeUpdate(array $companies): void
    {
        $this->lensMeiliSearch->addDocuments('company', $companies);
    }

    private function synchronizeDelete(array $companies): void
    {
        $companyIds = array_map(static fn (Company $company) => (string)$company->id, $companies);

        $this->lensMeiliSearch->index('company')->deleteDocuments($companyIds);
    }
}
