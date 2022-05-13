<?php

namespace Lens\Bundle\LensApiBundle\Validator;

use Lens\Bundle\LensApiBundle\Data\Dealer;
use Lens\Bundle\LensApiBundle\LensApi;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueDealerValidator extends ConstraintValidator
{
    public function __construct(
        private LensApi $lensApi,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueDealer) {
            throw new UnexpectedTypeException($constraint, UniqueDealer::class);
        }

        if (!$value instanceof Dealer) {
            return;
        }

        $dealers = $this->lensApi->dealers->list();
        foreach ($dealers as $dealer) {
            if ($dealer->name === $value->name && $dealer->id !== $value->id) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ name }}', $value->name)
                    ->addViolation();

                break;
            }
        }
    }
}
