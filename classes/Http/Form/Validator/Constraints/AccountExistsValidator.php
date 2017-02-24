<?php
namespace OpenCFP\Http\Form\Validator\Constraints;

use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AccountExistsValidator extends ConstraintValidator
{
    public $groups = ['user'];

    /**
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        $user = Sentinel::findUserByCredentials(['email' => $value]);

        if ($user !== null) {
           $this->context->buildViolation($constraint->message)
               ->addViolation();
        }
    }
}
