<?php
namespace OpenCFP\Http\Form\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TwitterAccountValidator extends ConstraintValidator
{
    public $groups = ['twitter'];

    /**
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (strlen($value) > 0 && !preg_match('/^@?(\w){1,15}$/', $value, $matches)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%string%', $value)
                ->addViolation();
        }
    }
}
