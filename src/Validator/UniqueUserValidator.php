<?php

namespace Lens\Bundle\LensApiBundle\Validator;

use Lens\Bundle\LensApiBundle\Data\User;
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
        if (!$constraint instanceof UniqueDealer) {
            throw new UnexpectedTypeException($constraint, UniqueDealer::class);
        }

        if (!$value instanceof User) {
            return;
        }

        $user = $this->lensApi->users->byUsername($value);
        if (!$user) {
            return;
        }

        if ($user->username === $value->username && $user->id !== $value->id) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ username }}', $value->username)
                ->addViolation();
        }
    }
}
