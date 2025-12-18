<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

use function is_string;

class InitialsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!($constraint instanceof Initials)) {
            throw new UnexpectedTypeException($constraint, Initials::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (!preg_match(Initials::PATTERN, $value)) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameters(['{{ value }}' => $value])
                ->addViolation();
        }
    }
}
