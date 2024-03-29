<?php

namespace Lens\Bundle\LensApiBundle\Validator;

use Lens\Bundle\LensApiBundle\Entity\Company\Dealer;
use Lens\Bundle\LensApiBundle\LensApi;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueDealerValidator extends ConstraintValidator
{
    public function __construct(
        private readonly LensApi $lensApi,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueDealer) {
            throw new UnexpectedTypeException($constraint, UniqueDealer::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!$value instanceof Dealer) {
            return;
        }

        $dealer = $this->lensApi->dealers->findOneByName($value->name);
        if ($dealer->name === $value->name && $dealer->id !== $value->id) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value->name)
                ->addViolation();
        }
    }
}
