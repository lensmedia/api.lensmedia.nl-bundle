<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Meilisearch;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Lens\Bundle\LensApiBundle\Entity\Company\Company;
use Lens\Bundle\LensApiBundle\Entity\Company\Employee;
use Lens\Bundle\LensApiBundle\Entity\Personal\Personal;
use Lens\Bundle\LensApiBundle\Entity\User;
use Lens\Bundle\MeilisearchBundle\Attribute\Index;
use Lens\Bundle\MeilisearchBundle\Document;
use Lens\Bundle\MeilisearchBundle\Exception\InvalidTransformData;

#[AsEntityListener(event: Events::postPersist, method: 'onUpdate', entityManager: 'lens_api', entity: Personal::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'onUpdate', entityManager: 'lens_api', entity: Personal::class)]
#[AsEntityListener(event: Events::postRemove, method: 'onRemove', entityManager: 'lens_api', entity: Personal::class)]
#[AsEntityListener(event: Events::postPersist, method: 'onUpdate', entityManager: 'lens_api', entity: User::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'onUpdate', entityManager: 'lens_api', entity: User::class)]
#[AsEntityListener(event: Events::postRemove, method: 'onRemove', entityManager: 'lens_api', entity: User::class)]
#[AsEntityListener(event: Events::postPersist, method: 'onUpdate', entityManager: 'lens_api', entity: Company::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'onUpdate', entityManager: 'lens_api', entity: Company::class)]
#[AsEntityListener(event: Events::postRemove, method: 'onRemove', entityManager: 'lens_api', entity: Company::class)]
readonly class PersonalSearch extends Search
{
    use MapPersonalTrait;
    use MapUserTrait;

    public const string INDEX = 'personal';

    public function supports(): array
    {
        return [Personal::class];
    }

    public function getIndexes(): array
    {
        return [
            new Index(uid: self::INDEX, client: 'lens_api'),
        ];
    }

    public function onUpdate(object $object, LifecycleEventArgs $event): void
    {
        if ($this->isLoadingFixturesInDebug()) {
            return;
        }

        // When a company is updated, update all employees as well (might have name change)
        if ($object instanceof Company) {
            $this->lensMeilisearch->addDocuments(self::INDEX, $object->employees->map(
                static fn (Employee $employee) => $employee->personal,
            )->toArray());

            return;
        }

        if ($object instanceof User) {
            $object = $object->personal;
        }

        if ($object instanceof Personal) {
            $this->lensMeilisearch->addDocuments(self::INDEX, [$object]);
        }
    }

    public function onRemove(object $object, LifecycleEventArgs $event): void
    {
        // Removal of company is update for employees (remove company from their profile)
        if ($object instanceof Company) {
            $this->onUpdate($object, $event);

            return;
        }

        if ($object instanceof User) {
            $object = $object->personal;
        }

        if ($object instanceof Personal) {
            $this->lensMeilisearch->index(self::INDEX)->deleteDocument((string)$object->id);
        }
    }

    public function toDocument(object $data, array $context = []): Document
    {
        if (!($data instanceof Personal)) {
            throw new InvalidTransformData($data, Personal::class);
        }

        $document = $this->mapPersonal($data, mapUser: true, mapCompanies: true);

        return new Document($document);
    }
}
