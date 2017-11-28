<?php

namespace OpenCFP\Domain\Services;

use Symfony\Component\HttpFoundation\Request;

interface Validator
{
    public function isValid(Request $request): bool;
}
