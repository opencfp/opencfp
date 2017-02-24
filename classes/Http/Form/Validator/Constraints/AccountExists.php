<?php
namespace OpenCFP\Http\Form\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class AccountExists extends Constraint
{
    public $message = 'That account already exists';
}
