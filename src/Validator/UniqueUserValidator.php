<?php

namespace Lens\Bundle\LensApiBundle\Validator;

use Lens\Bundle\LensApiBundle\LensApi;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueUserValidator extends ConstraintValidator
{
    public function __construct(
        private LensApi $lensApi,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueUser) {
            throw new UnexpectedTypeException($constraint, UniqueUser::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        $user = $this->lensApi->users->byUsername($value);
        if (!$user) {
            return;
        }

        if ($user->username === $value) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
