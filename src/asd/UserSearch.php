<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\asd;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Lens\Bundle\LensApiBundle\Entity\Company\Company;
use Lens\Bundle\LensApiBundle\Entity\Personal\Personal;
use Lens\Bundle\LensApiBundle\Entity\User;
use Lens\Bundle\MeilisearchBundle\Attribute\Index;
use Lens\Bundle\MeilisearchBundle\Document;
use Lens\Bundle\MeilisearchBundle\Exception\InvalidTransformData;

#[AsEntityListener(event: Events::postPersist, method: 'onUpdate', entityManager: 'lens_api', entity: User::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'onUpdate', entityManager: 'lens_api', entity: User::class)]
#[AsEntityListener(event: Events::postRemove, method: 'onRemove', entityManager: 'lens_api', entity: User::class)]
#[AsEntityListener(event: Events::postPersist, method: 'onUpdate', entityManager: 'lens_api', entity: Personal::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'onUpdate', entityManager: 'lens_api', entity: Personal::class)]
#[AsEntityListener(event: Events::postRemove, method: 'onRemove', entityManager: 'lens_api', entity: Personal::class)]
#[AsEntityListener(event: Events::postPersist, method: 'onUpdate', entityManager: 'lens_api', entity: Company::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'onUpdate', entityManager: 'lens_api', entity: Company::class)]
#[AsEntityListener(event: Events::postRemove, method: 'onRemove', entityManager: 'lens_api', entity: Company::class)]
readonly class UserSearch extends Search
{
    use MapPersonalTrait;
    use MapUserTrait;

    public const string INDEX = 'user';

    public function supports(): array
    {
        return [User::class];
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

        if ($object instanceof Company) {
            $users = [];
            foreach ($object->employees as $employee) {
                $user = $employee->personal->user;
                if ($user) {
                    $users[] = $user;
                }
            }

            $this->lensMeilisearch->addDocuments(self::INDEX, $users);

            return;
        }

        if ($object instanceof Personal) {
            $object = $object->user;
        }

        if ($object instanceof User) {
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

        if ($object instanceof Personal) {
            $object = $object->user;
        }

        if ($object instanceof User) {
            $this->lensMeilisearch->index(self::INDEX)->deleteDocument((string)$object->id);
        }
    }

    public function toDocument(object $data, array $context = []): Document
    {
        if (!($data instanceof User)) {
            throw new InvalidTransformData($data, User::class);
        }

        $user = $this->mapUser($data, mapPersonal: true);

        return new Document($user);
    }
}
