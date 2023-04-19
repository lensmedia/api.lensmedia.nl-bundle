<?php

namespace Lens\Bundle\LensApiBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

use function is_string;

class ChamberOfCommerceValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!($constraint instanceof ChamberOfCommerce)) {
            throw new UnexpectedTypeException($constraint, ChamberOfCommerce::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (!preg_match('~^\d{8}$~', $value)) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameters(['{{ value }}' => $value])
                ->addViolation();
        }
    }
}
