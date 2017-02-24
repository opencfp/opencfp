<?php
namespace OpenCFP\Http\Form\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class TwitterAccount extends Constraint
{
    public $message = '"%string%" is not a valid Twitter account name';
}
