<?php

namespace OpenCFP\Domain\Speaker;

class NotAllowedException extends \Exception
{
    public static function notAllowedToView(string $property): self
    {
        return new self(\sprintf('Not allowed to view %s. Hidden property', $property));
    }
}
