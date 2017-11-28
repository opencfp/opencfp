<?php

namespace OpenCFP\Domain\Services;

use Symfony\Component\HttpFoundation\Request;

interface RequestValidator
{
    public function isValid(Request $request): bool;
}
