<?php

namespace OpenCFP\Domain;

class ValidationException extends \Exception
{
    private $errors;

    public static function withErrors(array $errors = []): self
    {
        $instance         = new static('There was an error.');
        $instance->errors = $errors;

        return $instance;
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
