<?php

namespace Lens\Bundle\LensApiBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class CbrValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!($constraint instanceof Cbr)) {
            throw new UnexpectedTypeException($constraint, Cbr::class);
        }

        if ((null === $value) || ('' === $value)) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (!preg_match('~^\d{4}[a-z]\d$~i', $value)) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameters(['{{ cbr }}' => $value])
                ->addViolation();
        }
    }
}
