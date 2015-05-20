<?php

namespace OpenCFP\Domain\Services; 

interface RandomStringGenerator
{
    public function generate($length = 40);
}
