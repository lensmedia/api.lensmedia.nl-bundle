<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Meilisearch;

use Lens\Bundle\LensApiBundle\Entity\Company\Employee;
use Lens\Bundle\LensApiBundle\Entity\Personal\Personal;
use LogicException;

trait MapPersonalTrait
{
    use MapContactMethodsTrait;

    private function mapPersonal(Personal $personal, bool $mapUser = false, bool $mapCompanies = false): array
    {
        $entry = ['id' => $personal->id];

        // Flatten the name
        $name = trim(implode(' ', [
            empty($personal->nickname) ? $personal->initials : $personal->nickname,
            trim($personal->surnameAffix.' '.$personal->surname),
        ]));

        if ('' !== $name) {
            $entry['name'] = $name;
        }

        $contactMethods = $this->mapContactMethods($personal->contactMethods);
        if (!empty($contactMethods)) {
            $entry = array_merge($entry, $contactMethods);
        }

        if ($mapCompanies) {
            $entry['companies'] = $personal->companies->map(static fn (Employee $employee) => [
                'id' => $employee->id,
                'function' => $employee->function,
                'company' => [
                    'id' => $employee->company->id,
                    'name' => $employee->company->name,
                ],
            ])->toArray();
        }

        if ($mapUser) {
            if (!method_exists($this, 'mapUser')) {
                throw new LogicException('The mapUser method is not found, did you forget to use MapUserTrait?');
            }

            $entry['user'] = $personal->user
                ? $this->mapUser($personal->user)
                : null;
        }

        return $entry;
    }
}
