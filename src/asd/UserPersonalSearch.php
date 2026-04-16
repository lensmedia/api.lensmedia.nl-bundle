<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\asd;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Lens\Bundle\LensApiBundle\Entity\Company\Company;
use Lens\Bundle\LensApiBundle\Entity\Personal\Personal;
use Lens\Bundle\LensApiBundle\Entity\User;
use Lens\Bundle\LensApiBundle\asd\Data\UserPersonalData;
use Lens\Bundle\MeilisearchBundle\Attribute\Index;
use Lens\Bundle\MeilisearchBundle\Document;
use Lens\Bundle\MeilisearchBundle\Exception\InvalidTransformData;

use function sprintf;

/**
 * This search is a merge of both User and Personal, as they are closely related in a one-to-one relationship and often
 * searched together. Used to exclude duplicate results as opposed to when using federation search for both entities.
 *
 * It listens to changes in both entities and updates the search index accordingly.
 * When a company is updated or removed, it also updates the related users to ensure the search index remains consistent.
 */
#[AsEntityListener(event: Events::postPersist, method: 'onUpdate', entityManager: 'lens_api', entity: User::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'onUpdate', entityManager: 'lens_api', entity: User::class)]
#[AsEntityListener(event: Events::postRemove, method: 'onRemove', entityManager: 'lens_api', entity: User::class)]
#[AsEntityListener(event: Events::postPersist, method: 'onUpdate', entityManager: 'lens_api', entity: Personal::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'onUpdate', entityManager: 'lens_api', entity: Personal::class)]
#[AsEntityListener(event: Events::postRemove, method: 'onRemove', entityManager: 'lens_api', entity: Personal::class)]
#[AsEntityListener(event: Events::postPersist, method: 'onUpdate', entityManager: 'lens_api', entity: Company::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'onUpdate', entityManager: 'lens_api', entity: Company::class)]
#[AsEntityListener(event: Events::postRemove, method: 'onRemove', entityManager: 'lens_api', entity: Company::class)]
readonly class UserPersonalSearch extends Search
{
    use MapPersonalTrait;
    use MapUserTrait;

    public const string INDEX = 'user_personal';

    public function supports(): array
    {
        return [UserPersonalData::class];
    }

    public function getIndexes(): array
    {
        return [
            new Index(uid: self::INDEX, settings: [
                'filterableAttributes' => ['user.id', 'personal.id'],
            ], client: 'lens_api'),
        ];
    }

    public function onUpdate(object $object, LifecycleEventArgs $event): void
    {
        if ($this->isLoadingFixturesInDebug()) {
            return;
        }

        $documents = [];

        if ($object instanceof Company) {
            foreach ($object->employees as $employee) {
                $user = $employee->personal->user;
                if ($user) {
                    $documents[] = UserPersonalData::fromUser($user);
                }
            }
        } elseif ($object instanceof Personal) {
            $documents[] = UserPersonalData::fromPersonal($object);
        } elseif ($object instanceof User) {
            $documents[] = UserPersonalData::fromUser($object);
        } else {
            return;
        }

        $this->lensMeilisearch->addDocuments(self::INDEX, $documents);
    }

    public function onRemove(object $object, LifecycleEventArgs $event): void
    {
        // Removal of company is update for employees (remove company from their profile)
        if ($object instanceof Company) {
            $this->onUpdate($object, $event);

            return;
        }

        // When a user is removed we remove the entry and if there is an associated personal we add that back in
        // without the user data.
        if ($object instanceof User) {
            $this->lensMeilisearch->index(self::INDEX)->deleteDocuments([
                'filter' => [
                    sprintf('user.id = %s', $object->id),
                ],
            ]);

            if ($object->personal) {
                $this->onUpdate($object->personal, $event);
            }

            return;
        }

        // When a personal is removed we remove the entry and if there is an associated user we add that back in
        // without the personal data.
        if ($object instanceof Personal) {
            $this->lensMeilisearch->index(self::INDEX)->deleteDocuments([
                'filter' => [
                    sprintf('personal.id = %s', $object->id),
                ],
            ]);

            if ($object->user) {
                $this->onUpdate($object->user, $event);
            }

            return;
        }
    }

    public function toDocument(object $data, array $context = []): Document
    {
        if (!$data instanceof UserPersonalData) {
            throw new InvalidTransformData($data, UserPersonalData::class);
        }

        return new Document([
            'id' => $data->id(),
            'user' => $data->user ? $this->mapUser($data->user) : null,
            'personal' => $data->personal ? $this->mapPersonal($data->personal, mapCompanies: true) : null,
        ]);
    }
}
