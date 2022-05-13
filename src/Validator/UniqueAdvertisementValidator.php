<?php

namespace Lens\Bundle\LensApiBundle\Validator;

use Lens\Bundle\LensApiBundle\Data\Advertisement;
use Lens\Bundle\LensApiBundle\LensApi;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueAdvertisementValidator extends ConstraintValidator
{
    public function __construct(
        private LensApi $lensApi,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueAdvertisement) {
            throw new UnexpectedTypeException($constraint, UniqueAdvertisement::class);
        }

        if (!$value instanceof Advertisement) {
            return;
        }

        $advertisements = $this->lensApi->advertisements->list();
        foreach ($advertisements as $advertisement) {
            if ($advertisement->type === $value->type && $advertisement->id !== $value->id) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ type }}', $value->type)
                    ->addViolation();

                break;
            }
        }
    }
}
