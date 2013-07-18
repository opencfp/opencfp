<?php

namespace OpenCFP\Config;


interface ParameterResolverInterface
{
    /**
     * Resolves a parameter by replacing dynamic placeholders with
     * their corresponding values.
     *
     * @param mixed $parameter The parameter for which to resolve the value
     * @return mixed A resolved parameter
     */
    public function resolve($parameter);
}