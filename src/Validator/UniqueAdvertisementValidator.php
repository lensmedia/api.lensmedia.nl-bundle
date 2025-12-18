<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Validator;

use Lens\Bundle\LensApiBundle\Entity\Personal\Advertisement;
use Lens\Bundle\LensApiBundle\LensApi;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueAdvertisementValidator extends ConstraintValidator
{
    public function __construct(
        private readonly LensApi $lensApi,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!($constraint instanceof UniqueAdvertisement)) {
            throw new UnexpectedTypeException($constraint, UniqueAdvertisement::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!$value instanceof Advertisement) {
            return;
        }

        $advertisement = $this->lensApi->advertisements->findOneByType($value->type);
        if ($advertisement->type === $value->type && $advertisement->id !== $value->id) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ type }}', $value->type)
                ->addViolation();
        }
    }
}
