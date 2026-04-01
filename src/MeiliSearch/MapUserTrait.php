<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\MeiliSearch;

use Lens\Bundle\LensApiBundle\Entity\User;
use LogicException;

trait MapUserTrait
{
    private function mapUser(User $user, bool $mapPersonal = false): array
    {
        $document = [
            'id' => $user->id,
            'username' => $user->username,
            'roles' => $user->roles,

            'createdAt' => $user->createdAt->getTimestamp(),
            'createdAtDate' => $user->createdAt->format('c'),
            'updatedAt' => $user->updatedAt->getTimestamp(),
            'updatedAtDate' => $user->updatedAt->format('c'),
            'lastLoggedInAt' => $user->lastLoggedInAt?->getTimestamp(),
            'lastLoggedInAtDate' => $user->lastLoggedInAt?->format('c'),
            'disabledAt' => $user->disabledAt?->getTimestamp(),
            'disabledAtDate' => $user->disabledAt?->format('c'),
        ];

        if ($mapPersonal) {
            if (!method_exists($this, 'mapPersonal')) {
                throw new LogicException('The mapPersonal method is not found, did you forget to use MapPersonalTrait?');
            }

            $document['personal'] = $user->personal
                ? $this->mapPersonal($user->personal, mapCompanies: true)
                : null;
        }

        return $document;
    }
}
